<?php

namespace Modules\Auth\Controllers;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Models\User;

class AuthController extends Controller
{
    /*
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name'              => $request->name,
                'organization_name' => $request->organization_name ?? '',
                'email'             => $request->email,
                'password'          => Hash::make($request->password),
            ]);

            return ApiResponse::success('Registration successful!', [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]);
        } catch (Exception $e) {
            return ApiResponse::error('Unable to process the request.', [
                'details' => [$e->getMessage()],
            ], 422);
        }
    }

    /** Login a user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid credentials.', [
                    'details' => ['The provided credentials are incorrect.'],
                ], 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            // return $user;

            return ApiResponse::success('Login successful!', [
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
            ]);
        } catch (Exception $e) {
            return ApiResponse::error('Unable to process the request.', [
                'details' => ['An error occurred while logging in. Please try again later.'],
            ], 422);
        }
    }

    /**
     * Logout the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return ApiResponse::success('Logout successful!', []);
        } catch (Exception $e) {
            return ApiResponse::error('Unable to process the request.', [
                'details' => ['An error occurred while logging out. Please try again later.'],
            ], 422);
        }
    }
}
