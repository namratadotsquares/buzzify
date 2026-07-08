@extends('../layout/main')

@section('head')
    @yield('subhead')
@endsection

@section('content')
    @include('../layout/components/mobile-menu')
    <div class="flex">
        <nav class="side-nav">
            <a href="{{url('/dashboard/side-menu/light')}}" class="intro-x flex items-center">
                <?php
                    if(file_exists(public_path()."/upload/logo/".setting('site_logo'))) {
                        $url = url('upload/logo').'/'.setting('site_logo');
                        // $url = url('upload/logo/buzzify-logo-white.png');
                    }else{
                        $url = url('upload/no-image.png');
                    }
                    $url = url('upload/logo/buzzify-logo-white.png');
                ?>
                <img class="max-width-70" src="{{$url}}">
                <span class="hidden xl:block text-white text-lg ml-3">
                    <!-- Blog -->
                </span>
            </a>
            <div class="side-nav__devider my-6"></div>
            <ul>
                @can('dashboard')
                <li>
                    <a href="{{url('/dashboard/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'dashboard') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon">
                            <i data-feather="home"></i>
                        </div>
                        <div class="side-menu__title">
                            {{__('admin.dashboard')}}
                        </div>
                    </a>
                </li>
                @endcan


                <!--@can('feed-item-list')-->
                <!--<li>-->
                <!--    <a href="{{url('/letest-story')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'letest-story') { echo 'side-menu--active'; } ?>">-->
                <!--        <div class="side-menu__icon"> <i data-feather="file-text"></i> </div>-->
                <!--        <div class="side-menu__title">-->
                <!--            {{__('admin.letest_story')}}-->
                <!--        </div>-->
                <!--    </a>-->
                <!--</li>-->
                <!--@endcan-->
                
                @can('feed-item-list')
                <!--<li>-->
                <!--    <a href="{{url('/letest-request-product')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'letest-request-product') { echo 'side-menu--active'; } ?>">-->
                <!--        <div class="side-menu__icon"> <i data-feather="file-text"></i> </div>-->
                <!--        <div class="side-menu__title">-->
                <!--            {{__('admin.letest_req')}}-->
                <!--        </div>-->
                <!--    </a>-->
                <!--</li>-->
                @endcan

                @role('admin')

                <li>
                    <a href="{{url('/user-feedback')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'user-feedback') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="message-square"></i> </div>
                        <div class="side-menu__title">
                            {{__('admin.feedback')}}
                        </div>
                    </a>
                </li>
              @endrole
                
                @can('feed-item-list')
                {{-- <li>
                    <a href="{{url('/feed-item')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'feed-item') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="file-text"></i> </div>
                        <div class="side-menu__title">
                            {{__('admin.feed_items')}}
                        </div>
                    </a>
                </li> --}}
                @endcan

                
                
              

                @can('news-api-post-list')
                <li>
                    <a href="{{url('/news-api-post')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'news-api-post') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="book-open" class="mx-auto"></i> </div>
                        <div class="side-menu__title">
                            {{__('admin.news_api')}}
                        </div>
                    </a>
                </li>
                @endcan
              @role('admin')
                <li>
                    <a href="javascript:;" class="side-menu <?php if(Request::segment(1) == 'wallet-product') { echo 'side-menu--active side-menu--open'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="credit-card"></i> </div>
                        <div class="side-menu__title"> {{__('admin.manage_wallet')}} <i data-feather="chevron-down" class="menu__sub-icon"></i> </div>
                    </a>
                    <ul <?php if(Request::segment(1) == 'wallet-product') { ?>class="side-menu__sub-open" style="display: block;"<?php }else{ ?> style="display: none;"<?php } ?>>
                       
                        {{-- @can('wallet-product-list') --}}
                        <li>
                            <a href="{{url('/wallet-product/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'wallet-product') { echo 'side-menu--active'; } ?>">
                            
                                <div class="side-menu__title">
                                    {{__('admin.wallet_product')}}
                                </div>
                            </a>
                        </li>
                        {{-- @endcan
                        @can('redeem-request-list') --}}
                        <li>
                            <a href="{{url('/letest-request-product')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'letest-request-product') { echo 'side-menu--active'; } ?>">
                                
                           <div class="side-menu__title">
                             {{__('admin.product_req')}}
                             </div>
                     </a>
                </li>
                        {{-- @endcan --}}
                        
                    </ul>
                </li>
                @endrole

                
                @can('blog-list')
                <li>
                    <a href="javascript:;" class="side-menu <?php if(Request::segment(1) == 'blog' || Request::segment(1) == 'add-blog' || Request::segment(1) == 'edit-blog' || Request::segment(1) == 'slider' ||  Request::segment(1) == 'edit-blog-translation') { echo 'side-menu--active side-menu--open'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="monitor"></i> </div>
                        <div class="side-menu__title"> {{__('admin.blog_post')}} <i data-feather="chevron-down" class="menu__sub-icon"></i> </div>
                    </a>
                    <ul <?php if(Request::segment(1) == 'blog' || Request::segment(1) == 'add-blog' || Request::segment(1) == 'edit-blog' || Request::segment(1) == 'slider' ||  Request::segment(1) == 'edit-blog-translation') { ?>class="side-menu__sub-open" style="display: block;"<?php }else{ ?> style="display: none;"<?php } ?>>
                        @can('blog-create')
                        <li>
                            <a href="{{url('/add-blog')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'add-blog') { echo 'side-menu--active'; } ?>">
                                <div class="side-menu__title"> {{__('admin.create_post')}} </div>
                            </a>
                        </li>
                        @endcan
                        <li>
                            <a href="{{url('/blog')}}/{{$layout}}/{{$theme}}?post=all" class="side-menu <?php if(isset($_GET['post'])){ if($_GET['post']=='all'){ echo "side-menu--active"; } } ?>" >
                                <div class="side-menu__title"> {{__('admin.all_post')}} </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('/blog')}}/{{$layout}}/{{$theme}}?post=publish" class="side-menu <?php if(isset($_GET['post'])){ if($_GET['post']=='publish'){ echo "side-menu--active"; } } ?>">
                                <div class="side-menu__title"> {{__('admin.published_post')}} </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('/blog/')}}/{{$layout}}/{{$theme}}?post=unpublish" class="side-menu <?php if(isset($_GET['post'])){ if($_GET['post']=='unpublish'){ echo "side-menu--active"; } } ?>">
                                <div class="side-menu__title"> {{__('admin.unpublished_post')}} </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('/blog/')}}/{{$layout}}/{{$theme}}?post=draft" class="side-menu <?php if(isset($_GET['post'])){ if($_GET['post']=='draft'){ echo "side-menu--active"; } } ?>">
                                <div class="side-menu__title"> {{__('admin.draft_post')}} </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('/slider/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(isset($_GET['post'])){ if($_GET['post']=='slider'){ echo "side-menu--active"; } } if(Request::segment(1) == 'slider') { echo 'side-menu--active'; } ?>">
                                <div class="side-menu__title"> {{__('admin.slider_post')}} </div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                @can('feed-item-list')
                
                @endcan
                
                
                 @role('admin')
                <li>
                    <a href="javascript:;" class="side-menu <?php if(Request::segment(1) == 'feed-back' || (Request::segment(1) == 'story') || (Request::segment(1) == 'request-product')) { echo 'side-menu--active side-menu--open'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="users"></i> </div>
                        <div class="side-menu__title"> {{__('admin.user_stories_redeem')}} <i data-feather="chevron-down" class="menu__sub-icon"></i> </div>
                    </a>
                    <ul <?php if(Request::segment(1) == 'story' || (Request::segment(1) == 'request-product') || (Request::segment(1) == 'feed-back')) { ?>class="side-menu__sub-open" style="display: block;"<?php }else{ ?> style="display: none;"<?php } ?>>
                       
                       
                        <li>
                            <a href="{{url('/story')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'story') { echo 'side-menu--active'; } ?>">
                                <div class="side-menu__title">
                                    {{__('admin.story')}}
                                </div>
                            </a>
                        </li> 
                     
                      
                        <li>
                            <a href="{{url('/letest-request-product')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'letest-request-product') { echo 'side-menu--active'; } ?>">
                                <div class="side-menu__title">
                                    {{__('admin.product_req')}}
                                </div>
                            </a>
                        </li>
                      
                        <!-- <li>
                            <a href="{{url('/feed-back')}}/{{$layout}}/{{$theme}}" class="side-menu <?php //if(Request::segment(1) == 'feed-back') { echo 'side-menu--active'; } ?>">
                                <div class="side-menu__title">
                                    {{__('admin.feedback')}}
                                </div>
                            </a>
                        </li> -->
                       
                        
                    </ul>
                </li>
            @endrole

                @can('rss-feed-list')
                <li>
                    <a href="{{url('/rss-feed-src')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'rss-feed-src') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"><i data-feather="rss"></i></div>
                        <div class="side-menu__title">
                            {{__('admin.rss_feed')}}
                        </div>
                    </a>
                </li>
                @endcan

                @can('category-list')
                <li>
                    <a href="{{url('/category/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'category') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"><i data-feather="layers"></i></div>
                        <div class="side-menu__title">
                            {{__('admin.categories')}}
                        </div>
                    </a>
                </li>
                @endcan

                @can('ads-list')
                <li>
                    <a href="javascript:;" class="side-menu ">
                        <div class="side-menu__icon"> <i data-feather="target"></i> </div>
                        <div class="side-menu__title"> Ads Manage<i data-feather="chevron-down" class="menu__sub-icon"></i> </div>
                    </a>
                    <ul <?php if(in_array(Request::segment(1), ['ads','create-add','edit-ad','add-ads-slider','change-order','show-preview'], true)) { ?>class="side-menu__sub-open" style="display: block;"<?php }else{ ?> style="display: none;"<?php } ?>>
                        @can('ads-create')
                            <li>
                                <a href="{{url('/create-add')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'create-add') { echo 'side-menu--active'; } ?>">
                                    <div class="side-menu__title"> Create Ads </div>
                                </a>
                            </li>
                        @endcan
                        <li>
                            <a href="{{url('/ads')}}/{{$layout}}/{{$theme}}?post=all" class="side-menu <?php if(Request::segment(1) == 'ads' && isset($_GET['post']) && $_GET['post']=='all'){ echo 'side-menu--active'; } ?>" >
                                <div class="side-menu__title"> Ads List </div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
              
                @can('ads-list')
                <li>
                    <a href="javascript:;" class="side-menu ">
                        <div class="side-menu__icon"> <i data-feather="file-text"></i> </div>
                        <div class="side-menu__title"> News Ads Manage<i data-feather="chevron-down" class="menu__sub-icon"></i> </div>
                    </a>
                    <ul <?php if(in_array(Request::segment(1), ['news-ads','news-create-ads','edit-Newsad'], true)) { ?>class="side-menu__sub-open" style="display: block;"<?php }else{ ?> style="display: none;"<?php } ?>>
                        @can('ads-create')
                            <li>
                                <a href="{{url('/news-create-ads')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'news-create-ads') { echo 'side-menu--active'; } ?>">
                                    <div class="side-menu__title"> Create News Ads </div>
                                </a>
                            </li>
                        @endcan
                        <li>
                            <a href="{{url('/news-ads')}}/{{$layout}}/{{$theme}}?post=all" class="side-menu <?php if(Request::segment(1) == 'news-ads' && isset($_GET['post']) && $_GET['post']=='all'){ echo 'side-menu--active'; } ?>" >
                                <div class="side-menu__title"> News Ads List </div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan

                @can('live-news-list')
                {{-- <li>
                    <a href="{{url('/live-news/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'live-news') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"><i data-feather="monitor"></i></div>
                        <div class="side-menu__title">
                            {{__('admin.live_news')}}
                        </div>
                    </a>
                </li> --}}
                @endcan

                @can('epaper-list')
                {{-- <li>
                    <a href="{{url('/e-paper/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'e-paper') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"><i data-feather="file-text"></i></div>
                        <div class="side-menu__title">
                            {{__('admin.e_paper')}}
                        </div>
                    </a>
                </li> --}}
                @endcan
                
               

                @can('cms-pages-list')
                <li>
                    <a href="{{url('/cms-pages/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'cms-pages' ||  Request::segment(1) == 'edit-cms-page' || Request::segment(1) == 'edit-cms-page-translation') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="edit"></i> </div>
                        <div class="side-menu__title">
                            {{__('admin.cms_pages')}}
                        </div>
                    </a>
                </li>
                @endcan
                
                @if(Gate::check('user-list') || Gate::check('sub-admin-list'))
                <li>
                    <a href="javascript:;" class="side-menu <?php if(Request::segment(1) == 'users' || Request::segment(1) == 'sub-admin') { echo 'side-menu--active side-menu--open'; } ?>">
                        <div class="side-menu__icon"> <i data-feather="users"></i> </div>
                        <div class="side-menu__title"> {{__('admin.users')}} <i data-feather="chevron-down" class="menu__sub-icon"></i> </div>
                    </a>
                    <ul <?php if(Request::segment(1) == 'users' || Request::segment(1) == 'sub-admin') { ?>class="side-menu__sub-open" style="display: block;"<?php }else{ ?> style="display: none;"<?php } ?>>
                        @can('user-list')
                        <li>
                            <a href="{{url('/users/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'users') { echo 'side-menu--active'; } ?>">
                                <div class="side-menu__title"> {{__('admin.users')}} </div>
                            </a>
                        </li>
                        @endcan
                        @can('sub-admin-list')
                        <li>
                            <a href="{{url('/sub-admin/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'sub-admin') { echo 'side-menu--active'; } ?>">
                                <div class="side-menu__title"> {{__('admin.manager')}} </div>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endif

                @can('search-log-list')
                <li>
                    <a href="{{url('/search-log/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'search-log') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon">
                            <i data-feather="search"></i>
                        </div>
                        <div class="side-menu__title">
                            {{__('admin.search_log')}}
                        </div>
                    </a>
                </li>
                @endcan

                @can('setting-list')
                <li>
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/site-setting" class="side-menu <?php if(Request::segment(1) == 'setting' || Request::segment(1) ==  'settings') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon">
                            <i data-feather="settings"></i>
                        </div>
                        <div class="side-menu__title">
                            {{__('admin.settings')}}
                        </div>
                    </a>
                </li>
                @endcan

                @can('show-notification-form')
                <li>
                    <a href="{{url('/send-notification/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'send-notification') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon">
                            <i data-feather="bell"></i>
                        </div>
                        <div class="side-menu__title">
                            {{__('admin.send_notification')}}
                        </div>
                    </a>
                </li>
                @endcan

                @can('quote-list')
                <!--<li>
                    <a href="{{url('/quotes/')}}/{{$layout}}/{{$theme}}" class="side-menu <?php if(Request::segment(1) == 'quotes') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon">
                            <i data-feather="book-open"></i>
                        </div>
                        <div class="side-menu__title">
                            {{__('admin.quotes')}}
                        </div>
                    </a>
                </li>-->
                @endcan

                @can('quote-list')
                <li>
                    <a href="{{url('/languages/translations')}}?layout={{$layout}}&theme={{$theme}}" class="side-menu <?php if(Request::segment(1) == 'languages' || Request::segment(2) ==  'translations') { echo 'side-menu--active'; } ?>">
                        <div class="side-menu__icon">
                            <i data-feather="pen-tool"></i>
                        </div>
                        <div class="side-menu__title">
                            {{__('admin.localization')}}
                        </div>
                    </a>
                </li>
                @endcan
                
            </ul>
        </nav>
        <div class="content">

            @yield('subcontent')
        </div>
    </div>
@endsection
