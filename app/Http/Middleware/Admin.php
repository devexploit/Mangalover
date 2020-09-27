<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class Admin
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
        $autho = $request->header('Authorization');

        $app = Redis::connection();
        $userToken =  $app->hgetall($autho);
        if($userToken){


            if($autho == $userToken['token'] && strtotime($userToken['time']) + 1200 > time() && $userToken['is_admin'] == "1"){
                return $next($request);
            }
        }
        return response(['error'=>'no user'],401);

    }
}
