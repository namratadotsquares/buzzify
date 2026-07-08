@extends('../site/layout/main') @section('content')
<link rel="stylesheet" href="{{ asset('site/css/main_new.css')}}?v='.date('YmdHis').'')}}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ asset('site/css/shop.css')}}?v='.date('YmdHis').'')}}" type="text/css" media="all" /> 
   <div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss">
      <div class="options_layout_container full_layout_enable_front">
         <!-- Start header -->
         @include('../site/layout/components/header_1')
         <!-- end header -->
         @include('../site/layout/components/overlay-menu')
         <div class="main_title_wrapper category_title_section">
            <div class="container">
               <div class="row">
                  <div class="col-md-12 main_title_col">
                     <div class="jl_cat_mid_title">
                        <h3 class="categories-title title">{{ __('frontend.login_text') }}</h3>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <section id="content_main" class="clearfix">
            <div class="container">
               <div class="row main_content">
                  <!-- begin content -->
                  <div class="page-full col-md-12 post-3938 page type-page status-publish hentry" id="content">
                     <div class="content_single_page post-3938 page type-page status-publish hentry">
                        <div class="content_page_padding">
                           <div class="woocommerce">
                              <div class="woocommerce-notices-wrapper"></div>
                              <h2>{{ __('frontend.login') }}</h2>
                              <form class="woocommerce-form woocommerce-form-login login" id="user-login">
                                 <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>
                                 @csrf
                                 <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                    <label for="email">{{ __('frontend.username_or_email') }}&nbsp;<span class="required">*</span>
                                    </label>
                                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="email" autocomplete="email" value="" placeholder="{{ __('frontend.username_or_email_placeholder') }}" />
                                 </p>
                                 <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                    <label for="password">{{ __('frontend.password') }}&nbsp;<span class="required">*</span>
                                    </label>
                                    <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" placeholder="{{ __('frontend.password_placeholder') }}" />
                                 </p>
                                 <p class="form-row">
                                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                                       <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="remember_me" type="checkbox" id="remember_me"/> <span>{{ __('frontend.remember_me') }}</span>
                                    </label>
                                    <button type="button" class="woocommerce-button button woocommerce-form-login__submit" id="login_button" onclick="user_login(event,'user-login');">{{ __('frontend.login') }}</button>
                                 </p>
                                 <p class="woocommerce-LostPassword lost_password"> 
                                    <a href="{{url('forget-password')}}">{{ __('frontend.forget_password') }}</a>
                                    <a href="{{url('user-signup')}}" class="pull-right">{{ __('frontend.signup') }}</a>
                                 </p>								 
                                 <a class="btn-fb" href="javascript:;" onclick="alert('Facebook Login is not available for demo purpose due to Facebook terms and conditions. Please use the Sign In option to login')">
                                    <div class="fb-content">
                                    <div class="logo">
                                       <i class="fa fa-facebook"></i>
                                    </div>
                                    </div>
                                 </a>
                              </form>
                           </div>
                        </div>
                        <div class="brack_space"></div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
         <!-- Start footer -->
         @include('../site/layout/components/footer')
         <!-- End footer -->
      </div>
   </div>
   <div id="go-top"><a href="#go-top"><i class="fa fa-angle-up"></i></a>
   </div>
   @endsection