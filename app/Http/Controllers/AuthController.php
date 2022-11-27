<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions;

use Illuminate\Http\Request;

class AuthController extends Controller
{
   /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
        $this->middleware('jwtauth', ['except' => ['login','refresh']]);
    }

    public function test(){
        return response()->json(auth()->check());
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

        $loginField = request('name');
        $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        request()->merge([$loginType => $loginField]);

        $credentials = [$loginType=>request('name'), 'password'=>request('password')];

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        return response()->json([
            'token'=>$token,
            'expires_in' => auth()->factory()->getTTL() * 60,

            'user'=>$user->makeHidden([
                "email_verified_at",
                "created_at",
                "updated_at"])
        ]);

        ;

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['error'=>null,'message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {

        try {
            return $this->respondWithToken(auth()->refresh());
            // JWTAuth::parseToken()->authenticate();

        } catch (\Exception $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException &&
            !$e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException &&
            !$e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){

                return $this->respondWithToken(auth()->refresh());

            }
            return response()->json(['error'=>'invalid','message'=>'old token invalid or being blacklisted'],401);

        }

    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
