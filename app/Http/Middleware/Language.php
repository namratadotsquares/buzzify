<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\Languages;
use anlutro\LaravelSettings\Facade as Setting;


class Language
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
        if (auth()->user()) {
            if (auth()->user()->type == 'admin') {
                Session::put('locale', Setting('preferred_site_language'));
                App::setLocale(Setting('preferred_site_language'));
            }else if (auth()->user()->type == 'user') {
                if (auth()->user()->lang_code != '') {
                    App::setLocale(auth()->user()->lang_code);
                    Session::put('locale', auth()->user()->lang_code);
                    setcookie('lang_code',auth()->user()->lang_code,time()+60*60*24*365);
                }else if(isset($_COOKIE['lang_code']) && $_COOKIE['lang_code'] != ''){
                    Session::put('locale', $_COOKIE['lang_code']);
                    App::setLocale($_COOKIE['lang_code']);
                }else{
                    App::setLocale(Setting('preferred_site_language'));
                    Session::put('locale', Setting('preferred_site_language'));
                    setcookie('lang_code',Setting('preferred_site_language'),time()+60*60*24*365);
                }
            }
        }else{
            if(isset($_COOKIE['lang_code']) && $_COOKIE['lang_code'] != ''){
                App::setLocale($_COOKIE['lang_code']);
                Session::put('locale', $_COOKIE['lang_code']);
                setcookie('lang_code',$_COOKIE['lang_code'],time()+60*60*24*365);
            }else{
                App::setLocale(Setting('preferred_site_language'));
                Session::put('locale', Setting('preferred_site_language'));
                setcookie('lang_code',Setting('preferred_site_language'),time()+60*60*24*365);
            }
        }
        return $next($request);
    }
}
