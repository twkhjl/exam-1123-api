<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function create(Request $request)
    {

        try {

            $validateRuleArr = [
                'name' => ['required', 'unique:users'],
                'email' => ['required', 'unique:users', 'email'],
                'password' => ['required', 'confirmed'],
            ];

            $validator = Validator::make($request->all(), $validateRuleArr, [

                'required' => ':attribute不可空白',
                'unique' => ':attribute已被使用',
                'confirmed' => ':attribute不一致',
                'email' => ':attribute格式不正確',
            ], [

                'name' => '用戶名稱',
                'password' => '密碼',
            ]);



            if ($validator->fails()) {


                return response()->json(['errors' => $validator->errors()]);
            };


            $user = new user();
            $fillable = collect($user->getFillable())->toArray();

            $formField = $request->only($fillable);

            $formField['password'] = bcrypt($request->password);

            $user=$user->create($formField);

            $token = auth()->login($user);



            return response()->json([
                'token' => $token,
                'expires_in' => auth()->factory()->getTTL() * 60,

                'user' => $user->makeHidden([
                    "email_verified_at",
                    "created_at",
                    "updated_at"
                ])
            ]);

        } catch (\Exception $e) {

            return
                response()->json(['error' => 'server', 'message' => $e->getMessage()]);
        }
    }
    public function change_password(Request $request)
    {

        try {

            $validateRuleArr = [
                'password' => ['required', 'confirmed'],
            ];


            $validator = Validator::make($request->all(), $validateRuleArr, [

                'required' => ':attribute不可空白',
                'confirmed' => ':attribute不一致',
            ], [
                'password' => '密碼',
            ]);



            if ($validator->fails()) {


                return response()->json(['errors' => $validator->errors()]);
            };


            $user = User::find($request->id);

            $formField['password'] = bcrypt($request->password);

            $user->update($formField);


            return response()->json([
                'user' => $user->makeHidden([
                    "email_verified_at",
                    "created_at",
                    "updated_at"
                ])
            ]);

        } catch (\Exception $e) {

            return
                response()->json(['error' => 'server', 'message' => $e->getMessage()]);
        }
    }
}
