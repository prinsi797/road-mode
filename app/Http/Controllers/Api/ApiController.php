<?php

namespace App\Http\Controllers\Api;

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

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'status' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'message' => 'user login successfully',
            'token' => $token,
        ]);
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
                $service->sc_photo = $service->sc_photo ? asset('storage/' . $service->sc_photo) : null;
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
}
