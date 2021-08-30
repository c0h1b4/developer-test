<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use Illuminate\Support\Arr;
use JWTAuth;

class UserAuthController extends Controller
{
/**
 * @OA\Schema(
 *  schema="User",
 *  allOf={
 *    @OA\Schema(
 *     @OA\Property(property="name", type="string", format="string"),
 *     @OA\Property(property="email", type="string", format="string"),
 *     @OA\Property(property="updated_at", type="string", format="string"),
 *     @OA\Property(property="created_at", type="string", format="string"),
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="balance", type="integer", format="int64"),
 *   )}
 * )
 * @OA\Post(
 * path="/api/register",
 * summary="Sign in",
 * description="Register user by name, email, password and password_confirmation",
 * operationId="authLogin",
 * tags={"auth"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass user credentials",
 *    @OA\JsonContent(
 *       required={"name","email","password","password_confirmation"},
 *       @OA\Property(property="name", type="string", format="text", example="Test User"),
 *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
 *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
 *       @OA\Property(property="password_confirmation", type="string", format="password", example="PassWord12345"),
 *    ),
 * ),
 *   @OA\Response(
 *     response=201,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="message", type="string", format="text", example="User created successfully"),
 *        @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
 *     )
 *  ),
 * @OA\Response(
 *    response=422,
 *    description="Wrong data response",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="multiple errors")
 *        )
 *     )
 * )
 */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a User
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:5|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Get a JWT token after successful login
     * @return \Illuminate\Http\JsonResponse
     */
/**
* @OA\Post(
 * path="/api/login",
 * summary="Log in",
 * description="Log user by email and password",
 * operationId="authLogin",
 * tags={"auth"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass user credentials",
 *    @OA\JsonContent(
 *       required={"email","password"},
 *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
 *       @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
 *    ),
 * ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="access_token", type="string", format="text", example="JWT Json Web Token"),
 *        @OA\Property(property="token_type", type="string", format="text", example="Bearer"),
 *        @OA\Property(property="expires_in", type="integer", format="int64", example="3600"),
 *        @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
 *     )
 *  ),
 * @OA\Response(
 *    response=422,
 *    description="Wrong data response",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="multiple errors")
 *        )
 *     )
 * )
 */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = JWTAuth::attempt($validator->validated())) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid email and password.', 'error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

     /**
     * Get the token array structure.
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }


    /**
     * Refresh a JWT token
     * @return \Illuminate\Http\JsonResponse
     */
/**
 * @OA\Post(
 * path="/api/refresh",
 * summary="Refresh token",
 * description="Refresh token",
 * operationId="authLogin",
 * tags={"auth"},
 * security={ {"bearer": {} }},
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="access_token", type="string", format="text", example="JWT Json Web Token"),
 *        @OA\Property(property="token_type", type="string", format="text", example="Bearer"),
 *        @OA\Property(property="expires_in", type="integer", format="int64", example="3600"),
 *        @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
 *     )
 *  ),
 * @OA\Response(
 *    response=401,
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated"),
 *    )
 *  )
 * )
 */
     public function refresh() {
        return $this->createNewToken(Auth::refresh());
    }


    /**
     * Get the Auth user using token.
     * @return \Illuminate\Http\JsonResponse
     */
/**
 * @OA\GET(
 * path="/api/user",
 * summary="User information",
 * description="User information",
 * operationId="authLogin",
 * tags={"auth"},
 * security={ {"bearer": {} }},
 * @OA\Response(
 *   response=200,
 *   description="Success",
 *   @OA\JsonContent(
 *    @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
 *    )
 * ),
 * @OA\Response(
 *    response=401,
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated"),
 *    )
 * )
 * )
 */
     public function user() {
        return response()->json(auth()->user());
    }


    /**
     * Logout user (Invalidate the token).
     * @return \Illuminate\Http\JsonResponse
     */
    /**
 * @OA\Post(
 * path="/api/logout",
 * summary="Logout user",
 * description="Logout the actual user",
 * operationId="authLogin",
 * tags={"auth"},
 * security={ {"bearer": {} }},
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *        @OA\Property(property="status", type="string", format="text", example="success"),
 *        @OA\Property(property="message", type="string", format="text", example="User logged out succesfully"),
 *     )
 *  ),
 * @OA\Response(
 *    response=401,
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Unauthenticated"),
 *    )
 *   )
 * )
 */
    public function logout() {
        Auth::logout();
        return response()->json(['status' => 'success', 'message' => 'User logged out successfully']);
    }
}
