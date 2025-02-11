<?php

namespace App\Http\Controllers\Api;

use App\Models\CompanyMaster;
use JWTAuth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;

class ApiController extends Controller {
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="User's name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *       @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="User's mobile",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="User's password",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="User registered successfully"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function register(Request $request) {
        //Validate data
        $data = $request->only('name', 'email', 'password', 'mobile', 'role');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'password' => 'required|string|min:6|max:50',
            'role' => 'required|string|exists:roles,name' // Ensure the role exists in the roles table
        ]);

        // Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password)
        ]);

        // Assign Role to User
        $user->assignRole($request->role);

        // Return success response
        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        // Validate credentials
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        // Send failed response if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        // Attempt login and create token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Return response with user details and token
        return response()->json([
            'success' => true,
            'message' => 'User login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->roles->pluck('name')->first() // Assuming the user has one role
            ]
        ], 200);
    }


    public function logout(Request $request) {
        try {
            Auth::logout();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again.'
            ], 500);
        }
    }

    public function getService(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $services = ServiceCategory::all();

            $services = $services->map(function ($service) {
                $service->sc_photo = $service->sc_photo ? asset($service->sc_photo) : null;
                $service->is_status = $service->is_status == "1" ? "Active" : "InActive";
                return $service;
            });

            if ($services->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'No services found',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Services fetched successfully',
                'data' => $services,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCompaniesForVehicle(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
            ]);
            $companies = CompanyMaster::where('vehical_id', $request->vehicle_id)
                ->get()
                ->map(function ($company) {
                    return [
                        'id' => $company->id,
                        'com_code' => $company->com_code,
                        'com_name' => $company->com_name,
                        'com_logo' => asset($company->com_logo), // Full path for logo
                        'is_status' => $company->is_status == 1 ? 'Active' : 'Inactive', // Convert status to text
                        'vehical_id' => $company->vehical_id
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Companies fetched successfully',
                'data' => $companies
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getVehicleModels(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $request->validate([
                'vehicle_id' => 'required|exists:company_master,vehical_id',
                'company_id' => 'required|exists:company_master,id',
            ]);

            // Fetch vehicle models based on vehicle_id and company_id
            $vehicleModels = DB::table('model_master')
                ->where('com_id', $request->company_id)
                ->join('company_master', 'model_master.com_id', '=', 'company_master.id')
                ->where('company_master.vehical_id', $request->vehicle_id)
                ->select(
                    'model_master.id',
                    'model_master.model_code',
                    'model_master.model_name',
                    'model_master.model_photo',
                    'model_master.model_description'
                )
                ->get()
                ->map(function ($model) {
                    return [
                        'id' => $model->id,
                        'model_code' => $model->model_code,
                        'model_name' => $model->model_name,
                        'model_photo' => asset($model->model_photo), // Full path for model image
                        'model_description' => $model->model_description
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Vehicle models fetched successfully',
                'data' => $vehicleModels
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}