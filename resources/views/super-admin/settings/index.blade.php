@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.settings_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.settings_list')}}</h2>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 xxl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box bg-theme-1 p-5 mt-6">
                <div class="border-theme-3 dark:border-dark-5 mt-5 text-white">
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/site-setting" class="flex items-center px-3 py-2 rounded-md <?php if(Request::segment(4) == 'site-setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i> {{__('admin.site_settings')}} </a>

                 {{--   <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/global" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'global') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="phone"></i> {{__('admin.mobile_app_settings')}} </a> --}}

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/local" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'local') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="box"></i> {{__('admin.localization_settings')}} </a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/notification" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'notification') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="send"></i> {{__('admin.push_notification_settings')}} </a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/social" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'social') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="activity"></i>{{__('admin.social_media_settings')}} </a>

                    {{-- <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/font-setting" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'font-setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="type"></i>{{__('admin.font_setting')}} </a> --}}

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/live-News&E-News" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'live-News&E-News') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.live_enews_settings')}}</a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/admob" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'admob') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.admob')}}</a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/fb_ad_settings" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'fb_ad_settings') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.fb_ad_settings')}}</a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/wallet_setting" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'wallet_setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.wallet_setting')}}</a>

                     {{-- <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/location_setting" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'location_setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.location_setting')}}</a> --}}
                    
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/news_deletion" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'news_deletion') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="trash"></i>News Deletion Days</a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/app_settings" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'app_settings') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="trash"></i>App Settings</a>

                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 xxl:col-span-9">
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                            
                    
                    <h2 class="font-medium text-base mr-auto">
                        @if(isset($title)){{$title}}@endif
                    </h2>
                </div>
                <div class="p-5">
                    @if($page == 'permission')
                        <form method="post" action="{{url('/admin/settingPermission')}}">
                            {{ csrf_field() }}
                            <div class="mt-3">
                                <label>{{__('admin.roles')}}</label>
                                <select name="role_id" class="input w-full border mt-2">
                                    <option value="">{{__('admin.roles_placeholder')}}</option>
                                    @foreach($roles as $role)
                                        <option value="{{$role->id}}">{{ucfirst($role->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <label>{{__('admin.permission')}}</label>
                                <select name="permission_id[]" class="input w-full border mt-2" multiple>
                                    <option value="">{{__('admin.permission_placeholder')}}</option>
                                    @foreach($permissions as $permission)
                                        <option value="{{$permission->id}}">{{$permission->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-right mt-5">
                                <button type="submit" class="button w-24 bg-theme-1 text-white" id="createBtn">{{__('admin.save')}}</button>
                            </div>
                        </form>
                    @else
                        <form method="post" action="{{url('/updateSetting')}}">
                            <input type="hidden" name='page_name' value="{{$page}}">
                            {{ csrf_field() }}
                            @foreach($data as $row)

                                @if($page == 'global')
                                @elseif($page == 'local')

                                    @if($row->key == 'date_format')
                                        <div>
                                            <label>{{__('admin.date_formate')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="date_format" value="{{$row->value}}" placeholder="{{__('admin.date_formate_placeholder')}}">
                                        </div>
                                    @endif
                                
                                    @if($row->key == 'timezone')
                                        <div class="mt-3">
                                            <label>{{__('admin.timezone')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.select_timezone')}}" name="timezone" class="tail-select w-full">
                                                    @for($c= 0; $c< count($zones);$c++)
                                                        <option @if($row->value == $zones[$c]) selected  @endif value="{{$zones[$c]}}" >{{$zones[$c]}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                @elseif($page == 'font-setting')

                                    @if($row->key == 'h_1_size')
                                        <div>
                                            <label>{{__('admin.h_1_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_1_size" value="{{$row->value}}" placeholder="{{__('admin.h_1_size_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'h_2_size')
                                        <div>
                                            <label>{{__('admin.h_2_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_2_size" value="{{$row->value}}" placeholder="{{__('admin.h_2_size_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'h_3_size')
                                        <div>
                                            <label>{{__('admin.h_3_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_3_size" value="{{$row->value}}" placeholder="{{__('admin.h_3_size_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'h_4_size')
                                        <div>
                                            <label>{{__('admin.h_4_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="h_4_size" value="{{$row->value}}" placeholder="{{__('admin.h_4_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'p_size')
                                        <div>
                                            <label>{{__('admin.p_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="p_size" value="{{$row->value}}" placeholder="{{__('admin.p_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'span_size')
                                        <div>
                                            <label>{{__('admin.span_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="span_size" value="{{$row->value}}" placeholder="{{__('admin.span_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'small_size')
                                        <div>
                                            <label>{{__('admin.small_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="small_size" value="{{$row->value}}" placeholder="{{__('admin.small_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'lable_size')
                                        <div>
                                            <label>{{__('admin.lable_size')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="lable_size" value="{{$row->value}}" placeholder="{{__('admin.lable_size_placeholder')}}">
                                        </div>
                                    @endif


                                    @if($row->key == 'font_family')
                                        <div class="mt-3">
                                            <label>{{__('admin.font_family')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.select_font_family')}}" name="font_family" class="tail-select w-full">
                                                    @for($c= 0; $c< count($font_family);$c++)
                                                        <option @if($row->value == $font_family[$c]) selected  @endif value="{{$font_family[$c]}}" >{{$font_family[$c]}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    @endif


                                @elseif($page == 'site-setting')
                                    @if($row->key == 'homepage_theme')
                                        {{-- <div class="mt-3 mb-10">
                                            <label>{{__('admin.home_page_theme')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="Select Home Page Theme" name="homepage_theme" class="tail-select w-full">
                                                    <option @if($row->value == 'home_1') selected  @endif value="home_1" >{{__('admin.theme_1')}}</option>
                                                    <option @if($row->value == 'home_2') selected  @endif value="home_2" >{{__('admin.theme_2')}}</option>
                                                    <option @if($row->value == 'home_3') selected  @endif value="home_3" >{{__('admin.theme_3')}}</option>
                                                    <option @if($row->value == 'home_4') selected  @endif value="home_4" >{{__('admin.theme_4')}}</option>
                                                    <option @if($row->value == 'home_5') selected  @endif value="home_5" >{{__('admin.theme_5')}}</option>
                                                </select>
                                            </div>
                                        </div>--}}
                                    @endif

                                    @if($row->key == 'layout')
                                      {{--   <div class="mt-3 mb-10">
                                            <label>{{__('admin.blog_datail_theme')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.blog_datail_theme_placeholder')}}" name="layout" class="tail-select w-full">
                                                    <option @if($row->value == 'layout_1') selected  @endif value="layout_1" >{{__('admin.theme_1')}}</option>
                                                    <option @if($row->value == 'layout_2') selected  @endif value="layout_2" >{{__('admin.theme_2')}}</option>
                                                </select>
                                            </div>
                                        </div>--}}
                                    @endif

                                    @if($row->key == 'site_name')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.Website_name')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="site_name" value="{{$row->value}}" placeholder="{{__('admin.Website_name_placeholder')}}<">
                                        </div>
                                    @endif

                                    @if($row->key == 'from_email')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.email_from')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="from_email" value="{{$row->value}}" placeholder="{{__('admin.email_from_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'news_api_key')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.news_api_key')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="news_api_key" value="{{$row->value}}" placeholder="{{__('admin.news_api_key_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'site_logo')
                                        <?php 
                                            if(file_exists(public_path()."/upload/logo/".$row->value) && $row->value!='') { 
                                                $url = url('upload/logo').'/'.$row->value;
                                            }else{
                                                $url = url('upload/no-image.png');
                                            }
                                        ?>
                                        <input type="hidden" name="site_logo" id="site_logo" value="">
                                        <div class="mt-3">
                                            <label>{{__('admin.website_logo')}}</label>
                                            <div class="col-span-12 sm:col-span-12">
                                                <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_website_logo')}}" onclick="triggerFileInput('bguploadBtn')">
                                                <input class="bguploadBtn hide" type="file" onchange="uploadWebsiteLogo(this,'website_logo','add',0);" accept="image/jpg, image/jpeg, image/png" />
                                            </div>
                                            <div class="col-span-12 sm:col-span-12 mt-3">
                                                <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="website_logo" src="{{$url}}" class="width-20"  >
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_favicon')
                                        <?php 
                                            if(file_exists(public_path()."/upload/favicon/".$row->value) && $row->value!='') { 
                                                $url = url('upload/favicon').'/'.$row->value;
                                            }else{
                                                $url = url('upload/no-image.png');
                                            }
                                        ?>
                                        <input type="hidden" name="site_favicon" id="site_favicon" value="">
                                        <div class="mt-3">
                                            <label>{{__('admin.website_favicon')}}</label>
                                            <small style="color:red;">({{__('admin.website_favicon_note')}})</small>
                                            <div class="col-span-12 sm:col-span-12">
                                                <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_website_favicon')}}" onclick="triggerFileInput('faviconuploadBtn')">
                                                <input class="faviconuploadBtn hide" type="file" onchange="uploadWebsiteFavicon(this,'website_favicon','add',0);" accept=".ico"/>
                                            </div>
                                            <div class="col-span-12 sm:col-span-12 mt-3">
                                                <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="website_favicon" src="{{$url}}" style="width: 5%;"  >
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_phone')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.top_phone_number')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="site_phone" value="{{$row->value}}" placeholder="{{__('admin.top_phone_number_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'footer_about')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.footer_about_us_info')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="footer_about" value="{{$row->value}}" placeholder="{{__('admin.footer_about_us_info_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'powered_by')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.powered_by')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="powered_by" value="{{$row->value}}" placeholder="{{__('admin.powered_by_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_title')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_title')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="site_seo_title" value="{{$row->value}}" placeholder="{{__('admin.seo_title_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_description')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_description')}}</label>
                                            <textarea type="text" class="input w-full border mt-2" name="site_seo_description" placeholder="{{__('admin.seo_description_placeholder')}}">{{$row->value}}</textarea>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_keyword')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_keyword')}}</label>
                                            <textarea type="text" class="input w-full border mt-2" name="site_seo_keyword" placeholder="{{__('admin.seo_keyword_placeholder')}}">{{$row->value}}</textarea>
                                        </div>
                                    @endif

                                    @if($row->key == 'site_seo_tag')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.seo_tags')}}</label>
                                            <textarea type="text" class="input w-full border mt-2" name="site_seo_tag" placeholder="{{__('admin.seo_tags_placeholder')}}">{{$row->value}}</textarea>
                                        </div>
                                    @endif

                                    @if($row->key == 'preferred_site_language')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.preferred_site_language')}}</label>
                                            <div class="mt-2">
                                                <select data-placeholder="{{__('admin.preferred_site_language_placeholder')}}" name="preferred_site_language" class="tail-select w-full">
                                                    @foreach($languages as $language)
                                                        <option @if($row->value == $language->language) selected  @endif  value="{{$language->language}}">{{$language->name}}</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'google_analytics_code')
                                        <div class="mt-3 mb-10">
                                            <label>Google analytic code</label>
                                            <textarea type="text" class="input w-full border mt-2" name="google_analytics_code" placeholder="Google analytic code">{{$row->value}}</textarea>
                                        </div>
                                    @endif
                                    
                                    

                                    <!-- @if($row->key == 'ads_frequency')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.top_ads_frequency')}}</label>
                                            <input type="number" class="input w-full border mt-2" name="ads_frequency" value="{{$row->value}}" placeholder="{{__('admin.top_ads_frequency_placeholder')}}">
                                        </div>
                                    @endif -->

                                @elseif($page == 'notification')

                                    @if($row->key == 'enable_notifications')
                                        <div class="mt-3">
                                            <label for="vertical-checkbox-chris-evans">{{__('admin.enable_push_notification')}}</label>
                                            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                                                <input type="checkbox" class="input border mr-2" name="enable_notifications"  @if($row->value == 1) value="{{$row->value}}" checked @endif  id="vertical-checkbox-chris-evans">
                                                <label class="cursor-pointer select-none" for="vertical-checkbox-chris-evans">{{__('admin.enable_push_notification_placeholder')}}</label>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'firebase_msg_key')
                                        <div class="mt-3 mb-10">
                                            <label>{{__('admin.firebase_msg_key')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="firebase_msg_key" value="{{$row->value}}" placeholder="{{__('admin.firebase_msg_key_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'firebase_api_key')
                                        <div class="mt-3">
                                            <label>{{__('admin.firebase_api_key')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="firebase_api_key" value="{{$row->value}}" placeholder="{{__('admin.firebase_api_key_placeholder')}}">
                                        </div>
                                    @endif
								@elseif($page == 'admob')

                                    @if($row->key == 'enable_ads')
                                        <div class="mt-3">
                                            <!-- <label for="vertical-checkbox-chris-evans">{{__('admin.enable_ads')}}</label> -->
                                            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                                                <input type="checkbox" class="input border mr-2" name="enable_ads"  @if($row->value == 1) value="{{$row->value}}" checked @endif  id="vertical-checkbox-chris-evans">
                                                <label class="cursor-pointer select-none" for="vertical-checkbox-chris-evans">{{__('admin.enable_ads_placeholder')}}</label>
                                            </div>
                                        </div>
                                    @endif
                                    @if($row->key == 'admob_banner_id_android')
                                        <div class="mt-3">
                                            <label>{{__('admin.admob_banner_id_android')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="admob_banner_id_android" value="{{$row->value}}" placeholder="{{__('admin.admob_banner_id_android_placeholder')}}">
                                        </div>
                                    @endif

                                    @if($row->key == 'admob_interstitial_id_android')
                                        <div class="mt-3">
                                            <label>{{__('admin.admob_interstitial_id_android')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="admob_interstitial_id_android" value="{{$row->value}}" placeholder="{{__('admin.admob_interstitial_id_android_placeholder')}}">
                                        </div>
                                    @endif
              						@if($row->key == 'admob_banner_id_ios')
                                        <div class="mt-3">
                                            <label>{{__('admin.admob_banner_id_ios')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="admob_banner_id_ios" value="{{$row->value}}" placeholder="{{__('admin.admob_banner_id_ios_placeholder')}}">
                                        </div>
                                    @endif
              						@if($row->key == 'admob_interstitial_id_ios')
                                        <div class="mt-3">
                                            <label>{{__('admin.admob_interstitial_id_ios_placeholder')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="admob_interstitial_id_ios" value="{{$row->value}}" placeholder="{{__('admin.admob_interstitial_id_ios_placeholder')}}">
                                        </div>
                                    @endif
                                @elseif($page == 'fb_ad_settings')
                                    @if($row->key == 'enable_fb_ads')
                                        <div class="mt-3">
                                            <!-- <label for="vertical-checkbox-chris-evans">{{__('admin.enable_fb_ads')}}</label> -->
                                            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                                                <input type="checkbox" class="input border mr-2" name="enable_fb_ads" @if($row->value == 1) value="{{$row->value}}"  checked @endif  id="vertical-checkbox-chris-evans">
                                                <label class="cursor-pointer select-none" for="vertical-checkbox-chris-evans">{{__('admin.enable_fb_ads_placeholder')}}</label>
                                            </div>
                                        </div>
                                    @endif
                                    @if($row->key == 'fb_ads_placement_id_android')
                                        <div class="mt-3">
                                            <label>{{__('admin.fb_ads_placement_id_android')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="fb_ads_placement_id_android" value="{{$row->value}}" placeholder="{{__('admin.fb_ads_placement_id_android_placeholder')}}">
                                        </div>
                                    @endif
              						@if($row->key == 'fb_ads_placement_id_ios')
                                        <div class="mt-3">
                                            <label>{{__('admin.fb_ads_placement_id_ios')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="fb_ads_placement_id_ios" value="{{$row->value}}" placeholder="{{__('admin.fb_ads_placement_id_ios_placeholder')}}">
                                        </div>
                                    @endif
              						@if($row->key == 'fb_ads_app_token')
                                        <div class="mt-3">
                                            <label>{{__('admin.fb_ads_app_token')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="fb_ads_app_token" value="{{$row->value}}" placeholder="{{__('admin.fb_ads_app_token_placeholder')}}">
                                        </div>
                                    @endif
                                    @elseif($page == 'wallet_setting')
                                        
                                    @if($row->key == 'wallet_value')
                                        <div class="mt-3">
                                            <label>{{__('admin.wallet_value')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="wallet_value" value="{{$row->value}}" placeholder="{{__('admin.wallet_value')}}">
                                        </div>
                                    @endif
                                    @if($row->key == 'wallet_stories_count')
                                        <div class="mt-3">
                                            <label>{{__('admin.wallet_stories_count')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="wallet_stories_count" value="{{$row->value}}" placeholder="{{__('admin.wallet_stories_count')}}">
                                        </div>
                                    @endif
                                     
                                    @elseif($page == 'location_setting')
                                        
                                    {{-- @if($row->key == 'location_radius')
                                        <div class="mt-3">
                                            <label>{{__('admin.location_radius')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="location_radius" value="{{$row->value}}" placeholder="{{__('admin.location_radius')}}">
                                        </div>
                                    @endif --}}

                                     @elseif($page == 'app_settings')
                                        
                                    @if($row->key == 'time_for_news_view')
                                        <div class="mt-3">
                                            <label>{{__('admin.time_for_news_view')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="time_for_news_view" value="{{$row->value}}" placeholder="{{__('admin.time_for_news_view')}}">
                                        </div>
                                    @endif
                                    @if($row->key == 'after_news_ads')
                                        <div class="mt-3">
                                            <label>{{__('admin.after_news_ads')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="after_news_ads" value="{{$row->value}}" placeholder="{{__('admin.after_news_ads')}}">
                                        </div>
                                    @endif
                                      @if($row->key == 'location_radius')
                                        <div class="mt-3">
                                            <label>{{__('admin.location_radius')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="location_radius" value="{{$row->value}}" placeholder="{{__('admin.location_radius')}}">
                                        </div>
                                    @endif
                                      @if($row->key == 'news_max_words')
                                        <div class="mt-3">
                                            <label>{{__('admin.news_max_words')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="news_max_words" value="{{$row->value}}" placeholder="{{__('admin.news_max_words')}}">
                                        </div>
                                    @endif
                                    @if($row->key == 'feature_category_auto_remove')
                                        <div class="mt-3">
                                            <label>{{__('admin.feature_category_auto_remove')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="feature_category_auto_remove" value="{{$row->value}}" placeholder="{{__('admin.feature_category_auto_remove')}}">
                                        </div>
                                    @endif
                                    @if($row->key == 'personal_category_auto_remove')
                                        <div class="mt-3">
                                            <label>{{__('admin.personal_category_auto_remove')}}</label>
                                            <input type="text" class="input w-full border mt-2" name="personal_category_auto_remove" value="{{$row->value}}" placeholder="{{__('admin.personal_category_auto_remove')}}">
                                        </div>
                                    @endif
                                    @elseif($page == 'news_deletion')
                                        
                                    @if($row->key == 'news_deletion')
                                        <div class="mt-3">
                                            <label>News Deletion Days</label>
                                            <input type="text" class="input w-full border mt-2" name="news_deletion" value="{{$row->value}}" placeholder="{{__('admin.news_deletion')}}">
                                        </div>
                                    @endif
                                    <!-- @if($row->key == 'wallet_expiry')-->
                                    <!--    <div class="mt-6">-->
                                    <!--        <div class="row">-->
                                    <!--            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-1">-->
                                    <!--                <input type="radio" class="input border mr-2" name="wallet_expiry" value="1"-->
                                    <!--                @if($row->value == 1)   checked @endif  id="vertical-checkbox-chris-evans-expiry">-->
                                    <!--                <label class="cursor-pointer select-none" for="vertical-checkbox-chris-evans-expiry">{{__('admin.Expiry')}}</label>-->
                                    <!--            </div>-->
                                    <!--            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-1">-->
                                    <!--                <input type="radio" class="input border mr-2" name="wallet_expiry" value="0" @if($row->value == 0)  checked @endif  id="vertical-checkbox-chris-evans-unlimited">-->
                                    <!--                <label class="cursor-pointer select-none" for="vertical-checkbox-chris-evans-unlimited">{{__('admin.unlimited')}}</label>-->
                                    <!--            </div>-->
                                    <!--        </div>-->
                                    <!--    </div>-->
                                    <!--@endif-->
                                    
                                    <!--@if($row->key == 'wallet_expiry_range')-->
                                    <!--    <div class="mt-3" id="wallet-range-div">-->
                                    <!--        <label>{{__('admin.wallet_range')}}</label>-->
                                    <!--        <input type="number" class="input w-full border mt-2" name="wallet_expiry_range" value="{{$row->value}}" placeholder="{{__('admin.wallet_range')}}">-->
                                    <!--    </div>-->
                                    <!--@endif-->

              					
                                @elseif($page == 'live-News&E-News')

                                   @if($row->key == 'live_news_logo')
                                        <?php 
                                            if(file_exists(public_path()."/upload/live-news-logo/".$row->value) && $row->value!='') { 
                                                $url = url('upload/live-news-logo').'/'.$row->value;
                                            }else{
                                                $url = url('upload/no-image.png');
                                            }
                                        ?>
                                        <input type="hidden" name="live_news_logo" id="live_news_logo" value="">
                                        <div class="mt-3">
                                            <label>Live News Logo (Resolution 512 x 512 px)</label>
                                            <div class="col-span-12 sm:col-span-12 mt-3">
                                                <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="liveNewsImage" src="{{$url}}" class="width-20"  >
                                            </div>
                                            <div class="col-span-12 sm:col-span-12">
                                                <input type="button" class="button w-30 bg-theme-1 text-white" value="Upload Live News Logo" onclick="triggerFileInput('bguploadBtns')">
                                                <input class="bguploadBtns hide" type="file" onchange="uploadLiveNewsLogo(this,'liveNewsImage','add',0);" accept="image/jpg, image/jpeg"/>
                                            </div>
                                            
                                        </div>
                                    @endif

                                    @if($row->key == 'live_news_status')
                                        <div class="mt-8 mb-8">
                                            <label for="vertical-checkbox-chris-evans">Live News Status</label>
                                            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                                                <input type="checkbox" class="input border mr-2" name="live_news_status"  @if($row->value == 1) value="{{$row->value}}" checked @endif  id="vertical-checkbox-chris-evans">
                                                <label class="cursor-pointer select-none" for="vertical-checkbox-chris-evans">Enable Dispaly icon</label>
                                            </div>
                                        </div>
                                    @endif

                                    @if($row->key == 'e_paper_logo')
                                        <?php 
                                            if(file_exists(public_path()."/upload/e-paper/".$row->value) && $row->value!='') { 
                                                $url = url('upload/e-paper').'/'.$row->value;
                                            }else{
                                                $url = url('upload/no-image.png');
                                            }
                                        ?>
                                        <input type="hidden" name="e_paper_logo" id="e_paper_logo" value="">
                                        <div class="mt-3">
                                            <label>E-News Logo (Resolution 512 x 512 px)</label>
                                            <div class="col-span-12 sm:col-span-12 mt-3">
                                                <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="ePaperImage" src="{{$url}}" class="width-20"  >
                                            </div>
                                            <div class="col-span-12 sm:col-span-12">
                                                <input type="button" class="button w-30 bg-theme-1 text-white" value="Upload E-News Logo" onclick="triggerFileInput('bguploadBtn')">
                                                <input class="bguploadBtn hide" type="file" onchange="uploadEPaperLogo(this,'ePaperImage','add',0);" accept="image/jpg, image/jpeg"/>
                                            </div>
                                            
                                        </div>
                                    @endif

                                    @if($row->key == 'e_paper_status')
                                        <div class="mt-8 mb-8">
                                            <label for="vertical-checkbox-chris"> E-News Status</label>
                                            <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                                                <input type="checkbox" class="input border mr-2" name="e_paper_status"  @if($row->value == 1) value="{{$row->value}}" checked @endif  id="vertical-checkbox-chris">
                                                <label class="cursor-pointer select-none" for="vertical-checkbox-chris">Enable Dispaly icon</label>
                                            </div>
                                        </div>
                                    @endif

                                @endif

                            @endforeach
                            @can('setting-update')
                            <div class="text-right mt-5">                                        
                                <button type="submit" class="button w-24 bg-theme-1 text-white" id="createBtn">{{__('admin.save')}}</button>
                            </div>
                            @endcan
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $('input[name="wallet_expiry"]').change(function () {
            if (this.value == 1) {
                $('#wallet-range-div').show();
            } else {
                $('#wallet-range-div').hide();
            }
        });

        // Trigger the change event on page load to initialize the visibility
        $('input[name="wallet_expiry"]:checked').trigger('change');
    });
    
    $('input[name="wallet_expiry"]').change(function () {
        console.log(this.value);
        if (this.value == 1) {
            console.log("Expiry selected");
            $('#wallet-range-div').show();
        } else {
            console.log("Unlimited selected");
            $('#wallet-range-div').hide();
        }
    });
    
</script>

@endsection