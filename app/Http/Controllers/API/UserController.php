<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $rules=[
            'name' => 'required',
            'email' => 'required|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required'
        ];
        $validate = Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['errors'=>$validate->errors()],400);
        }

        $request['password'] = bcrypt($request->password);

        $user = User::create($request->all());
        $token = openssl_random_pseudo_bytes(64);
        $token = bin2hex($token);
        return response([ 'user' => $user, 'access_token' => $token]);

    }
    public function login(Request $request)
    {
        $rules=[
            'login' => 'required',
            'password'=>'required'
        ];

        $validate = Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['errors'=>$validate->errors()],400);
        }
        $login = "";
        if (strstr($request['login'],'@')){
            $login = $request->login;

        }
        else {
            $login = $request->login;

        }

        if (!auth()->attempt([
            'email'=>$login,
            'password'=>$request->password
        ]) && !auth()->attempt([
                'username'=>$login,
                'password'=>$request->password
            ])) {
            return response(['message' => 'Invalid Credentials']);
        }


        $token = openssl_random_pseudo_bytes(64);
        $token = bin2hex($token);
        return response([ 'access_token' => $token]);


    }


}
