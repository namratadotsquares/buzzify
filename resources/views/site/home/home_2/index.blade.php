@extends('../site/layout/main') @section('content')
<div class="options_layout_wrapper jl_none_box_styles jl_border_radiuss">
    <div class="options_layout_container full_layout_enable_front">
        <!-- Start header -->
        @include('../site/layout/components/header_2')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')
        <!-- start carousel -->

        <div class="jelly_homepage_builder jl_car_home jl_nonav_margin">
            <div class="jl_wrapper_row">
                <div class="row jelly_loading_pro jelly_cus_h619 jl_fontsize23 jl_builder_4carousel car_style4 jl_hide_author_img jl_hide_arrow jl_hide_dots">
                    @foreach($slider_post as $slider_blog)
                    <div class="col-md-3">
                        <div class="jl_car_wrapper">
                            <div class="jl_car_img_front">
                                <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/360/')}}/{{$slider_blog->blog_image}}');"></span>

                                <div class="background_over_image">
                                    @if($slider_blog->content_type == 'audio')
                                    <img src="{{url('upload/audio-image.png')}}" class="top_week_main_img_2" />
                                    @elseif($slider_blog->content_type == 'video')
                                    <img src="{{url('upload/video-image.png')}}" class="top_week_main_img_2" />
                                    @endif
                                </div>
								

                              	<a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" class="link_grid_header_absolute" title="{{$slider_blog->title}}" rel="blog"></a>
                                
                            </div>
                            <div class="post-entry-content">
                                <span class="meta-category-small">
                                    @if($slider_blog->blog_category_data)
                                    <span class="meta-category-small">
                                        <a class="post-category-color-text" style="background: {{$slider_blog->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$slider_blog->blog_category_data->category->slug}}" rel="category">
                                            {{$slider_blog->blog_category_data->category->name}}
                                        </a>
                                    </span>
                                    @endif
                                </span>
                                <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" rel="blog">{{$slider_blog->title}}</a></h3>
                                <span class="jl_post_meta">
                                    <span class="jl_author_img_w">
                                        @if($slider_blog->author)
                                        <img
                                            src="{{url('upload/author/original')}}/{{$slider_blog->author->image}}"
                                            width="30"
                                            height="30"
                                            alt="{{$slider_blog->author->name}}"
                                            class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                            onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"
                                        />

                                        <a href="javascript:;" title="{{$slider_blog->author->name}}" rel="author">{{$slider_blog->author->name}}</a>
                                        @endif
                                    </span>
                                    <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($slider_blog->schedule_date))}}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- end carousel -->
        <div class="jl_home_section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 jl_mid_main_3col">
                        <div class="jl_3col_wrapin">
                            <!-- start grid 3 col -->
                            <div class="jelly_homepage_builder jl_nonav_margin homepage_builder_3grid_post jl_fontsize22 jl_cus_grid3 colstyle1 jl_hide_author_img">
                                <div class="homepage_builder_title">
                                    <h2>
                                        {{ __('frontend.business_today') }}
                                    </h2>
                                    <span class="jl_hsubt">This is sample subtitle blog post section</span>
                                </div>
                                <div class="jl_wrapper_row jl-post-block-593245">
                                    <div class="row">
                                        @foreach($recent_middle_post as $recent_middle_blogs)
                                        <div class="box blog_grid_post_style jl_row_1">
                                            <div class="jl_grid_box_wrapper img_absolute_2">
                                                <div class="image-post-thumb">
                                                    <a
                                                        href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}"
                                                        class="link_image featured-thumbnail"
                                                        title="{{$recent_middle_blogs->title}}"
                                                        rel="blog"
                                                    >
                                                        <img
                                                            width="780"
                                                            height="450"
                                                            src="{{url('upload/blog/banner/360/')}}/{{$recent_middle_blogs->blog_image}}"
                                                            class="blog_image_resize attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image main_img_2"
                                                            alt="{{$recent_middle_blogs->title}}"
                                                            onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                                        />
                                                        <div class="background_over_image">
                                                            @if($recent_middle_blogs->content_type == 'audio')
                                                            <img src="{{url('upload/audio-image.png')}}" class="child_img_1" />
                                                            @elseif($recent_middle_blogs->content_type == 'video')
                                                            <img src="{{url('upload/video-image.png')}}" class="child_img_1" />
                                                            @endif
                                                        </div>
                                                    </a>
                                                    <span class="meta-category-small">
                                                        @if($recent_middle_blogs->blog_category_data)
                                                        <a
                                                            class="post-category-color-text"
                                                            style="background: {{$recent_middle_blogs->blog_category_data->category->color}};"
                                                            href="{{url('category-blog')}}?category={{$recent_middle_blogs->blog_category_data->category->slug}}"
                                                            rel="category"
                                                        >
                                                            {{$recent_middle_blogs->blog_category_data->category->name}}
                                                        </a>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="post-entry-content">
                                                    <h3 class="image-post-title">
                                                        <a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" rel="blog">
                                                            <?php echo substr(strip_tags($recent_middle_blogs->title),0,65); if(strlen($recent_middle_blogs->title)>64){ echo "..."; } ?>
                                                        </a>
                                                    </h3>
                                                    <span class="jl_post_meta">
                                                        <span class="jl_author_img_w">
                                                            @if($recent_middle_blogs->author)
                                                            <img
                                                                src="{{url('upload/author/original')}}/{{$recent_middle_blogs->author->image}}"
                                                                width="30"
                                                                height="30"
                                                                alt="{{$recent_middle_blogs->author->name}}"
                                                                class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                                onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"
                                                            />
                                                            <a title="{{$recent_middle_blogs->author->name}}" rel="author">{{$recent_middle_blogs->author->name}}</a>
                                                            @endif
                                                        </span>
                                                        <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($recent_middle_blogs->schedule_date))}}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="clear_line_3col_home"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- end grid 3 col -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(count($top_of_week_post)>0)
        <div class="jl_home_section jl_home_mbg">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 jl_mid_main_3col">
                        <div class="jl_3col_wrapin">
                            <div class="jl_main_with_right_post jelly_homepage_builder">
                                <div class="homepage_builder_title">
                                    <h2 class="builder_title_home_page">
                                        {{ __('frontend.top_of_week') }}
                                    </h2>
                                </div>

                                @if(isset($top_of_week_post[0]))
                                <div class="jl_main_post_style_padding">
                                    <div class="jl_main_post_style">
                                        <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/800/')}}/{{$top_of_week_post[0]->blog_image}}');"></span>
                                        @if($top_of_week_post[0]->category!='')<a href="{{url('/blog-details')}}/{{$top_of_week_post[0]->category['slug']}}/{{$top_of_week_post[0]->slug}}" class="link_grid_header_absolute" title="{{$top_of_week_post[0]->title}}" rel="blog"></a>@endif
                                        <div class="post-entry-content">
                                            <span class="meta-category-small">
                                                @if($top_of_week_post[0]->blog_category_data)
                                                <a
                                                    class="post-category-color-text"
                                                    style="background: {{$top_of_week_post[0]->blog_category_data->category->color}};"
                                                    href="{{url('category-blog')}}?category={{$top_of_week_post[0]->blog_category_data->category->slug}}"
                                                    rel="category"
                                                >
                                                    {{$top_of_week_post[0]->blog_category_data->category->name}}
                                                </a>
                                                @endif
                                            </span>
                                            <h3 class="image-post-title">@if($top_of_week_post[0]->category!='')<a href="{{url('/blog-details')}}/{{$top_of_week_post[0]->slug}}" rel="blog"><?php echo substr(strip_tags($top_of_week_post[0]->title),0,200); if(strlen($top_of_week_post[0]->title)>35){ echo "..."; } ?></a>@else <?php echo substr(strip_tags($top_of_week_post[0]->title),0,200); if(strlen($top_of_week_post[0]->title)>35){ echo "..."; } ?> @endif</h3>
                                            <span class="jl_post_meta">
                                                <span class="jl_author_img_w">
                                                    @if($top_of_week_post[0]->author)
                                                    <img
                                                        src="{{url('upload/author/original')}}/{{$top_of_week_post[0]->author->image}}"
                                                        width="30"
                                                        height="30"
                                                        alt="{{$top_of_week_post[0]->author->name}}"
                                                        class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                        onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"
                                                    />

                                                    <a title="{{$top_of_week_post[0]->author->name}}" rel="author">{{$top_of_week_post[0]->author->name}}</a>
                                                    @endif
                                                </span>
                                                <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($top_of_week_post[0]->schedule_date))}}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endif @if(count($top_of_week_post)>1)
                                <?php for($i=1;$i<5;$i++){ if(isset($top_of_week_post[$i])){ ?>
                                <div class="jl_list_post_wrapper">
                                    @if($top_of_week_post[0]->category!='')<a href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}" class="jl_small_format feature-image-link image_post featured-thumbnail" rel="blog">
                                        <a href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}">
                                            <img
                                                width="120"
                                                height="120"
                                                src="{{url('upload/blog/banner/360/')}}/{{$top_of_week_post[$i]->blog_image}}"
                                                class="attachment-disto_small_feature size-disto_small_feature wp-post-image"
                                                alt="{{$top_of_week_post[$i]->title}}"
                                                onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                            />
                                        </a>
                                        <div class="background_over_image">
                                            @if($top_of_week_post[$i]->content_type == 'audio')
                                            <img src="{{url('upload/audio-image.png')}}" class="top_week_child_img_2" />
                                            @elseif($top_of_week_post[$i]->content_type == 'video')
                                            <img src="{{url('upload/video-image.png')}}" class="top_week_child_img_2" />
                                            @endif
                                        </div>
                                    </a>
                                  	@else
                                        <a href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}">
                                            <img
                                                width="120"
                                                height="120"
                                                src="{{url('upload/blog/banner/360/')}}/{{$top_of_week_post[$i]->blog_image}}"
                                                class="attachment-disto_small_feature size-disto_small_feature wp-post-image"
                                                alt="{{$top_of_week_post[$i]->title}}"
                                                onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                            />
                                        </a>
                                        <div class="background_over_image">
                                            @if($top_of_week_post[$i]->content_type == 'audio')
                                            <img src="{{url('upload/audio-image.png')}}" class="top_week_child_img_2" />
                                            @elseif($top_of_week_post[$i]->content_type == 'video')
                                            <img src="{{url('upload/video-image.png')}}" class="top_week_child_img_2" />
                                            @endif
                                        </div>
                                 	@endif
                                    <div class="item-details">
                                        <span class="meta-category-small">
                                            @if($top_of_week_post[$i]->blog_category_data)
                                            <a
                                                class="post-category-color-text"
                                                style="background: {{$top_of_week_post[$i]->blog_category_data->category->color}};"
                                                href="{{url('category-blog')}}?category={{$top_of_week_post[$i]->blog_category_data->category->slug}}"
                                                rel="category"
                                            >
                                                {{$top_of_week_post[$i]->blog_category_data->category->name}}
                                            </a>
                                            @endif
                                        </span>
                                        @if($top_of_week_post[0]->category!='')
                                      		<h3 class="feature-post-title"><a href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}" rel="blog"><?php echo substr(strip_tags($top_of_week_post[$i]->title),0,25); if(strlen($top_of_week_post[0]->title)>20){ echo "..."; }  ?></a></h3>
                                      	@else
                                      		<h3 class="feature-post-title"><?php echo substr(strip_tags($top_of_week_post[$i]->title),0,25); if(strlen($top_of_week_post[0]->title)>20){ echo "..."; }  ?></h3>
                                      	@endif
                                        <span class="post-meta meta-main-img auto_image_with_date">
                                            <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($top_of_week_post[$i]->schedule_date))}}</span>
                                        </span>
                                    </div>
                                </div>
                                <?php } } ?>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif @if(count($editors_post)>0)
        <div class="jl_home_section">
            <div class="container">
                <div class="row">
                    <div class="col-md-8" id="content">
                        <!-- start grid sidebar -->
                        <div class="jelly_homepage_builder jl_nonav_margin homepage_builder_3grid_post jl_fontsize22 jl_cus_grid2 colstyle1">
                            <div class="homepage_builder_title">
                                <h2>
                                    {{ __('frontend.more_from_blog') }}
                                </h2>
                                <span class="jl_hsubt">{{ __('frontend.more_from_blog_sub_heading') }}</span>
                            </div>
                            <div class="jl_wrapper_row jl-post-block-108832">
                                <div class="row">
                                    @foreach($editors_post as $recent_middle_blogs)
                                    <div class="box blog_grid_post_style jl_row_1">
                                        <div class="jl_grid_box_wrapper">
                                            <div class="image-post-thumb">
                                                <a
                                                    href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}"
                                                    class="link_image featured-thumbnail"
                                                    title="{{$recent_middle_blogs->title}}"
                                                    rel="blog"
                                                >
                                                    <img
                                                        width="780"
                                                        height="450"
                                                        src="{{url('upload/blog/banner/360/')}}/{{$recent_middle_blogs->blog_image}}"
                                                        class="blog_image_resize attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image"
                                                        alt="{{$recent_middle_blogs->title}}"
                                                        onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                                    />
                                                    <div class="background_over_image">
                                                        @if($recent_middle_blogs->content_type == 'audio')
                                                        <img src="{{url('upload/audio-image.png')}}" class="child_img_1" />
                                                        @elseif($recent_middle_blogs->content_type == 'video')
                                                        <img src="{{url('upload/video-image.png')}}" class="child_img_1" />
                                                        @endif
                                                    </div>
                                                </a>
                                              
                                                <span class="meta-category-small">
                                                    @if($recent_middle_blogs->blog_category_data)
                                                    <a
                                                        class="post-category-color-text"
                                                        style="background: {{$recent_middle_blogs->blog_category_data->category->color}};"
                                                        href="{{url('category-blog')}}?category={{$recent_middle_blogs->blog_category_data->category->slug}}"
                                                        rel="category"
                                                    >
                                                        {{$recent_middle_blogs->blog_category_data->category->name}}
                                                    </a>
                                                    @endif
                                                </span>
                                             	
                                            </div>
                                            <div class="post-entry-content">
                                                <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" rel="blog">
                                                            <?php echo substr(strip_tags($recent_middle_blogs->title),0,72); if(strlen($recent_middle_blogs->title)>71){ echo "..."; } ?>
                                                        </a></h3>
                                                <span class="jl_post_meta">
                                                    <span class="jl_author_img_w">
                                                        @if($recent_middle_blogs->author)
                                                        <img
                                                            src="{{url('upload/author/original')}}/{{$recent_middle_blogs->author->image}}"
                                                            width="30"
                                                            height="30"
                                                            alt="{{$recent_middle_blogs->author->name}}"
                                                            class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                            onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"
                                                        />

                                                        <a href="javascript:;" title="{{$recent_middle_blogs->author->name}}" rel="author">{{$recent_middle_blogs->author->name}}</a>
                                                        @endif
                                                    </span>
                                                    <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($recent_middle_blogs->schedule_date))}}</span>
                                                </span>
                                                <div class="content_post_grid">
                                                    <p>Mauris mattis auctor cursus. Phasellus tellus tellus, imperdiet ut imperdiet eu, iaculis a sem. Donec vehicula luctus nunc in...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="clear_line_3col_home"></div>
                                </div>
                            </div>
                        </div>
                        <!-- end grid sidebar -->
                    </div>

                    <!-- start sidebar -->
                    @include('../site/layout/components/side_content')
                    <!-- end sidebar -->
                </div>
            </div>
        </div>
        @endif
    </div>
    <!-- end content -->
    <!-- Start footer -->
    @include('../site/layout/components/footer')
    <!-- End footer -->
</div>
<div id="go-top">
    <a href="#go-top"><i class="fa fa-angle-up"></i></a>
</div>
@endsection
