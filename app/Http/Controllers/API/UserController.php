<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use http\Header;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Validator;
use App\Models\Token;
use Illuminate\Support\Facades\DB;

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
            "time"=>$userToken->created_at,
            "is_admin"=>0,

                "username" => $user->username
            ]
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

     if(strtotime($userToken->updated_at) + 1200 < time()){
         $token = openssl_random_pseudo_bytes(64);
         $token = bin2hex($token);

         $this->app->del($userToken->token);
         $userToken->token = $token;
         $userToken->save();
         $this->app->hmset($token,
             ["user_id"=>$user->id,
                 "token"=>$token,
                 "time"=>$userToken->updated_at,
                 "is_admin"=>0,
                 "username" => $user->username
             ]
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

        $username = $this->app->hget($request->header('Authorization'),'username');

        if(!auth()->attempt([
            'username' => $username,
            'password' => $request['password']
        ])){
            return response()->json(['error'=>'wrong password'],200);
        }

        User::where('username',$username)->first()
        ->update(
            [
                'password' => bcrypt($request->new_password)
            ]
        );

        return response()->json(['okay'=>'password changed successfully'],200);


    }

    public function resetPasswordRequest(Request $request)
    {

        $rules = [
          'email' => 'required|email'
        ];

        $validate = Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['errors'=>$validate->errors()],400);
        }

        $user = User::where('email',$request->email)->first();

        if(is_null($user)){
            return response()->json(['error'=>'data not found'],404);
        }




        $control = DB::table('password_resets')->where('email',$request->email)
            ->first();


        if(isset($control)){

            if($control->time + 900 < time()){
                DB::table('password_resets')->delete($control->id);
            }
            else {

                return response()->json(['error' => 'you should wait for a new token that has been issued recently']);

            }

        }

            $resetToken = openssl_random_pseudo_bytes(16);
            $resetToken = bin2hex($resetToken);


          DB::table('password_resets')->insert(
            [
                'email' => $request->email,
                'token'=>$resetToken,
                'time'=>time()
            ]

        );

        return response()->json(['message' => "15 minutes valid token generated",'token'=>$resetToken]);

    }

    public function resetPasswordPut(Request $request,$token)
    {

        $rules = [
            'password'=>'required|min:6',
            'password_confirmation'=>'required|min:6|same:password',
        ];

        $validate = Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['errors'=>$validate->errors()],400);
        }

        $resetToken = DB::table('password_resets')->where('token',$token)->first();

        if(is_null($resetToken)){
            return response()->json(['error'=>'data not found'],404);
        }


        $user=User::where('email',$resetToken->email)->first();
        $user->password = bcrypt($request->password);

        $user->save();
        DB::table('password_resets')->delete($resetToken->id);

        return response()->json(['message'=>'The password was changed']);

    }
}
