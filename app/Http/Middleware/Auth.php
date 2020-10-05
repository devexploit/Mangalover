<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;


class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        try {
            $autho = $request->header('Authorization');

            $app = Redis::connection();
            $userToken =  $app->hgetall($autho);


            if($autho == $userToken['token'] && strtotime($userToken['time']) + 1200 > time() && $userToken['is_admin'] == "0"){
                return $next($request);
            }
            $app->del($autho);
            return response(['error'=>'no user'],401);
        }

        catch (\Exception $err) {
            return response()->json(['error'=>$err]);
        }




    }
}
