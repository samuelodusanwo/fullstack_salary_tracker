<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //
    #[OA\Post(
        path: '/auth/login',
        tags: ['Authentication'],
        summary: 'Authenticate user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password')
                ]
            )
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful authentication',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'boolean', example: true),
                new OA\Property(property: 'status_code', type: 'integer', example: 200),
                new OA\Property(property: 'message', type: 'string', example: 'Operation successful'),
                new OA\Property(property: 'data', type: 'object', properties: [
                    new OA\Property(property: 'access_token', type: 'string', example: 'your_jwt_token'),
                    new OA\Property(property: 'token_type', type: 'string', example: 'bearer'),
                    new OA\Property(property: 'access_token_expires_in', type: 'integer', example: 3600),
                    new OA\Property(property: 'user', ref: '#/components/schemas/User')
                ]),
                new OA\Property(property: 'errors', type: 'null')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden access.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error.',
        content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')
    )]
    public function login(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email', // Removed 'exists:users,email' here to allow custom error messages
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Credential validation failed.',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Invalid credentials',
                'data' => null,
                'errors' => [
                    'email' => ['No user found with this email address.']
                ]
            ], 401);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Invalid credentials',
                'data' => null,
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }
        // 3. Admin Role Check (Crucial for our project scope)
        if ($user->role !== 'admin') {
            JWTAuth::invalidate(JWTAuth::getToken()); // Invalidate token for non-admin
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Unauthorized access',
                'data' => null,
                'errors' => [
                    'role' => ['You do not have permission to access this resource.']
                ]
            ], 403);
        }

        // --- Custom JWT Generation for Access Token & Refresh Token ---

        // Access Token (short-lived, e.g., 1 hour)
        $accessTokenExpiresInSeconds = config('jwt.ttl', 60) * 60; // Use config or default to 60 minutes
        $accessToken = JWTAuth::claims([
            'exp' => Carbon::now()->addSeconds($accessTokenExpiresInSeconds)->timestamp,
        ])->fromUser($user);

        // 4. Return the detailed JSON response
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Operation successful',
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'bearer',
                'access_token_expires_in' => $accessTokenExpiresInSeconds,
                'id' => $user->id,
                'name' => $user->name, // Using 'name' from our schema
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'role' => $user->role,
            ],
            'errors' => null
        ]);
    }

    #[OA\Post(
        path: '/auth/logout',
        tags: ['Authentication'],
        summary: 'Admin Logout',
        description: 'Invalidate the current JWT token, logging out the admin user.',
        security: [['bearerAuth' => []]], // Requires JWT bearer token
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully logged out',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'status_code', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Successfully logged out'),
                        new OA\Property(property: 'data', type: 'null', nullable: true),
                        new OA\Property(property: 'errors', type: 'null', nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid or missing token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'status_code', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                        new OA\Property(property: 'data', type: 'null', nullable: true),
                        new OA\Property(property: 'errors', type: 'null', nullable: true)
                    ]
                )
            )
        ]
    )]
    public function logout()
    {
        Auth::logout(); // Invalidate the token
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Successfully logged out',
            'data' => null,
            'errors' => null
        ]);
    }
    #[OA\Get(
        path: '/auth/me',
        tags: ['Authentication'],
        summary: 'Get Authenticated Admin User',
        description: 'Get the currently authenticated admin user\'s details.',
        security: [['bearerAuth' => []]], // Requires JWT bearer token
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully retrieved user',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'status_code', type: 'integer', example: 200),
                        new OA\Property(property: 'message', type: 'string', example: 'Operation successful'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User'), // Reference User schema directly in data
                        new OA\Property(property: 'errors', type: 'null', nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Invalid or missing token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'status_code', type: 'integer', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                        new OA\Property(property: 'data', type: 'null', nullable: true),
                        new OA\Property(property: 'errors', type: 'null', nullable: true)
                    ]
                )
            )
        ]
    )]
    public function me()
    {
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Operation successful',
            'data' => JWTAuth::user(),
            'errors' => null
        ]);
    }
}
