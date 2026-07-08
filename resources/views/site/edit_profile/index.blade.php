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
       
        
        <div class="main_title_wrapper category_title_section jl_cat_img_bg">
             <div class="category_image_bg_image" style="background-image: url('{{ asset('upload/news-baner.jpg') }}');"></div>
             <!--
             <div class="category_image_bg_image" style="background-image: url('{{ asset('upload/newsban.jpg') }}');"></div>
             <div class="category_image_bg_image" style="height:500px;background-image: url('http://news.jaipurtesthouse.com/upload/news-banner.jpg');"></div>-->
            <div class="category_image_bg_ov"></div>
            <div class="jl_cat_title_wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 main_title_col">
                            <div class="jl_cat_mid_title">
                                <h3 class="categories-title title">{{ __('frontend.edit_profile') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <section id="content_main" class="clearfix">
            <div class="container">
                <div class="row main_content">
                    <!-- begin content -->
                    <div class="wrapper bg-white mt-sm-5">
                        <div class="d-flex align-items-start py-3 border-bottom profileimg mt-10">
                            <img src="{{$user->photo}}" class="img" id="show_image" alt="{{$user->name}}" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';" />
                            <div class="pl-sm-4 pl-2" id="img-section">
                                <b>{{ __('frontend.profile_update') }}</b>
                                <p>{{ __('frontend.profile_image_accept') }}</p>
                                <button class="btn button border" id="camera_icon" onclick="onFileChanged();"><b>{{ __('frontend.upload') }}</b></button>
                                <input class="file-uploads hide" type="file" name="image" />
                            </div>
                        </div>
                        <b><p style="background-color: #f0f0f0; margin-top:10px; padding: 10px; border-radius: 5px; font-family: 'Arial', sans-serif;  color: #0015ff; text-align: center;">
                            Available Wallet Amount: ₹ {{ $point }}
                        </p></b> 
                        <div class="py-2">
                            <form id="edit_profile">
                                <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>
                                @csrf
                                <input type="hidden" name="id" id="id" value="{{$user->id}}" />
                                <div class="row py-2">
                                    <div class="col-md-6">
                                        <label for="firstname">{{ __('frontend.name') }}</label>
                                        <input type="text" class="bg-light form-control" placeholder="{{ __('frontend.name') }}" name="name" value="{{$user->name}}" placeholder="{{ __('frontend.name_plceholder') }}" />
                                    </div>
                                    <div class="col-md-6 pt-md-0 pt-3">
                                        <label for="lastname">{{ __('frontend.email') }}</label>
                                        <input type="email" class="bg-light form-control" placeholder="{{ __('frontend.email') }}" name="email" value="{{$user->email}}" placeholder="{{ __('frontend.email_placeholder') }}" />
                                    </div>
                                </div>
                                <div class="row py-2">
                                    <div class="col-md-6 pt-md-0 pt-3">
                                        <label for="phone">{{ __('frontend.phone') }}</label>
                                        <input type="tel" class="bg-light form-control" placeholder="{{ __('frontend.phone_plceholder') }}" name="phone" value="{{$user->phone}}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label for="firstname">{{ __('frontend.password') }}</label>
                                        <input type="password" class="bg-light form-control" placeholder="{{ __('frontend.password_placeholder') }}" name="password" />
                                    </div>
                                </div>

                                <div class="py-3 pb-4 border-bottom text-center">
                                    <button class="btn btn-primary-save mr-3" type="button" onclick="edit_profile(event,'edit_profile');">{{ __('frontend.save') }}</button>
                                </div>
                                <div class="d-sm-flex align-items-center pt-3 text-center" id="deactivate">
                                    <div>
                                        <b>{{ __('frontend.delete_account') }}</b>
                                        <p>{{ __('frontend.detail_of_account') }}</p>
                                    </div>
                                    <div class="ml-auto">
                                        <button type="button" class="btn danger w-200 deletebtnModal" onclick="deletebtnModal('id01',true);">{{ __('frontend.delete') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <div id="id01" class="modal">
            <span onclick="deletebtnModal('id01',false);" class="close">×</span>
            <form class="modal-content" action="/action_page.php">
                <div class="container">
                    <h1>{{ __('frontend.are_you_sure') }}</h1>
                    <p>{{ __('frontend.delete_account_confirmation') }}</p>

                    <div class="clearfix">
                        <button type="button" onclick="deletebtnModal('id01',false);" class="cancelbtn">{{ __('frontend.cancel') }}</button>
                        <a href="{{url('delete-account')}}" class="deletebtn deletebutton">{{ __('frontend.delete') }}</a>
                    </div>
                </div>
            </form>
        </div>
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