<?php

namespace App\Http\Controllers\Api;

use App\Models\BranchMaster;
use App\Models\CityMaster;
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
    public function updateService(Request $request, $id) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $service = ServiceCategory::find($id);
            if (!$service) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
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
    public function deleteService($id) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $service = ServiceCategory::find($id);
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

    public function updateCity(Request $request, $id) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $city = CityMaster::find($id);
            if (!$city) {
                return response()->json([
                    'status' => false,
                    'message' => 'City not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'city_name' => 'required'
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
    public function deleteCity($id) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $city = CityMaster::find($id);
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


    public function updateUser(Request $request, $id) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $data = $request->only('name', 'email', 'u_fullname', 'u_current_addr', 'phone_number', 'role', 'u_adhar_photo');
            $validator = Validator::make($data, [
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

    public function userDelete($id) {
        try {
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }

            $user = User::find($id);
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
    public function updateRole(Request $request, $id) {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255', // Role name
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $role = Role::find($id);
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
}
