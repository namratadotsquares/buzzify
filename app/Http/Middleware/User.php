<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if(Auth::user()->type == 'user'){
                return $next($request);
            }
            Auth::logout();
            return redirect('user-login')->with('error','You are not authorized to access user account');
        }
        return redirect('designer-portal/login')->with('error','You have not designer access');
    }
}
