@extends('../site/layout/main') @section('content')
<link rel="stylesheet" href="{{ asset('site/css/main_new.css')}}?v='.date('YmdHis').'')}}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ asset('site/css/shop.css')}}?v='.date('YmdHis').'')}}" type="text/css" media="all" />
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform">
    <div class="options_layout_container full_layout_enable_front no_transform">
        <!-- Start header -->
        @include('../site/layout/components/header_1')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')
        <!-- begin content -->
        <div class="main_title_wrapper category_title_section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 main_title_col">
                        <div class="jl_cat_mid_title">
                            <h3 class="categories-title title">{{ __('frontend.forget_password') }}</h3>
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
                                <div class="woocommerce" id="forget">
                                    <div class="woocommerce-notices-wrapper"></div>
                                    <h2>{{ __('frontend.email') }}</h2>
                                    <form class="woocommerce-form woocommerce-form-login login" id="forget-password">
                                        <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>
                                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                            <label for="email">{{ __('frontend.email') }}&nbsp;<span class="required">*</span> </label>
                                            <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="email" autocomplete="email" placeholder="{{ __('frontend.email_placeholder') }}" value="" />
                                        </p>
                                        <p class="form-row m-t-10">
                                            <button type="button" class="woocommerce-button button woocommerce-form-login__submit" id="forget_password_btn" onclick="forget_password(event,'forget-password');">
                                                {{ __('frontend.forget_password') }}
                                            </button>
                                        </p>
                                    </form>
                                </div>
                                <div class="woocommerce hide" id="reset">
                                    <div class="woocommerce-notices-wrapper"></div>
                                    <h2>{{ __('frontend.email') }}</h2>
                                    <form class="woocommerce-form woocommerce-form-login login" id="reset-password">
                                        <input name="email" type="hidden" id="email_value" value="" />
                                        <input name="otp_check" type="hidden" id="otp_check" value="" />
                                        <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>
                                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                            <label for="otp">{{ __('frontend.otp') }}&nbsp;<span class="required">*</span> </label>
                                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="otp" id="otp" autocomplete="otp" placeholder="{{ __('frontend.otp_placeholder') }}" />
                                        </p>
                                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                            <label for="password">{{ __('frontend.password') }}&nbsp;<span class="required">*</span> </label>
                                            <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="password" autocomplete="password" placeholder="{{ __('frontend.password_placeholder') }}" />
                                        </p>
                                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                            <label for="c_password">{{ __('frontend.confirm_password') }}&nbsp;<span class="required">*</span> </label>
                                            <input
                                                type="password"
                                                class="woocommerce-Input woocommerce-Input--text input-text"
                                                name="c_password"
                                                id="c_password"
                                                autocomplete="c_password"
                                                placeholder="{{ __('frontend.confirm_password_placeholder') }}"
                                            />
                                        </p>

                                        <p class="form-row -t-10">
                                            <button type="button" class="woocommerce-button button woocommerce-form-login__submit" id="reset_password_btn" onclick="reset_password(event,'reset-password');">{{ __('frontend.reset') }}</button>
                                        </p>
                                    </form>
                                </div>
                            </div>
                            <div class="brack_space"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end content -->
        <!-- Start footer -->
        @include('../site/layout/components/footer')
        <!-- End footer -->
    </div>
</div>
<div id="go-top">
    <a href="#go-top"><i class="fa fa-angle-up"></i></a>
</div>
@endsection