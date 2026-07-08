@extends('../site/layout/main') @section('content')
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss">
  	<link rel="stylesheet" href="{{ asset('site/css/social-icon-font.css')}}" type="text/css" media="all" />
    <div class="options_layout_container full_layout_enable_front">
        <!-- Start header -->
        @include('../site/layout/components/header_1')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')
		<style>
      	<style>
        h1{
            font-size: {{setting('h_1_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        h2{
            font-size: {{setting('h_2_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        h3{
            font-size: {{setting('h_3_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        h4{
            font-size: {{setting('h_4_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }
        p{
            font-size: {{setting('p_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        span{
            font-size: {{setting('span_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        label{
            font-size: {{setting('lable_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        body{
            font-family:{{setting('font_family')}}!important;
            
        }
        
        /*{
            font-family:{{setting('font_family')}}!important;
        }*/
        </style>
      	</style>
        <div class="jl_home_section jl_home_slider">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 jl_mid_main_3col">
                        <div class="page_builder_slider jelly_homepage_builder">
                            <div class="jl_slider_nav_tab large_center_slider_container">
                                <div class="row header-main-slider-large">
                                    <div class="col-md-12">
                                        <div class="large_center_slider_wrapper">
                                            <div class="home_slider_header_tab jelly_loading_pro">
                                                @foreach($slider_post as $slider_blog)
                                                <div class="item">
                                                    <div class="banner-carousel-item">
                                                        <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/original')}}/{{$slider_blog->blog_image}}');"></span>
                                                        <div class="banner-container">
                                                            <div class="container">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="banner-inside-wrapper">
                                                                            @if($slider_blog->blog_category_data)
                                                                                <span class="meta-category-small"><a class="post-category-color-text" style="background: {{$slider_blog->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$slider_blog->blog_category_data->category->slug}}">{{$slider_blog->blog_category_data->category->name}}</a></span>
                                                                            @endif
                                                                            
                                                                            <span class="jl_post_meta">
                                                                                <span class="jl_author_img_w slider_1">
                                                                                    @if($slider_blog->author)
                                                                                    <img src="{{url('upload/author/original')}}/{{$slider_blog->author->image}}" width="30" height="30" alt="{{$slider_blog->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo slider_main_img_1"  onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"/>

                                                                                    @if($slider_blog->content_type  == 'audio')
                                                                                        <img src="{{url('upload/audio-image.png')}}" class="slider_child_img_1"/>
                                                                                    @elseif($slider_blog->content_type  == 'video')
                                                                                        <img src="{{url('upload/video-image.png')}}" class="slider_child_img_1"/>
                                                                                    @endif

                                                                                    <a title="Posts by {{$slider_blog->author->name}}" rel="author">{{$slider_blog->author->name}}</a>
                                                                                    @endif
                                                                                </span>
                                                                                <h5><a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" tabindex="-1">{{$slider_blog->title}}</a></h5>
                                                                                <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($slider_blog->schedule_date))}}</span>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            <div class="jlslide_tab_nav_container">
                                                <div class="jlslide_tab_nav_row">
                                                    <div class="home_slider_header_tab_nav news_tiker_loading_pro">
                                                        @foreach($slider_post as $slider_blogs)
                                                        <div class="item">
                                                            <div class="banner-carousel-item">
                                                                <span class="image_small_nav" style="background-image: url('{{url('upload/blog/banner/360')}}/{{$slider_blogs->blog_image}}');"></span>
                                                                <h5>
                                                                    {{$slider_blogs->title}}
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Home grid section -->
        <div class="jl_home_section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 jl_mid_main_3col">
                        <div class="jl_3col_wrapin">
                            <div class="jelly_homepage_builder jl_nonav_margin homepage_builder_3grid_post jl_fontsize22 jl_cus_grid3 colstyle1">
                                <div class="homepage_builder_title">
                                    <h2>
                                        {{ __('frontend.editor_choice') }} 
                                    </h2>
                                    <span class="jl_hsubt"></span>
                                </div>
                                <div class="jl_wrapper_row">
                                    <div class="row">
                                        @foreach($recent_middle_post as $recent_middle_blogs)

                                        <div class="col-md-4 blog_grid_post_style jl_row_1">
                                            <div class="jl_grid_box_wrapper img_absolute_1">
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
                                                            src="{{url('upload/blog/banner/360')}}/{{$recent_middle_blogs->blog_image}}"
                                                            class="blog_image_resize attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image main_img_1"
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
                                                            <?php echo substr(strip_tags($recent_middle_blogs->title),0,65); if(strlen($recent_middle_blogs->title)>64){ echo "..."; }?>
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
                                                            />
                                                            <a title="{{$recent_middle_blogs->author->name}}" rel="author">{{$recent_middle_blogs->author->name}}</a>
                                                            @endif
                                                        </span>
                                                        <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($recent_middle_blogs->schedule_date))}}</span>
                                                    </span>
                                                    <div class="content_post_grid">
                                                        <p><?php echo substr(strip_tags($recent_middle_blogs->description),0,80); if(strlen($recent_middle_blogs->description)>65){ echo "..."; } ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        <div class="clear_line_3col_home"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Home main right -->
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
                                        <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/800')}}/{{$top_of_week_post[0]->blog_image}}');"></span>

                                        <a href="{{url('/blog-details')}}/{{$top_of_week_post[0]->slug}}" class="link_grid_header_absolute" title="{{$top_of_week_post[0]->title}}" rel="blog"></a>
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
                                            <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$top_of_week_post[0]->slug}}" rel="blog"><?php echo substr(strip_tags($top_of_week_post[0]->title),0,85); if(strlen($top_of_week_post[0]->title)>75){ echo "..."; }?>  </a></h3>
                                            <span class="jl_post_meta">
                                                <span class="jl_author_img_w top_week_1">
                                                    @if($top_of_week_post[0]->author)
                                                    <img
                                                        src="{{url('upload/author/original')}}/{{$top_of_week_post[0]->author->image}}"
                                                        width="30"
                                                        height="30"
                                                        alt="{{$top_of_week_post[0]->author->name}}"
                                                        class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
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
                                    <a href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}" class="jl_small_format feature-image-link image_post featured-thumbnail" rel="blog">
                                        <img
                                            width="120"
                                            height="120"
                                            src="{{url('upload/blog/banner/360')}}/{{$top_of_week_post[$i]->blog_image}}"
                                            class="attachment-disto_small_feature size-disto_small_feature wp-post-image"
                                            alt="{{$top_of_week_post[$i]->title}}"
                                            onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"
                                        />
                                        <div class="background_over_image">
                                            @if($top_of_week_post[$i]->content_type == 'audio')
                                            <img src="{{url('upload/audio-image.png')}}" class="top_week_child_img_1" />
                                            @elseif($top_of_week_post[$i]->content_type == 'video')
                                            <img src="{{url('upload/video-image.png')}}" class="top_week_child_img_1" />
                                            @endif
                                        </div>
                                    </a>
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
                                        <h3 class="feature-post-title"><a href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}" rel="blog"><?php echo substr(strip_tags($top_of_week_post[$i]->title),0,40); if(strlen($top_of_week_post[$i]->title)>35){ echo "..."; } ?></a></h3>
                                        <span class="post-meta meta-main-img auto_image_with_date">
                                            <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($top_of_week_post[$i]->schedule_date))}}</span>
                                        </span>
                                    </div>
                                </div>
                                <?php } } ?>
                                @endif
                            </div>
                            @if(count($top_of_week_post)>5)
                            <div class="jelly_homepage_builder jl_nonav_margin homepage_builder_3grid_post jl_cus_grid_overlay jl_fontsize20 jl_cus_grid3">
                                <div class="jl_wrapper_row jl-post-block-314983">
                                    <div class="row">
                                        <?php for($i=5;$i<8;$i++){ if(isset($top_of_week_post[$i])){ ?>
                                        <div class="col-md-4 blog_grid_post_style jl_row_1">
                                            <div class="jl_grid_box_wrapper">
                                                <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/360')}}/{{$top_of_week_post[$i]->blog_image}}');"></span>
                                                <a
                                                    href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}"
                                                    class="link_grid_header_absolute"
                                                    title="{{$top_of_week_post[$i]->title}}"
                                                    rel="blog"
                                                ></a>
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
                                                <div class="post-entry-content">
                                                    <h3 class="image-post-title">
                                                        <a href="{{url('/blog-details')}}/{{$top_of_week_post[$i]->slug}}" rel="blog"> <?php echo substr(strip_tags($top_of_week_post[$i]->title),0,40); if(strlen($top_of_week_post[$i]->title)>35){ echo "..."; } ?></a>
                                                    </h3>
                                                    <span class="jl_post_meta">
                                                        <span class="jl_author_img_w">
                                                            @if($top_of_week_post[$i]->author)
                                                            <img
                                                                src="{{url('upload/author/original')}}/{{$top_of_week_post[$i]->author->image}}"
                                                                width="30"
                                                                height="30"
                                                                alt="{{$top_of_week_post[$i]->author->name}}"
                                                                class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                            />
                                                            <a title="{{$top_of_week_post[$i]->author->name}}" rel="author">{{$top_of_week_post[$i]->author->name}}</a>
                                                            @endif
                                                        </span>
                                                        <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($top_of_week_post[$i]->schedule_date))}}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } } ?>
                                        <div class="clear_line_3col_home"></div>
                                    </div>
                                </div>
                            </div>
                            @endif
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
                        <div class="post_list_medium_widget jl_nonav_margin page_builder_listpost jelly_homepage_builder jl-post-block-725291">
                            <?php for($i=0;$i<6;$i++){ if(isset($editors_post[$i])){ ?>
                            <div class="blog_list_post_style">
                                <div class="image-post-thumb featured-thumbnail home_page_builder_thumbnial">
                                    <div class="jl_img_container">
                                        <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/360')}}/{{$editors_post[$i]->blog_image}}');"></span>

                                        <div class="background_over_image">
                                            @if($editors_post[$i]->content_type == 'audio')
                                            <img src="{{url('upload/audio-image.png')}}" class="top_week_child_img_1" />
                                            @elseif($editors_post[$i]->content_type == 'video')
                                            <img src="{{url('upload/video-image.png')}}" class="top_week_child_img_1" />
                                            @endif
                                        </div>

                                        <a href="{{url('/blog-details')}}/{{$editors_post[$i]->slug}}" class="link_grid_header_absolute" rel="blog"></a>
                                    </div>
                                </div>
                                <div class="post-entry-content">
                                    <span class="meta-category-small">
                                        @if($editors_post[$i]->blog_category_data)
                                        <a class="post-category-color-text" style="background: {{$editors_post[$i]->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$editors_post[$i]->blog_category_data->category->slug}}" rel="category">
                                            {{$editors_post[$i]->blog_category_data->category->name}}
                                        </a>
                                        @endif
                                    </span>
                                    <span class="post-meta meta-main-img auto_image_with_date">
                                        <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($editors_post[$i]->schedule_date))}}</span>
                                    </span>
                                    <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$editors_post[$i]->slug}}" rel="blog">
                                      <?php echo substr(strip_tags($editors_post[$i]->title),0,100); if(strlen($editors_post[$i]->title)>95){ echo "..."; } ?>
                                     </a></h3>
                                    <div class="large_post_content">
                                        <p><?php echo substr(strip_tags($editors_post[$i]->description),0,250);?>..</p>
                                    </div>
                                </div>
                            </div>
                            <?php } } ?>
                        </div>
                    </div>
                    <!-- start sidebar -->
                    @include('../site/layout/components/side_content')
                    <!-- end sidebar -->
                </div>

                <div class="jelly_homepage_builder jl_nonav_margin homepage_builder_3grid_post jl_fontsize18 jl_cus_grid4 colstyle1">
                    <div class="jl_wrapper_row jl-post-block-111621">
                        <div class="row">
                            <?php for($i=6;$i<10;$i++){ if(isset($editors_post[$i])){ ?>
                            <div class="col-md-4 blog_grid_post_style jl_row_1">
                                <div class="jl_grid_box_wrapper">
                                    <div class="image-post-thumb">
                                        <a href="{{url('/blog-details')}}/{{$editors_post[$i]->slug}}" class="link_image featured-thumbnail" title="{{$editors_post[$i]->title}}" rel="blog">
                                            <img
                                                width="400"
                                                height="280"
                                                src="{{url('upload/blog/banner/360')}}/{{$editors_post[$i]->blog_image}}"
                                                class="attachment-disto_slider_grid_small size-disto_slider_grid_small wp-post-image"
                                                alt="{{$editors_post[$i]->title}}"
                                                onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                            />

                                            <div class="background_over_image">
                                                @if($editors_post[$i]->content_type == 'audio')
                                                <img src="{{url('upload/audio-image.png')}}" class="top_week_child_img_1" />
                                                @elseif($editors_post[$i]->content_type == 'video')
                                                <img src="{{url('upload/video-image.png')}}" class="top_week_child_img_1" />
                                                @endif
                                            </div>
                                        </a>
                                        <span class="meta-category-small">
                                            @if($editors_post[$i]->blog_category_data)
                                            <a class="post-category-color-text" style="background: {{$editors_post[$i]->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$editors_post[$i]->blog_category_data->category->slug}}" rel="category">
                                                {{$editors_post[$i]->blog_category_data->category->name}}
                                            </a>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="post-entry-content">
                                        <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$editors_post[$i]->slug}}" rel="blog"><?php echo substr(strip_tags($editors_post[$i]->title),0,100); if(strlen($editors_post[$i]->title)>95){ echo "..."; } ?></a></h3>
                                        <span class="jl_post_meta">
                                            <span class="jl_author_img_w">
                                                @if($editors_post[$i]->author)
                                                <img
                                                    src="{{url('upload/author/original')}}/{{$editors_post[$i]->author->image}}"
                                                    width="30"
                                                    height="30"
                                                    alt="{{$editors_post[$i]->author->name}}"
                                                    class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                />
                                                <a title="{{$editors_post[$i]->author->name}}" rel="author">{{$editors_post[$i]->author->name}}</a>
                                                @endif
                                            </span>
                                            <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($editors_post[$i]->schedule_date))}}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php } } ?>
                            <div class="clear_line_3col_home"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- end content -->
        <!-- Start footer -->
        @include('../site/layout/components/footer')
        <!-- End footer -->
    </div>
    <div id="go-top">
        <a href="#go-top"><i class="fa fa-angle-up"></i></a>
    </div>
    @endsection
</div>
