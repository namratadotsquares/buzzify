<?php

namespace App\Http\Middleware;

use Closure, View, Route;

class Authenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        // echo json_encode($next($request)); die;
        if (!is_null(request()->user())) {
            return $next($request);
        } else {
            return redirect('admin-login');
        }
    }
}
