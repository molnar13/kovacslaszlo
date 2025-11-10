<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @api {post} /api/register Register new user
     * @apiName Register
     * @apiGroup Authentication
     * @apiVersion 1.0.0
     * @apiDescription Register a new user account
     *
     * @apiBody {String} name User's full name (required, max 255 characters)
     * @apiBody {String} email User's email address (required, unique, valid email format)
     * @apiBody {String} password User's password (required, min 8 characters)
     * @apiBody {String} password_confirmation Password confirmation (required, must match password)
     *
     * @apiSuccess {Object} user User object
     * @apiSuccess {Number} user.id User ID
     * @apiSuccess {String} user.name User's name
     * @apiSuccess {String} user.email User's email
     * @apiSuccess {String} token Authentication token
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "created_at": "2024-01-15T10:00:00.000000Z",
     *         "updated_at": "2024-01-15T10:00:00.000000Z"
     *       },
     *       "token": "1|abcdefghijklmnopqrstuvwxyz123456789"
     *     }
     *
     * @apiError (422) {Object} errors Validation errors
     * @apiErrorExample {json} Validation Error:
     *     HTTP/1.1 422 Unprocessable Entity
     *     {
     *       "message": "The email has already been taken.",
     *       "errors": {
     *         "email": ["The email has already been taken."]
     *       }
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -X POST http://localhost:8000/api/register \
     *       -H "Content-Type: application/json" \
     *       -d '{
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "password": "password123",
     *         "password_confirmation": "password123"
     *       }'
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @api {post} /api/login Login user
     * @apiName Login
     * @apiGroup Authentication
     * @apiVersion 1.0.0
     * @apiDescription Authenticate user and receive access token
     *
     * @apiBody {String} email User's email address (required)
     * @apiBody {String} password User's password (required)
     *
     * @apiSuccess {Object} user User object
     * @apiSuccess {Number} user.id User ID
     * @apiSuccess {String} user.name User's name
     * @apiSuccess {String} user.email User's email
     * @apiSuccess {String} token Authentication token
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "created_at": "2024-01-15T10:00:00.000000Z",
     *         "updated_at": "2024-01-15T10:00:00.000000Z"
     *       },
     *       "token": "2|zyxwvutsrqponmlkjihgfedcba987654321"
     *     }
     *
     * @apiError (422) {String} message Invalid credentials
     * @apiErrorExample {json} Invalid Credentials:
     *     HTTP/1.1 422 Unprocessable Entity
     *     {
     *       "message": "The provided credentials are incorrect."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -X POST http://localhost:8000/api/login \
     *       -H "Content-Type: application/json" \
     *       -d '{
     *         "email": "john@example.com",
     *         "password": "password123"
     *       }'
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * @api {post} /api/logout Logout user
     * @apiName Logout
     * @apiGroup Authentication
     * @apiVersion 1.0.0
     * @apiDescription Revoke user's current access token
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiSuccess {String} message Success message
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "Logged out successfully"
     *     }
     *
     * @apiError (401) {String} message Unauthorized
     * @apiErrorExample {json} Unauthorized:
     *     HTTP/1.1 401 Unauthorized
     *     {
     *       "message": "Unauthenticated."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -X POST http://localhost:8000/api/logout \
     *       -H "Authorization: Bearer YOUR_TOKEN"
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * @api {get} /api/user Get authenticated user
     * @apiName GetUser
     * @apiGroup Authentication
     * @apiVersion 1.0.0
     * @apiDescription Get the currently authenticated user's information
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
     *     }
     *
     * @apiSuccess {Number} id User ID
     * @apiSuccess {String} name User's name
     * @apiSuccess {String} email User's email
     * @apiSuccess {String} created_at Account creation timestamp
     * @apiSuccess {String} updated_at Last update timestamp
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "created_at": "2024-01-15T10:00:00.000000Z",
     *       "updated_at": "2024-01-15T10:00:00.000000Z"
     *     }
     *
     * @apiError (401) {String} message Unauthorized
     * @apiErrorExample {json} Unauthorized:
     *     HTTP/1.1 401 Unauthorized
     *     {
     *       "message": "Unauthenticated."
     *     }
     *
     * @apiExample {curl} Example usage:
     *     curl -X GET http://localhost:8000/api/user \
     *       -H "Authorization: Bearer YOUR_TOKEN"
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}