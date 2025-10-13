<?php

namespace App\Http\Controllers\api_v1;

use App\Http\Controllers\Controller;
use App\Logic\UserLogic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected UserLogic $userLogic)
    {
    }

    /**
     * Register a new user and issue a bearer token.
     *
     * Request: { name: string, email: string, password: string, password_confirmation: string }
     * Response: { token: string, token_type: "Bearer", user: User }
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'bail|required|string|max:35',
            'email' => 'bail|required|email|unique:users,email|max:35',
            'password' => 'bail|required|string|confirmed|min:6',
        ]);

        $user = $this->userLogic->insert(
            $validated['name'],
            $validated['email'],
            $validated['password'],
        );

        $token = $user->createToken('api_v1')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    /**
     * Login with email and password and receive a bearer token.
     *
     * Request: { email: string, password: string }
     * Response: { token: string, token_type: "Bearer", user: User }
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        /** @var User|null $user */
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is inactive',
            ], 403);
        }

        $token = $user->createToken('api_v1')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Invalidate current access token.
     * Auth: Bearer token (Sanctum)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Get the authenticated user.
     * Auth: Bearer token (Sanctum)
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
