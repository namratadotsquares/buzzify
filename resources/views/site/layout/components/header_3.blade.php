<div class="jl_topa_blank_nav jl_blank_06"></div>
<header class="header-wraper header_magazine_full_screen jl_headcus_06 header_magazine_full_screen jl_topa_menu_sticky options_dark_header jl_cus_sihead">
    <div id="menu_wrapper" class="menu_wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <!-- begin logo -->
                    <div class="logo_small_wrapper_table">
                        <div class="logo_small_wrapper">
                            <a class="logo_link" href="{{url('/')}}">
                                <img class="logo_black" src="{{asset('upload/logo')}}/{{setting('site_logo')}}"  alt="{{setting('site_name')}}" />
                            </a>
                        </div>
                    </div>
                    <!-- end logo -->
                    <!-- main menu -->
                    <div class="menu-primary-container navigation_wrapper header_layout_style1_custom">
                        <ul id="mainmenu" class="jl_main_menu">
                            <li class="menu-item menu-item-has-children"> <a href="{{url('/')}}">{{ __('frontend.home') }}<span class="border-menu"></span></a>
                                <ul class="sub-menu">
                                    <li class="menu-item"><a href="{{url('/')}}?home=home_1">{{ __('frontend.home_1') }}<span class="border-menu"></span></a></li>
                                    <li class="menu-item"><a href="{{url('/')}}?home=home_2">{{ __('frontend.home_2') }}<span class="border-menu"></span></a></li>
                                    <li class="menu-item"><a href="{{url('/')}}?home=home_3">{{ __('frontend.home_3') }}<span class="border-menu"></span></a></li>
                                    <li class="menu-item"><a href="{{url('/')}}?home=home_4">{{ __('frontend.home_4') }}<span class="border-menu"></span></a></li>
                                    <li class="menu-item"><a href="{{url('/')}}?home=home_5">{{ __('frontend.home_5') }}<span class="border-menu"></span></a></li>
                                </ul>
                            </li>
                            @foreach($category as $header_data)
                            <li class="menu-item">
                                <a href="{{url('category-blog')}}?category={{$header_data->slug}}">{{$header_data->name}}<span class="border-menu"></span></a>
                            </li>
                            @endforeach

                            @if(count($not_featured_category))
                            <li class="menu-item menu-item-has-children"> <a href="javascript:;">{{ __('frontend.more') }}<span class="border-menu"></span></a>
                                 <ul class="sub-menu">
                                    @foreach($not_featured_category as $header_data)
                                    <li class="menu-item"><a href="{{url('category-blog')}}?category={{$header_data->slug}}">{{$header_data->name}}<span class="border-menu"></span></a>
                                    </li>
                                    @endforeach
                                 </ul>
                            </li>
                            @endif

                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <!-- end main menu -->
                    <div class="search_header_menu">
                        <div class="menu_mobile_icons"><i class="fa fa-bars"></i></div>
                        <ul class="social_icon_header_top jl_socialcolor">
                            <?php $i=0;?>
                            @foreach($social as $social_icons)
                                <?php if($i<3){ 
                                $i++;?>
                                <li>
                                    <a class="{{$social_icons->name}}" href="{{$social_icons->url}}" target="_blank"><i class="fa <?php echo $social_icons->icon;?>"></i></a>
                                </li>
                                <?php } ?>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>