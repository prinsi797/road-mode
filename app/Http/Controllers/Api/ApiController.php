<?php

namespace App\Http\Controllers\Api;

use App\Models\AreaMaster;
use App\Models\BranchMaster;
use App\Models\CityMaster;
use App\Models\CompanyMaster;
use App\Models\CustomerMaster;
use App\Models\InquiryMaster;
use App\Models\packageMaster;
use App\Models\ModelMaster;
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

    // public function login(Request $request) {
    //     $credentials = $request->only('email', 'password');

    //     $validator = Validator::make($credentials, [
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:6|max:50'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->messages()], 200);
    //     }

    //     try {
    //         if (! $token = JWTAuth::attempt($credentials)) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Login credentials are invalid.',
    //             ], 400);
    //         }
    //     } catch (JWTException $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Could not create token.',
    //         ], 500);
    //     }

    //     $user = Auth::user();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'User login successful',
    //         'token' => $token,
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'phone_number' => $user->phone_number,
    //             'role' => $user->roles->pluck('name')->first()
    //         ]
    //     ], 200);
    // }
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        // Validation
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            // Check in User Table
            if ($token = JWTAuth::attempt($credentials)) {
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

            // Check in Customer Table if User Login Fails
            $customerCredentials = [
                'cust_email' => $request->email,
                'password' => $request->password
            ];

            if ($token = Auth::guard('customer-api')->attempt($customerCredentials)) {
                $customer = Auth::guard('customer-api')->user();
                return response()->json([
                    'success' => true,
                    'message' => 'Customer login successful',
                    'token' => $token,
                    'customer' => [
                        'id' => $customer->id,
                        'cust_name' => $customer->cust_name,
                        'cust_email' => $customer->cust_email,
                        'cust_mobile_no' => $customer->cust_mobile_no,
                        'role' => "customer",
                    ]
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Login credentials are invalid.',
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Could not create token.',
            ], 500);
        }
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            // $user = auth()->user();
            $user = Auth::guard('api')->user();
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
            $authUser = auth()->user();
            if (!$authUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized access',
                ], 401);
            }
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
                'inq_cust_id' => 'nullable',
                'inq_date' => 'required',
                'inq_pick_req_date' => 'required',
                'inq_slot_booking' => 'nullable',
                'inq_pick_address' => 'required|string',
                'inq_drop_address' => 'required|string',
                'inq_city' => 'nullable|string',
                'inq_branch_id' => 'nullable',
                'inq_package_id' => 'nullable',
                'inq_pks_s_id' => 'nullable',
                'inq_service_master_id' => 'nullable',
                'inq_des_from_customer' => 'nullable|string',
                'inq_pickup_man_id' => 'nullable|integer',
                'inq_desk_audio_link' => 'nullable|file|mimes:mp3',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'company_id' => 'nullable|exists:company_master,id',
                'model_id' => 'nullable|exists:model_master,id',
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
            $validated['is_status'] = 1;
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

            $inquiryWithRelations = InquiryMaster::with(['branch', 'package', 'customer', 'company', 'model', 'vehicle'])->find($inquiry->id);

            return response()->json([
                'success' => true,
                'message' => 'Pickup inquiry added successfully.',
                'data' => $inquiryWithRelations
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add pickup inquiry.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // customer register
    public function customerRegister(Request $request) {

        // $user = auth()->user();
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'cust_name' => 'required|string|max:255',
            'cust_city' => 'nullable|string|max:255',
            'cust_res_address' => 'nullable|string|max:255',
            'cust_pick_default_addr' => 'nullable|string|max:255',
            'cust_email' => 'nullable|email|max:255|unique:customer_master',
            'password' => 'required|string|min:6',
            'cust_for_branch_id' => 'required|exists:branch_master,id',
            'cust_package_id' => 'nullable|exists:package_master_master,id',
            'cust_mobile_no' => 'nullable|string|max:15',
            'cust_whtapp_no' => 'nullable|string|max:15',
            'cust_com_id' => 'required|exists:company_master,id',
            'cust_model_id' => 'required|exists:model_master,id',
            'cust_vehicle_no' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }
        $cust_code = $this->generateCustomerCode();

        try {
            $customer = new CustomerMaster();
            $customer->cust_code = $cust_code;
            $customer->cust_name = $request->cust_name;
            $customer->cust_city = $request->cust_city;
            $customer->cust_res_address = $request->cust_res_address;
            $customer->cust_pick_default_addr = $request->cust_pick_default_addr;
            $customer->cust_email = $request->cust_email;
            $customer->password = bcrypt($request->password);
            $customer->cust_for_branch_id = $request->cust_for_branch_id;
            $customer->cust_package_id = $request->cust_package_id;
            $customer->is_package_selected = $request->is_package_selected;
            $customer->cust_pack_start_date = $request->cust_pack_start_date;
            $customer->cust_pack_end_date = $request->cust_pack_end_date;
            $customer->cust_is_pack_renew = $request->cust_is_pack_renew;
            $customer->cust_is_noti_req = $request->cust_is_noti_req;
            $customer->cust_mobile_no = $request->cust_mobile_no;
            $customer->cust_whtapp_no = $request->cust_whtapp_no;
            $customer->cust_com_id = $request->cust_com_id;
            $customer->cust_model_id = $request->cust_model_id;
            $customer->cust_vehicle_no = $request->cust_vehicle_no;
            $customer->is_pack_expire = $request->is_pack_expire;
            $customer->is_renreable = $request->is_renreable;
            $customer->is_status = 1;
            // $customer->created_by = $user->name;
            $customer->save();

            return response()->json([
                'status' => true,
                'message' => 'Customer registered successfully',
                'data' => $customer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error during registration: ' . $e->getMessage(),
            ], 500);
        }
    }
    private function generateCustomerCode() {
        $lastCustomer = CustomerMaster::latest('id')->first();
        if ($lastCustomer) {
            $lastCode = $lastCustomer->cust_code;
            $numericPart = (int) substr($lastCode, 4);
            $nextNumericPart = $numericPart + 1;
            $newCode = 'CUST' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            $newCode = 'CUST001';
        }

        return $newCode;
    }
    public function customerLogin(Request $request) {
        $credentials = $request->only('cust_email', 'password');

        // Validation
        $validator = Validator::make($credentials, [
            'cust_email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            if (!$token = Auth::guard('customer-api')->attempt($credentials)) {
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

        $customer = Auth::guard('customer-api')->user();

        return response()->json([
            'success' => true,
            'message' => 'Customer login successful',
            'token' => $token,
            'customer' => [
                'id' => $customer->id,
                'cust_name' => $customer->cust_name,
                'cust_email' => $customer->cust_email,
                'cust_mobile_no' => $customer->cust_mobile_no,
            ]
        ], 200);
    }
    public function getAuthenticatedCustomer(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'cust_name' => $customer->cust_name,
                    'cust_email' => $customer->cust_email,
                    'cust_mobile_no' => $customer->cust_mobile_no,
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided'
            ], 401);
        }
    }

    public function getBranch(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $branches = BranchMaster::with('citymaster', 'areamaster')->get()->map(function ($branch) {
                $branch->br_photo = asset($branch->br_photo);
                $branch->br_sign = asset($branch->br_sign);
                return $branch;
            });;

            return response()->json([
                'status' => true,
                'message' => 'Branch retrieved successfully',
                'data' => $branches,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createBranch(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $data = $request->only('br_address', 'br_owner_name', 'br_owner_email', 'br_mobile', 'br_city', 'br_photo', 'br_sign', 'br_state', 'br_start_Date', 'br_end_date', 'br_renew_year', 'br_connection_link', 'br_db_name', 'br_user_name', 'br_password', 'br_city_id', 'br_area_id', 'br_pin_code');

            $validator = Validator::make($data, [
                'br_address' => 'required|string|max:255',
                'br_owner_name' => 'required|string|max:255',
                'br_owner_email' => 'required|email|max:255',
                'br_mobile' => 'required|string|max:15',
                'br_city' => 'nullable',
                'br_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'br_sign' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'br_state' => 'required|string|max:255',
                'br_start_Date' => 'required|date',
                'br_end_date' => 'nullable|date|after_or_equal:br_start_Date',
                'br_renew_year' => 'nullable',
                'br_connection_link' => 'nullable',
                'br_db_name' => 'nullable|string|max:255',
                'br_user_name' => 'nullable|string|max:255',
                'br_password' => 'nullable|string|max:255',
                'br_city_id' => 'required|exists:city_master,id',
                'br_area_id' => 'required|exists:area_master,id',
                'br_pin_code' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }
            $branch_code = $this->generateBranchCode();

            $photoPath = null;
            if ($request->hasFile('br_photo')) {
                $photoFile = $request->file('br_photo');
                $photoDirectory = public_path('branches');

                if (!file_exists($photoDirectory)) {
                    mkdir($photoDirectory, 0777, true);
                }

                $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move($photoDirectory, $photoName);
                $photoPath = 'branches/' . $photoName;
            }

            $signPath = null;
            if ($request->hasFile('br_sign')) {
                $signFile = $request->file('br_sign');
                $signDirectory = public_path('signatures');

                if (!file_exists($signDirectory)) {
                    mkdir($signDirectory, 0777, true);
                }

                $signName = uniqid() . '_' . time() . '.' . $signFile->getClientOriginalExtension();
                $signFile->move($signDirectory, $signName);
                $signPath = 'signatures/' . $signName;
            }

            $branch = BranchMaster::create([
                'br_code' => $branch_code,
                'br_address' => $request->br_address,
                'br_owner_name' => $request->br_owner_name,
                'br_owner_email' => $request->br_owner_email,
                'br_mobile' => $request->br_mobile,
                'br_city' => $request->br_city,
                'br_photo' => $photoPath,
                'br_sign' => $signPath,
                'br_state' => $request->br_state,
                'br_start_Date' => $request->br_start_Date,
                'br_end_date' => $request->br_end_date,
                'br_renew_year' => $request->br_renew_year,
                'br_connection_link' => $request->br_connection_link,
                'br_db_name' => $request->br_db_name,
                'br_user_name' => $request->br_user_name,
                'br_password' => bcrypt($request->br_password),
                'br_city_id' => $request->br_city_id,
                'br_area_id' => $request->br_area_id,
                'br_pin_code' => $request->br_pin_code,
                'is_status' => 1,
                'created_by' => auth()->user()->name,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Branch add successfully',
                'data' => $branch
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching City',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function generateBranchCode() {
        $lastBranch = BranchMaster::latest('id')->first();
        return 'BR' . str_pad(($lastBranch->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }

    public function updateBranch(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            $validator = Validator::make($request->all(), [
                'br_address' => 'required|string|max:255',
                'br_owner_name' => 'required|string|max:255',
                'br_owner_email' => 'required|email|max:255',
                'br_mobile' => 'required|string|max:15',
                'br_city' => 'nullable',
                'br_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'br_sign' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'br_state' => 'required|string|max:255',
                'br_start_Date' => 'required|date',
                'br_end_date' => 'nullable|date|after_or_equal:br_start_Date',
                'br_renew_year' => 'nullable',
                'br_connection_link' => 'nullable',
                'br_db_name' => 'nullable|string|max:255',
                'br_user_name' => 'nullable|string|max:255',
                'br_password' => 'nullable|string|max:255',
                'br_city_id' => 'required|exists:city_master,id',
                'br_area_id' => 'required|exists:area_master,id',
                'br_pin_code' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $branchId = $request->input('branch_id');
            $branch = BranchMaster::find($branchId);

            if (!$branch) {
                return response()->json([
                    'status' => false,
                    'message' => 'Area not found',
                ], 404);
            }
            if ($request->hasFile('br_photo')) {
                if ($branch->br_photo) {
                    $oldPhotoPath = public_path($branch->br_photo);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $photoFile = $request->file('br_photo');
                $photoName = time() . '_photo.' . $photoFile->getClientOriginalExtension();
                $photoPath = 'branches/' . $photoName;
                $photoFile->move(public_path('branches'), $photoName);
            } else {
                $photoPath = $branch->br_photo;
            }

            // Handling br_sign
            if ($request->hasFile('br_sign')) {
                if ($branch->br_sign) {
                    $oldSignPath = public_path($branch->br_sign);
                    if (file_exists($oldSignPath)) {
                        unlink($oldSignPath);
                    }
                }
                $signFile = $request->file('br_sign');
                $signName = time() . '_sign.' . $signFile->getClientOriginalExtension();
                $signPath = 'signatures/' . $signName;
                $signFile->move(public_path('signatures'), $signName);
            } else {
                $signPath = $branch->br_sign;
            }

            $branch->br_address = $request->br_address;
            $branch->br_owner_name = $request->br_owner_name;
            $branch->br_owner_email = $request->br_owner_email;
            $branch->br_mobile = $request->br_mobile;
            $branch->br_city = $request->br_city;
            $branch->br_photo = $photoPath;
            $branch->br_sign = $signPath;
            $branch->br_state = $request->br_state;
            $branch->br_start_Date = $request->br_start_Date;
            $branch->br_end_date = $request->br_end_date;
            $branch->br_renew_year = $request->br_renew_year;
            $branch->br_connection_link = $request->br_connection_link;
            $branch->br_db_name = $request->br_db_name;
            $branch->br_user_name = $request->br_user_name;
            $branch->br_password = $request->filled('br_password') ? bcrypt($request->br_password) : $branch->br_password; // Password update only if changed
            $branch->br_city_id = $request->br_city_id;
            $branch->br_area_id = $request->br_area_id;
            $branch->br_pin_code = $request->br_pin_code;
            $branch->is_status = 1;
            $branch->modified_by = Auth::guard('customer-api')->user()->cust_name;
            $branch->save();

            return response()->json([
                'status' => true,
                'message' => 'Area updated successfully',
                'data' => $branch,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching City',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function branchDelete(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            $validator = Validator::make($request->all(), [
                'branch_id' => 'required|exists:branch_master,id'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], 422);
            }
            $branchId = $request->input('branch_id');
            $branch = BranchMaster::find($branchId);
            if (!$branch) {
                return response()->json([
                    'status' => false,
                    'message' => 'Branch not found',
                ], 404);
            }
            // $user->delete();
            $branch->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'Branch deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // without token
    public function Branch(Request $request) {
        try {

            $branches = BranchMaster::with('citymaster', 'areamaster')->get()->map(function ($branch) {
                $branch->br_photo = asset($branch->br_photo);
                $branch->br_sign = asset($branch->br_sign);
                return $branch;
            });;

            return response()->json([
                'status' => true,
                'message' => 'Branch retrieved successfully',
                'data' => $branches,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function Packages(Request $request) {
        try {

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

    public function Companies(Request $request) {
        try {
            $company = CompanyMaster::get()->map(function ($company) {
                $company->com_logo = asset($company->com_logo);
                return $company;
            });;

            return response()->json([
                'status' => true,
                'message' => 'Company retrieved successfully',
                'data' => $company,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function Model(Request $request) {
        try {

            $models = ModelMaster::with('companymaster')->get()->map(function ($model) {
                $model->model_photo = asset($model->model_photo);
                return $model;
            });;

            return response()->json([
                'status' => true,
                'message' => 'Model retrieved successfully',
                'data' => $models,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMyRequest() {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $inquiries = DB::table('inquiry_master')
                ->where('inq_cust_id', $customer->id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $inquiries
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCompany(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'com_name' => 'required|string|max:255',
                'vehical_id' => 'required|exists:vehicles,id',
                'com_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $companyId = $request->input('company_id');
            $company = CompanyMaster::find($companyId);

            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company not found',
                ], 404);
            }
            if ($request->hasFile('com_logo')) {
                if ($company->com_logo) {
                    $oldPhotoPath = public_path($company->com_logo);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                $photoFile = $request->file('com_logo');
                $photoName = time() . '_photo.' . $photoFile->getClientOriginalExtension();
                $photoPath = 'company/' . $photoName;
                $photoFile->move(public_path('company'), $photoName);
            } else {
                $photoPath = $company->com_logo;
            }

            $company->com_name = $request->com_name;
            $company->vehical_id = $request->vehical_id;
            $company->com_logo = $photoPath;
            $company->is_status = 1;
            $company->modified_by = Auth::guard('customer-api')->user()->cust_name;
            $company->save();

            return response()->json([
                'status' => true,
                'message' => 'Company updated successfully',
                'data' => [
                    'id' => $company->id,
                    'com_code' => $company->com_code,
                    'com_name' => $company->com_name,
                    'com_logo' => url($company->com_logo),
                    'is_status' => $company->is_status,
                    'created_by' => $company->created_by,
                    'modified_by' => $company->modified_by,
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,
                    'vehical_id' => $company->vehical_id
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching Company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCompany(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:company_master,id'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], 422);
            }
            $companyId = $request->input('company_id');
            $company = CompanyMaster::find($companyId);
            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company not found',
                ], 404);
            }
            // $user->delete();
            $company->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'Company deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createModel(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $data = $request->only('model_name', 'model_photo', 'com_id', 'model_description');

            $validator = Validator::make($data, [
                'model_name' => 'required|string|max:255',
                'model_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'com_id' => 'required|exists:company_master,id',
                'model_description' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }
            $model_code = $this->generateModelCode();

            $photoPath = null;
            if ($request->hasFile('model_photo')) {
                $photoFile = $request->file('model_photo');
                $photoDirectory = public_path('models');

                if (!file_exists($photoDirectory)) {
                    mkdir($photoDirectory, 0777, true);
                }

                $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move($photoDirectory, $photoName);
                $photoPath = 'models/' . $photoName;
            }

            $model = ModelMaster::create([
                'model_code' => $model_code,
                'model_name' => $request->model_name,
                'model_photo' => $photoPath,
                'com_id' => $request->com_id,
                'model_description' => $request->model_description,
                'is_status' => 1,
                'created_by' => Auth::guard('customer-api')->user()->cust_name,
            ]);
            $model->load('companymaster');
            return response()->json([
                'status' => true,
                'message' => 'Model add successfully',
                'data' => [
                    'model_code' => $model->model_code,
                    'model_name' => $model->model_name,
                    'model_photo' => $model->model_photo ? asset($model->model_photo) : null, // Full image URL
                    'com_id' => $model->com_id,
                    'model_description' => $model->model_description,
                    'created_by' => $model->created_by,
                    'company_details' => $model->companymaster ? [
                        'company_id' => $model->companymaster->id,
                        'company_name' => $model->companymaster->com_name,
                        'company_code' => $model->companymaster->com_code,
                        'company_logo' => $model->companymaster->com_logo ? asset($model->companymaster->com_logo) : null,
                    ] : null,
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching Model',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    private function generateModelCode() {
        $lastBranch = ModelMaster::latest('id')->first();
        return 'MODEL' . str_pad(($lastBranch->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }

    public function createPackage(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $data = $request->only('pack_name', 'pack_other_faci', 'pack_description', 'pack_net_amt', 'pack_duration', 'package_logo', 'service_id');

            $serviceIds = $request->service_id;

            if (is_string($serviceIds)) {
                $serviceIds = json_decode($serviceIds, true);
            }

            if (!is_array($serviceIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid service_id format. It should be an array.',
                ], 400);
            }

            // Convert service_id array to a comma-separated string
            $serviceIdsString = implode(',', $serviceIds);
            // Validate request data
            $validator = Validator::make($data, [
                'pack_name' => 'required|string|max:255',
                'pack_other_faci' => 'required|string',
                'pack_description' => 'required',
                'pack_net_amt' => 'required',
                'pack_duration' => 'nullable',
                'package_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'service_id' => 'required|array',
                'service_id.*' => 'required|exists:service_cat_master,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            // Generate package code
            $pack_code = $this->generatePackageCode();

            $photoPath = null;
            if ($request->hasFile('package_logo')) {
                $photoFile = $request->file('package_logo');
                $photoDirectory = public_path('packages');

                if (!file_exists($photoDirectory)) {
                    mkdir($photoDirectory, 0777, true);
                }

                $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move($photoDirectory, $photoName);
                $photoPath = 'packages/' . $photoName;
            }

            // Store package data in the database
            $package = packageMaster::create([
                'pack_code' => $pack_code,
                'service_id' => $serviceIdsString, // Store as a comma-separated string
                'pack_name' => $request->pack_name,
                'pack_duration' => $request->pack_duration,
                'pack_other_faci' => $request->pack_other_faci,
                'pack_description' => $request->pack_description,
                'pack_net_amt' => $request->pack_net_amt,
                'package_logo' => $photoPath,
                'is_status' => 1,
                'created_by' => $customer->cust_name,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Package added successfully',
                'data' => $package,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the package',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    private function generatePackageCode() {
        $lastBranch = packageMaster::latest('id')->first();
        return 'PACK' . str_pad(($lastBranch->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);
    }

    public function updatePackage(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:package_master_master,id',
                'pack_name' => 'sometimes|string|max:255',
                'pack_other_faci' => 'sometimes|string',
                'pack_description' => 'sometimes',
                'pack_net_amt' => 'sometimes',
                'pack_duration' => 'nullable',
                'package_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'service_id' => 'sometimes|array',
                'service_id.*' => 'exists:service_cat_master,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->messages()], 200);
            }

            // Fetch the package to update
            $package = packageMaster::findOrFail($request->package_id);

            // Handle service_id array
            $serviceIds = $request->service_id;
            if (is_string($serviceIds)) {
                $serviceIds = json_decode($serviceIds, true);
            }
            if (is_array($serviceIds)) {
                $serviceIds = implode(',', $serviceIds);
            }

            // Handle package_logo upload if provided
            if ($request->hasFile('package_logo')) {
                $photoFile = $request->file('package_logo');
                $photoDirectory = public_path('packages');

                if (!file_exists($photoDirectory)) {
                    mkdir($photoDirectory, 0777, true);
                }

                $photoName = uniqid() . '_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move($photoDirectory, $photoName);
                $package->package_logo = 'packages/' . $photoName;
            }

            // Update package fields
            $package->update(array_filter([
                'pack_name' => $request->pack_name,
                'pack_duration' => $request->pack_duration,
                'pack_other_faci' => $request->pack_other_faci,
                'pack_description' => $request->pack_description,
                'pack_net_amt' => $request->pack_net_amt,
                'service_id' => $serviceIds,
                'updated_by' => $customer->cust_name,
            ]));

            return response()->json([
                'status' => true,
                'message' => 'Package updated successfully',
                'data' => $package,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the package',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePackage(Request $request) {
        try {
            $customer = Auth::guard('customer-api')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:package_master_master,id'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors(),
                ], 422);
            }
            $packageId = $request->input('package_id');
            $package = packageMaster::find($packageId);
            if (!$package) {
                return response()->json([
                    'status' => false,
                    'message' => 'Package not found',
                ], 404);
            }
            // $user->delete();
            $package->forceDelete();

            return response()->json([
                'status' => true,
                'message' => 'Package deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the Package',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
