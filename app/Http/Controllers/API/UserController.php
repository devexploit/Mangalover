<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Cassandra\Numeric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Validator;
use App\Models\Token;

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

//        Token::create(
//            [
//                'token' => $token,
//                'user_id'=>$user['id'],
//                'is_admin'=>false
//            ]
//        );

        $app = Redis::connection();

        if($app->hget($user->id,'time') < time() || !($app->exists($user->id))) {
            if($app->hget($user->id,'time') < time()){
                $app->del($user->id);
            }
            $app->hset($user->id,'id',$user->id);
            $app->hset($user->id,'token',$token);
            $app->hset($user->id,'isadmin',0);
            $app->hset($user->id,'username',$user->username);
            $app->hset($user->id,'email',$user->email);
            $app->hset($user->id,'time',time() + 1200);

        }

        return response(['user' => $app->hgetall($user->id)]);

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

        $user = User::where('email',$login)->orWhere('username', $login)
            ->first();

      //  return $user;

//        $token2 =  Token::create(
//            [
//                'token' => $token,
//                'user_id'=>$user->id,
//                'is_admin'=>false
//            ]
//        );

        $app = Redis::connection();

           if($app->hget($user->id,'time') < time() || !($app->exists($user->id))) {
               if($app->hget($user->id,'time') < time()){
                   $app->del($user->id);
               }
                   $app->hset($user->id,'id',$user->id);
                   $app->hset($user->id,'token',$token);
                   $app->hset($user->id,'isadmin',0);
                   $app->hset($user->id,'username',$user->username);
                   $app->hset($user->id,'email',$user->email);
                   $app->hset($user->id,'time',time() + 1200);
           }

        return response(['user' => $app->hgetall($user->id)]);

    }
}
