<?php

namespace Megaads\DealsPage\Middlewares;

use Closure;
use Config;

class DealPageAuth
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // Status flag:
        $loginSuccessful = false;
        // Check username and password:
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){

            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];

            if ($username == Config::get('subscriber.basicAuthentication.username', 'api')
                && $password == Config::get('subscriber.basicAuthentication.password', '123@123a')){
                $loginSuccessful = true;
            }
        }
        if ($loginSuccessful){
            return $next($request);
        }else{
            return response('Unauthorized.', 401,["WWW-Authenticate"=>"Basic realm='Couponforless'"]);
        }
    }

}