<div id="content_nav" class="jl_mobile_nav_wrapper">
    <div id="nav" class="jl_mobile_nav_inner">
        <div class="menu_mobile_icons mobile_close_icons closed_menu">
            <span class="jl_close_wapper"><span class="jl_close_1"></span><span class="jl_close_2"></span></span>
        </div>
		
		<div class="loginSignup">
            @if(Auth::user()=='')
			<ul>
				<li><a href="{{url('user-login')}}">{{ __('frontend.login') }}<span class="border-menu"></span></a></li>
				<li><a href="{{url('user-signup')}}">{{ __('frontend.signup') }}<span class="border-menu"></span></a></li>
			</ul>
            @endif
		</div>
        <ul id="mobile_menu_slide" class="menu_moble_slide">
            <li class="menu-item">
                <a href="{{url('/')}}">{{ __('frontend.home') }}<span class="border-menu"></span></a>
            </li>           
            <!--@foreach($category as $header_data)                    -->
            <!--    <li class="menu-item">-->
            <!--        <a href="{{url('category')}}/{{$header_data->slug}}">{{$header_data->name}}<span class="border-menu"></span></a>-->
            <!--    </li>-->
            <!--@endforeach           -->
            
            @foreach($category as $header_data)
            <li class="menu-item">
                <a href="{{url('category-blog')}}?category={{$header_data->slug}}">{{$header_data->name}}<span class="border-menu"></span></a>
            </li>
            @endforeach 
            
            @if(!Auth::user())
                <li class="menu-item">
                    <a href="{{url('feedback')}}">{{ __('frontend.my_feedback') }}<span class="border-menu"></span></a>
                </li>
            @endif
            
            @if(Auth::user() && Auth::user()->type == 'user')
                <li class="menu-item">
                    <a href="{{url('edit-profile')}}">{{ __('frontend.edit_profile') }}<span class="border-menu"></span></a>
                </li>
                <li class="menu-item">
                    <a href="{{url('saved-stories')}}">{{ __('frontend.saved_stories') }}<span class="border-menu"></span></a>
                </li>
                <li class="menu-item">
                    <a href="{{url('my-stories')}}">{{ __('frontend.my_stories') }}<span class="border-menu"></span></a>
                </li>
                <li class="menu-item">
                    <a href="{{url('manage-request')}}">{{ __('frontend.manage_request') }}<span class="border-menu"></span></a>
                </li>
                <li class="menu-item">
                    <a href="{{url('redeem-request')}}">{{ __('frontend.redeem_request') }}<span class="border-menu"></span></a>
                </li>                
                <li class="menu-item">
                    <a href="{{url('feedback')}}">{{ __('frontend.my_feedback') }}<span class="border-menu"></span></a>
                </li> 
                <li class="menu-item">
                    <a href="{{url('logout')}}">{{ __('frontend.logout') }}<span class="border-menu"></span></a>
                </li>
            @endif
        </ul>
        <span class="jl_none_space"></span>
        <div id="disto_about_us_widget-2" class="widget jellywp_about_us_widget">
            <div class="widget_jl_wrapper about_widget_content">
                <div class="jellywp_about_us_widget_wrapper">
                    <div class="social_icons_widget">
                      	<p>Follow us on</p>
                        <ul class="social-icons-list-widget icons_about_widget_display">
                            <?php $i=0;?>
                            @foreach($social as $social_icons)
                                <?php if($i<5){ 
                                $i++;?>
                                <li>
                                    <a class="{{$social_icons->name}}" href="{{$social_icons->url}}" target="_blank"><i class="fa <?php echo $social_icons->icon;?> side-menu-icons"></i></a>
                                </li>
                                <?php } ?>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <span class="jl_none_space"></span>
            </div>
        </div>
    </div>
</div>
<div class="mobile_menu_overlay"></div>