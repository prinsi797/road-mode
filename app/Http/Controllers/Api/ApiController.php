<?php

namespace App\Http\Controllers\Api;

use App\Models\AreaMaster;
use App\Models\BranchMaster;
use App\Models\CityMaster;
use App\Models\CompanyMaster;
use App\Models\InquiryMaster;
use App\Models\packageMaster;
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

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->mobile,
            'password' => bcrypt($request->password)
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

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

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'User login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->roles->pluck('name')->first()
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
    public function getServicesByVehicle(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $request->validate([
                'vehicle_id' => 'required|exists:service_cat_master,vehical_id',
            ]);

            $services = ServiceCategory::where('vehical_id', $request->vehicle_id)
                ->where('is_status', 1)
                ->get()
                ->map(function ($service) {
                    $service->sc_photo = url($service->sc_photo);
                    $service->is_status = $service->is_status ? 'Active' : 'Inactive';

                    return $service;
                });

            if ($services->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No services found for the given vehicle ID',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Services retrieved successfully',
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

    public function getBranches() {
        try {
            $branches = BranchMaster::select('id', 'br_code', 'br_owner_name', 'br_mobile', 'br_user_name')
                ->get();

            if ($branches->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No branches found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Branches retrieved successfully',
                'data' => $branches,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching branches',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function serviceCategory(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $services = ServiceCategory::get()
                ->map(function ($service) {
                    $service->sc_photo = url($service->sc_photo);
                    $service->is_status = $service->is_status ? 'Active' : 'Inactive';
                    return $service;
                });

            if ($services->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No services found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Services retrieved successfully',
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
    public function createService(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $data = $request->only('sc_name', 'sc_photo', 'sc_description', 'vehical_id');
            $validator = Validator::make($data, [
                'sc_name' => 'required',
                'sc_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'sc_description' => 'nullable',
                'vehical_id' => 'required|exists:vehicles,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }
            $photoPath = null;
            if ($request->hasFile('sc_photo')) {
                try {
                    $targetDirectory = public_path('services');
                    if (!file_exists($targetDirectory)) {
                        mkdir($targetDirectory, 0777, true);
                    }
                    $file = $request->file('sc_photo');
                    $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($targetDirectory, $fileName);
                    $photoPath = 'services/' . $fileName;
                } catch (Exception $e) {
                    return response()->json([
                        'error' => ['sc_photo' => ['The sc photo failed to upload.']],
                    ], 500);
                }
            }

            $user = ServiceCategory::create([
                'sc_name' => $request->sc_name,
                'sc_photo' => $photoPath,
                'sc_description' => $request->sc_description,
                'vehical_id' => $request->vehical_id,
                'is_status' =>  1,
                'created_by' => auth()->user()->name,
            ]);

            if ($user->sc_photo) {
                $user->sc_photo = asset($user->sc_photo);
            }

            return response()->json([
                'status' => true,
                'message' => 'Service add successfully',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateService(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $serviceId = $request->input('service_id');
            $service = ServiceCategory::find($serviceId);

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'service_id' => 'required|exists:service_cat_master,id',
                'sc_name' => 'required',
                'sc_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'sc_description' => 'nullable',
                'vehical_id' => 'required|exists:vehicles,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            // Handle photo upload if provided
            $photoPath = $service->sc_photo; // Keep the current photo by default
            if ($request->hasFile('sc_photo')) {
                try {
                    $targetDirectory = public_path('services');
                    if (!file_exists($targetDirectory)) {
                        mkdir($targetDirectory, 0777, true);
                    }
                    $file = $request->file('sc_photo');
                    $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($targetDirectory, $fileName);

                    // Delete the old photo if it exists
                    if ($service->sc_photo && file_exists(public_path($service->sc_photo))) {
                        unlink(public_path($service->sc_photo));
                    }

                    $photoPath = 'services/' . $fileName;
                } catch (Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());
                    return response()->json([
                        'error' => ['sc_photo' => ['The sc photo failed to upload.']],
                    ], 500);
                }
            }

            // Update service details
            $service->update([
                'sc_name' => $request->sc_name ?? $service->sc_name,
                'sc_photo' => $photoPath,
                'sc_description' => $request->sc_description ?? $service->sc_description,
                'vehical_id' => $request->vehical_id ?? $service->vehical_id,
                'is_status' =>  1,
                'modified_by' => auth()->user()->name,
            ]);

            if ($service->sc_photo) {
                $service->sc_photo = asset($service->sc_photo);
            }

            return response()->json([
                'status' => true,
                'message' => 'Service updated successfully',
                'data' => $service,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            \Log::error('Error occurred: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteService(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'service_id' => 'required|exists:service_cat_master,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], 422);
            }

            $serviceId = $request->input('service_id');
            $service = ServiceCategory::find($serviceId);

            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found',
                ], 404);
            }

            if ($service->sc_photo && file_exists(public_path($service->sc_photo))) {
                unlink(public_path($service->sc_photo));
            }

            // Delete the service record
            // $service->delete();
            $service->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'Service deleted successfully',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // city master
    public function city(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $citys = CityMaster::get();


            if ($citys->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No City found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'City retrieved successfully',
                'data' => $citys,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching citys',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createCity(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $data = $request->only('city_name');
            $validator = Validator::make($data, [
                'city_name' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            $city = CityMaster::create([
                'city_name' => $request->city_name,
                'is_status' =>  1,
                'created_by' => auth()->user()->name,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'City add successfully',
                'data' => $city
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching City',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCity(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $cityId = $request->input('city_id');

            $city = CityMaster::find($cityId);
            if (!$city) {
                return response()->json([
                    'status' => false,
                    'message' => 'City not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'city_name' => 'required',
                'city_id' => 'required|exists:city_master,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            // Update service details
            $city->update([
                'city_name' => $request->city_name ?? $city->city_name,
                'is_status' =>  1,
                'modified_by' => auth()->user()->name,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'City updated successfully',
                'data' => $city,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the city',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteCity(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'city_id' => 'required|exists:city_master,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], 422);
            }

            $cityId = $request->input('city_id');

            $city = CityMaster::find($cityId);

            if (!$city) {
                return response()->json([
                    'status' => false,
                    'message' => 'city not found',
                ], 404);
            }

            // Delete the service record
            // $service->delete();
            $city->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'City deleted successfully',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the city',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // user get
    public function User(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $users = User::get();

            if ($request->has('role')) {
                $user->assignRole($request->role);
            }

            // Eager-load 'roles' for all users
            $users = User::with('roles')->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No user found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'User retrieved successfully',
                'data' => $users,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createUser(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $data = $request->only('name', 'email', 'u_fullname', 'password', 'u_current_addr', 'phone_number', 'role', 'u_adhar_photo');
            $validator = Validator::make($data, [
                'name' => 'required',
                'email' => 'required',
                'phone_number' => 'required',
                'u_fullname' => 'required',
                'password' => 'required',
                'u_current_addr' => 'required',
                'role' => 'required|string|exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }
            $pack_code = $this->generateUserCode();

            $two_factor_enable = isset($request->two_factor_enable) && $request->two_factor_enable == "on" ? 1 : 0;

            $photoPath = null;
            if ($request->hasFile('u_adhar_photo')) {
                try {
                    $targetDirectory = public_path('users');
                    if (!file_exists($targetDirectory)) {
                        mkdir($targetDirectory, 0777, true);
                    }
                    $file = $request->file('u_adhar_photo');
                    $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($targetDirectory, $fileName);
                    $photoPath = 'users/' . $fileName;
                } catch (Exception $e) {
                    return response()->json([
                        'error' => ['u_adhar_photo' => ['The sc photo failed to upload.']],
                    ], 500);
                }
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'u_code' => $pack_code,
                'password' => bcrypt($request->password),
                'phone_number' => $request->phone_number,
                'u_fullname' => $request->u_fullname,
                'u_adhar_photo' => $photoPath,
                'two_factor_enable' => $two_factor_enable
            ]);

            $user->assignRole($request->role);
            if ($user->u_adhar_photo) {
                $user->u_adhar_photo = asset($user->u_adhar_photo);
            }

            return response()->json([
                'status' => true,
                'message' => 'City add successfully',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching City',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateUserCode() {
        $lastUser = User::latest('id')->first();
        return 'User' . str_pad(($lastUser->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }


    public function updateUser(Request $request) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $userId = $request->input('user_id');

            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $data = $request->only('user_id', 'name', 'email', 'u_fullname', 'u_current_addr', 'phone_number', 'role', 'u_adhar_photo');
            $validator = Validator::make($data, [
                'user_id' => 'required|exists:users,id',
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone_number' => 'required',
                'u_fullname' => 'required',
                'u_current_addr' => 'required',
                'role' => 'required|string|exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 422);
            }

            $photoPath = $user->u_adhar_photo;
            if ($request->hasFile('u_adhar_photo')) {
                try {
                    $targetDirectory = public_path('users');
                    if (!file_exists($targetDirectory)) {
                        mkdir($targetDirectory, 0777, true);
                    }
                    $file = $request->file('u_adhar_photo');
                    $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move($targetDirectory, $fileName);
                    $photoPath = 'users/' . $fileName;

                    if ($user->u_adhar_photo && file_exists(public_path($user->u_adhar_photo))) {
                        unlink(public_path($user->u_adhar_photo));
                    }
                } catch (Exception $e) {
                    return response()->json([
                        'error' => ['u_adhar_photo' => ['The photo failed to upload.']],
                    ], 500);
                }
            }

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'u_fullname' => $request->u_fullname,
                'u_current_addr' => $request->u_current_addr,
                'u_adhar_photo' => $photoPath,
            ]);

            if ($request->has('role')) {
                $user->syncRoles([$request->role]);
            }
            if ($user->u_adhar_photo) {
                $user->u_adhar_photo = asset($user->u_adhar_photo);
            }
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function userDelete(Request $request) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], 422);
            }
            $userId = $request->input('user_id');
            $user   = User::find($userId);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if ($user->u_adhar_photo && file_exists(public_path($user->u_adhar_photo))) {
                unlink(public_path($user->u_adhar_photo));
            }

            // $user->delete();
            $user->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Role create
    public function createRole(Request $request) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            if (!$authUser->hasRole('Admin')) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to create roles',
                ], 403);
            }

            $data = $request->only('name');
            $validator = Validator::make($data, [
                'name' => 'required|string|unique:roles,name',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 422);
            }

            $role = Role::create(['name' => $request->name]);

            return response()->json([
                'status' => true,
                'message' => 'Role created successfully',
                'data' => $role,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRoles() {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $roles = Role::all();

            return response()->json([
                'status' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $roles,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateRole(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255', // Role name
                'role_id' => 'required|exists:roles,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $roleId = $request->input('role_id');
            $role = Role::find($roleId);

            if (!$role) {
                return response()->json([
                    'status' => false,
                    'message' => 'Role not found',
                ], 404);
            }

            $role->name = $request->name;
            $role->save();

            return response()->json([
                'status' => true,
                'message' => 'Role updated successfully',
                'data' => $role,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // package get api
    public function getPackages(Request $request) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $query = packageMaster::where('is_status', 1);

            if ($request->has('package_id') && $request->package_id) {
                $query->where('id', $request->package_id);
            }

            if ($request->has('service_id') && $request->service_id) {
                $query->whereRaw('FIND_IN_SET(?, service_id)', [$request->service_id]);
            }

            $packages = $query->get();

            $transformedPackages = $packages->map(function ($package) {
                $serviceIds = explode(',', $package->service_id);
                $services = DB::table('service_cat_master')
                    ->whereIn('id', $serviceIds)
                    ->select('id', 'sc_name')
                    ->get();

                return [
                    'id' => $package->id,
                    'pack_code' => $package->pack_code,
                    'pack_name' => $package->pack_name,
                    'pack_duration' => $package->pack_duration,
                    'pack_other_faci' => $package->pack_other_faci,
                    'pack_description' => $package->pack_description,
                    'pack_net_amt' => $package->pack_net_amt,
                    'package_logo_url' => $package->package_logo ? url('/') . '/' . $package->package_logo : null,
                    'is_status' => $package->is_status,
                    'services' => $services,
                    // 'service_names' => $services->pluck('sc_name')->implode(', ')
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Packages retrieved successfully',
                'data' => $transformedPackages,
                'count' => $transformedPackages->count()
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve packages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // area master

    public function Area(Request $request) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            // $areas = AreaMaster::all();
            $areas = AreaMaster::with('city')->get();

            return response()->json([
                'status' => true,
                'message' => 'Area retrieved successfully',
                'data' => $areas,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createArea(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $data = $request->only('city_id', 'area_name', 'distance_from_branch');
            $validator = Validator::make($data, [
                'city_id' => 'required|exists:city_master,id',
                'area_name' => 'required',
                'distance_from_branch' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }
            $area = AreaMaster::create([
                'city_id' => $request->city_id,
                'area_name' => $request->area_name,
                'distance_from_branch' => $request->distance_from_branch
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Area add successfully',
                'data' => $area
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching City',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateArea(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'city_id' => 'required|exists:city_master,id',
                'area_name' => 'required',
                'distance_from_branch' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $areaId = $request->input('area_id');
            $area = AreaMaster::find($areaId);

            if (!$area) {
                return response()->json([
                    'status' => false,
                    'message' => 'Area not found',
                ], 404);
            }

            $area->city_id = $request->city_id;
            $area->area_name = $request->area_name;
            $area->distance_from_branch = $request->distance_from_branch;
            $area->save();

            return response()->json([
                'status' => true,
                'message' => 'Area updated successfully',
                'data' => $area,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the area',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function areaDelete(Request $request) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
            $validator = Validator::make($request->all(), [
                'area_id' => 'required|exists:area_master,id'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], 422);
            }
            $areaId = $request->input('area_id');
            $area = AreaMaster::find($areaId);
            if (!$area) {
                return response()->json([
                    'status' => false,
                    'message' => 'Area not found',
                ], 404);
            }
            // $user->delete();
            $area->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'Area deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // add pickup
    public function addPickupInquiry(Request $request) {

        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $validated = $request->validate([
                'inq_from_online_web' => 'nullable|string',
                'inq_job_no_id' => 'nullable|integer',
                'inq_cust_id' => 'nullable|integer',
                'inq_date' => 'required',
                'inq_pick_req_date' => 'required',
                'inq_slot_booking' => 'nullable',
                'inq_pick_address' => 'required|string',
                'inq_drop_address' => 'required|string',
                'inq_city' => 'nullable|string',
                'inq_branch_id' => 'nullable|integer',
                'inq_package_id' => 'nullable|integer',
                'inq_pks_s_id' => 'nullable|integer',
                'inq_service_master_id' => 'nullable|integer',
                'inq_des_from_customer' => 'nullable|string',
                'inq_pickup_man_id' => 'nullable|integer',
                'inq_desk_audio_link' => 'nullable|file|mimes:mp3',
                'inq_is_confirm' => 'nullable',
                'inq_is_confirm_timedate' => 'nullable',
                'is_status' => 'nullable|string',
                'created_by' => 'nullable|integer',
            ]);

            $lastInquiry = InquiryMaster::orderBy('id', 'desc')->first();
            $lastCode = $lastInquiry ? intval(substr($lastInquiry->inq_code, 4)) : 0;
            $newCode = 'INCQ' . str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);
            $ticketCode = 'TICKET' . str_pad($lastCode + 1, 3, '0', STR_PAD_LEFT);

            $validated['inq_code'] = $newCode;
            $validated['inq_pick_tickit_code'] = $ticketCode;
            $validated['is_status'] = 0;
            $validated['created_by'] = auth()->user()->id;


            if ($request->hasFile('inq_desk_audio_link')) {
                $audioFile = $request->file('inq_desk_audio_link');

                $pickupFolder = public_path('pickup');

                if (!file_exists($pickupFolder)) {
                    mkdir($pickupFolder, 0755, true);
                }

                $fileName = time() . '_' . $audioFile->getClientOriginalName();
                $audioFile->move($pickupFolder, $fileName);

                $validated['inq_desk_audio_link'] = 'pickup/' . $fileName;
            }

            $inquiry = InquiryMaster::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pickup inquiry added successfully.',
                'data' => $inquiry
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add pickup inquiry.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}