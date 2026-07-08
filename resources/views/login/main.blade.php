@extends('../layout/' . $layout)

@section('head')
    <title>{{__('admin.admin_login')}} - {{setting('site_name')}}</title>
@endsection

@section('content')
<style type="text/css">
    .card-box{
        padding: 20px;
        box-shadow: 0px 0px 12px 0px rgb(120 146 141 / 6%);
        -webkit-border-radius: 15px;
        border-radius: 15px;
        -moz-border-radius: 5px;
        background-clip: padding-box;
        background-color: #fff;
        float: left;
        /*width: 100%;*/
        border: 1px solid #DCDFE8;
    }
</style>
    <?php 
        $userData = DB::table('users')->where('id',1)->first();
    ?>
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="{{url('/')}}" class="-intro-x flex items-center pt-5">
                    
                    <!--<img class="width-15"  src="{{asset('upload/logo')}}/{{setting('site_logo')}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">-->
                    <img class="width-15"  src="{{asset('upload/logo/buzzify-logo-white.png')}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                    <span class="text-white text-lg ml-3">
                    </span>
                </a>
                <div class="my-auto">
                    <img class="-intro-x w-1/2 -mt-16" src="{{ asset('dist/images/illustration.svg') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10"></div>
                    <div class="-intro-x mt-5 text-lg text-white dark:text-gray-500"></div>
                </div>
            </div>
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto card-box">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-center">{{__('admin.admin_login')}} </h2>
                    <div class="intro-x mt-2 text-gray-500 xl:hidden text-center"></div>
                    <div class="intro-x mt-8">
                        <form id="login-form">
                            <input type="text" id="input-email" class="intro-x login__input input input--lg border border-gray-300 block" placeholder="{{__('admin.email_placeholder')}}">
                            <div id="error-email" class="login__input-error w-5/6 text-theme-6 mt-2"></div>
                            <input type="password" id="input-password" class="intro-x login__input input input--lg border border-gray-300 block mt-4" placeholder="{{__('admin.password_placeholder')}}">
                            <div id="error-password" class="login__input-error w-5/6 text-theme-6 mt-2"></div>
                        </form>
                    </div>
                    <!--<div class="intro-x flex text-gray-700 dark:text-gray-600 text-xs sm:text-sm mt-4">-->
                    <!--    <div class="flex items-center mr-auto">-->
                    <!--        <input type="checkbox" class="input border mr-2" id="input-remember-me">-->
                    <!--        <label class="cursor-pointer select-none" for="input-remember-me">{{__('admin.remember_me')}}</label>-->
                    <!--    </div>-->
                    <!--</div>-->
                    <div class="intro-x mt-5 xl:mt-8 text-center xl:text-center">
                        <button id="btn-login" class="button button--lg w-full  text-white bg-theme-1 xl:mr-3 align-top">{{__('admin.login')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>    
@endsection

@section('script')
    <script src="{{ asset('js/login.js') }}"></script>    
@endsection