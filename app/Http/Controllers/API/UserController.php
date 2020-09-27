<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use http\Header;
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

    public function __construct()
    {
       $this->app = Redis::connection();
    }

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

        $userToken = Token::create(
            [
                'token' => $token,
                'user_id'=>$user['id'],
                'is_admin'=>false
            ]
        );

        $this->app->hmset($userToken->token,
            ["user_id"=>$user->id,
            "token"=>$token,
            "time"=>$userToken->created_at]
        );

        return response(['user' => $user, 'token'=>$userToken->token]);
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

        $user = User::where('email',$login)->orWhere('username', $login)
            ->first();

      //  return $user;

        $userToken = Token::where('user_id',$user->id)->first();

     if(strtotime($userToken->updated_at) + 120 < time()){

         $token = openssl_random_pseudo_bytes(64);
         $token = bin2hex($token);

         $this->app->del($userToken->token);
         $userToken->token = $token;
         $userToken->save();
         $this->app->hmset($token,
             ["user_id"=>$user->id,
                 "token"=>$token,
                 "time"=>$userToken->updated_at]
         );
     }

        return response(['user' => $user, 'token'=>$userToken->token]);

    }

    public function passwordChange(Request $request)
    {

        $rules = [
          'password'=>'required|min:6',
          'password_confirmation'=>'required|min:6|same:password',
            'new_password'=>'required|min:6'
        ];

        $validate = Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['errors'=>$validate->errors()],400);
        }




//        if(!auth()->attempt([
//            'username' =>
//            'password' => $request['password']
//        ])){
//            return "gir";
//        }

    }

}
