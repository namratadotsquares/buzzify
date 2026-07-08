<footer id="footer-container" class="enable_footer_columns_dark">
    <div class="footer-columns">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <span class="jl_none_space"></span>
                    <div id="disto_about_us_widget-3" class="widget jellywp_about_us_widget">
                        <div class="widget_jl_wrapper about_widget_content">
                            <span class="jl_none_space"></span>
                            <div class="widget-title">
                                <h2>{{ __('frontend.about_us') }}</h2>
                            </div>
                            <div class="jellywp_about_us_widget_wrapper">
                                <p>{{setting('footer_about')}}</p>
                                <div class="social_icons_widget">
                                    <ul class="social-icons-list-widget icons_about_widget_display">
                                        @foreach($social as $social_icons)
                                        <li>
                                            <a href="{{$social_icons->url}}" class="{{$social_icons->name}} social-icon-link" target="_blank">
                                                <i class="fa <?php echo $social_icons->icon;?>"></i>
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <span class="jl_none_space"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <span class="jl_none_space"></span>
                    <div id="disto_recent_post_widget-3" class="widget post_list_widget">
                        <div class="widget_jl_wrapper">
                            <span class="jl_none_space"></span>
                            <div class="widget-title">
                                <h2>{{ __('frontend.recent_posts') }}</h2>
                            </div>
                            <div>
                                <ul class="feature-post-list recent-post-widget">
                                    @foreach($recent_blog as $blog)
                                    <li>
                                        <a href="{{url('/blog-details')}}/{{$blog->slug}}" class="jl_small_format feature-image-link image_post featured-thumbnail" title="{{$blog->title}}">
                                            <img width="120" height="120" src="{{url('upload/blog/banner/360/')}}/{{$blog->blog_image}}" class="attachment-disto_small_feature size-disto_small_feature wp-post-image" alt="{{$blog->title}}" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"/>
                                            
                                                <div class="background_over_image">
                                                        
                                                    @if($blog->content_type  == 'audio')
                                                        <img src="{{url('upload/audio-image.png')}}" class="top_week_child_img_1"/>
                                                    @elseif($blog->content_type  == 'video')
                                                        <img src="{{url('upload/video-image.png')}}" class="top_week_child_img_1"/>
                                                    @endif

                                                </div>

                                        </a>
                                        <div class="item-details">
                                            <span class="meta-category-small">
                                                @if($blog->blog_category_data)
                                                <span class="meta-category-small"><a class="post-category-color-text" style="background: {{$blog->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$blog->blog_category_data->category->slug}}" rel="category">{{$blog->blog_category_data->category->name}}</a></span>
                                                @endif
                                            </span>


                                            <h3 class="feature-post-title"><a href="{{url('/blog-details')}}/{{$blog->slug}}"><?php echo substr(strip_tags($blog->title),0,30);?>...</a></h3>
                                            <span class="post-meta meta-main-img auto_image_with_date">
                                                <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($blog->created_at))}}</span>
                                            </span>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <span class="jl_none_space"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="categories-4" class="widget widget_categories">
                        <div class="widget-title">
                            <h2>{{ __('frontend.categories') }}</h2>
                        </div>
                        <ul>
                            @foreach($category as $footer_cat)
                            <li class="cat-item">
                                <a href="{{url('category-blog')}}?category={{$footer_cat->slug}}" title="{{$footer_cat->blog_count}}">{{$footer_cat->name}}</a>
                                <span style="background: <?php echo $footer_cat->color; ?>">{{$footer_cat->blog_count}}</span>
                            </li>
                            @endforeach
                        </ul>

                    </div>


                    <?php 
                        $languages = DB::table('languages')->get();
                    ?>
                    <div id="categories-4" class="widget widget_categories">
                        <select id="user_language" class="form-control" >
                            @foreach($languages as  $language)
                                <option @if(isset($_COOKIE['lang_code']) && $_COOKIE['lang_code'] == $language->language) selected @endif value="{{$language->language}}">{{$language->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom enable_footer_copyright_dark">
        <div class="container">
            <div class="row bottom_footer_menu_text">
                <div class="col-md-6 footer-left-copyright">
                    © {{ __('frontend.copyright') }}
                    <?php echo date("Y");?>. {{setting('powered_by')}}
                </div>
                <div class="col-md-6 footer-menu-bottom">
                    <ul id="menu-footer-menu" class="menu-footer">
                        @foreach($site_content as $content)
                        <li class="menu-item menu-item-10"><a href="{{url('/')}}/{{$content->page_name}}">{{$content->title}}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>