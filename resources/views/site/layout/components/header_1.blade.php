<header class="header-wraper jl_header_magazine_style two_header_top_style header_layout_style3_custom jl_cusdate_head">
    <div class="header_top_bar_wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="menu-primary-container navigation_wrapper">
                        <ul id="jl_top_menu" class="jl_main_menu">
                            <li class="menu-item menu-item-home current-menu-item page_item page-item-4212 current_page_item menu-item-4461">
                                <a href="phone:{{setting('site_phone')}}" aria-current="page"><i class="fa fa-phone"></i> {{setting('site_phone')}} <span class="border-menu"></span> </a>
                            </li>
                        </ul>
                    </div>
                   
                    <div class="jl_top_bar_right"><span class="jl_current_title"> {{ __('frontend.current_date') }}</span> {{date('d M, Y')}}</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Start Main menu -->
    <div class="jl_blank_nav"></div>
    <div id="menu_wrapper" class="menu_wrapper jl_menu_sticky jl_stick">
        <div class="container">
            <div class="row">
                <div class="main_menu col-md-12">
                    <div class="logo_small_wrapper_table">
                        <div class="logo_small_wrapper">
                            <!-- begin logo -->
                            <a class="logo_link" href="{{url('/')}}">
                                <img src="{{asset('upload/logo')}}/{{setting('site_logo')}}"  alt="{{setting('site_name')}}"/>
                            </a>
                            <!-- end logo -->
                        </div>
                    </div>
                    <!-- main menu -->
                    <div class="menu-primary-container navigation_wrapper">
                        <ul id="mainmenu" class="jl_main_menu">
                            <li class="menu-item"> <a href="{{url('/')}}">{{ __('frontend.home') }}<span class="border-menu"></span></a>
                             
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
                    </div>
                    <!-- end main menu -->
                    <div class="search_header_menu">
                        <div class="menu_mobile_icons"><i class="fa fa-bars"></i></div>
                        <div class="menu_mobile_share_wrapper">
                            <ul class="social_icon_header_top">
                                <?php $i=0;?>
                                @foreach($social as $social_icons)
                                    <?php if($i<3){ 
                                    $i++;?>
                                    <li>
                                        <a class="{{$social_icons->name}} social-icon-link" href="{{$social_icons->url}}" target="_blank"><i class="fa <?php echo $social_icons->icon;?>"></i></a>
                                    </li>
                                    <?php } ?>
                                @endforeach
                                <li>
                                    <a class="social-icon-link dark-icon darkmode" href="javascript:;" onclick="changeLightDarkmode();"><i class="fa fa-moon-o mode_icon"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
