<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $email = $request->input('email');

        if (RateLimiter::tooManyAttempts($email, 5)) {
            return response()->json(['error' => 'Too many login attempts. Please try again later.'], 429);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|string|min:5|max:50',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // TODO: make login with username with email
            // $login_type = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            // $credentials = [
            //     $login_type => $request->input('login'),
            //     'password' => $request->input('password')
            // ];


            $credentials = request(['email', 'password']);

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $user = auth()->user();
            $personalAccessToken = $this->createPersonalAccessToken($user, $token);

            return $this->response()->json([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => '1 Day',
                'user' => $user,
                'personal_access_token' => $personalAccessToken
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|string|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // TODO: Send verification email
            // $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'User successfully! registered',
                'user' => $user
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while registering. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
    private function createPersonalAccessToken($user, string $token): JsonResponse
    {
        // Store the token in the database
        $personalAccessToken = PersonalAccessToken::create([
            'user_id' => $user->id,
            'name' => 'Login Token',
            'token' => $token,
            'expires_at' => now()->addDays(1),
        ]);

        return response()->json(['token' => $token, 'token_type' => 'Bearer Token']);
    }

    public function revokePersonalAccessToken(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid token provided',
                'errors' => $validator->errors()
            ], 422);
        }

        // Retrieve the token from the request
        $token = $request->input('token');

        $personalAccessToken = PersonalAccessToken::where('user_id', $user->id)
            ->where('token', $token)
            ->first();

        if ($personalAccessToken) {
            $personalAccessToken->delete();
            return response()->json(['message' => 'Token revoked successfully']);
        }

        return response()->json(['message' => 'Token not found'], 404);
    }
}
