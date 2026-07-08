	@extends('../site/layout/main') @section('content')
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss">
    <div class="options_layout_container full_layout_enable_front">
        <!-- Start header -->
        @include('../site/layout/components/header_1')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')

        <div class="jl_post_loop_wrapper">
            <div class="container" id="wrapper_masonry">
                <div class="row jl_front_b_cont">
                    <div class="col-md-12 jl_mid_main_3col">
                        <div class="jl_3col_wrapin">
                            <div id="pl-3476" class="panel-layout">
                                <div id="pg-3476-0" class="panel-grid panel-no-style">
                                    <div id="pgc-3476-0-0" class="panel-grid-cell">
                                        <span class="jl_none_space"></span>
                                        <div id="panel-3476-0-0-0" class="so-panel widget widget_disto_recent_grid5_widgets jl_widget_recent_grid5 panel-first-child panel-last-child" data-index="0">
                                            <div class="jl_grid5_builder jelly_homepage_builder">
                                                <div class="jl_grid5_wrapper">
                                                    <div class="jl_grid5_container">
                                                        <?php $i=0;?>
                                                        @foreach($slider_post as $slider_blog)
                                                        <?php $i++;?>
                                                        <div class="jl_grid5_item <?php echo ($i==1)?"jl_grid5main":"jl_grid5small";?> jl_grid{{$i}}">
                                                            <div class="jl_grid5_itemin">
                                                                <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/800/')}}/{{$slider_blog->blog_image}}')"></span>

                                                                            @if($slider_blog->content_type  == 'audio')
                                                                                <img src="{{url('upload/audio-image.png')}}" class="slider_child_img_5"/>
                                                                            @elseif($slider_blog->content_type  == 'video')
                                                                                <img src="{{url('upload/video-image.png')}}" class="slider_child_img_5"/>
                                                                            @endif

                                                                <a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" class="link_grid_header_absolute" title="{{$slider_blog->title}}" rel="blog"></a>
                                                                <span class="meta-category-small">
                                                                    @if($slider_blog->blog_category_data)
                                                                    <span class="meta-category-small"><a class="post-category-color-text" style="background: {{$slider_blog->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$slider_blog->blog_category_data->category->slug}}" rel="category">{{$slider_blog->blog_category_data->category->name}}</a></span>
                                                                    @endif
                                                                </span>
                                                                <div class="wrap_box_style_main image-post-title">
                                                                    <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" rel="blog"> <?php echo substr(strip_tags($slider_blog->title),0,40); if(strlen($slider_blog->title)>35){ echo "..."; } ?></a></h3>
                                                                    <span class="jl_post_meta">
                                                                        <span class="jl_author_img_w">
                                                                            @if($slider_blog->author)
                                                                            <img src="{{url('upload/author/original')}}/{{$slider_blog->author->image}}" width="30" height="30" alt="{{$slider_blog->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';" />

                                                                            
                                                                            <a title="{{$slider_blog->author->name}}" rel="author">{{$slider_blog->author->name}}</a>
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
                                            <span class="jl_none_space"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 grid-sidebar" id="content">
                        <div class="jl_wrapper_cat">
                            <?php $i=0;?>
                            @foreach($recent_middle_post as $recent_middle_blogs)
                                @if($i==0)
                                    <div class="jl_grid_mian loop-large-post">
                                        <div class="box jl_grid_layout1 blog_large_post_style appear_animation post-4761 post type-post status-publish format-standard has-post-thumbnail hentry category-sports">
                                            <div class="image-post-thumb">
                                                <a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" class="link_image featured-thumbnail" title="{{$recent_middle_blogs->title}}" rel="blog">
                                                    <img width="780" height="450" src="{{url('upload/blog/banner/800/')}}/{{$recent_middle_blogs->blog_image}}" class="attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image" alt="{{$recent_middle_blogs->title}}" onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';" style="height: auto;"/>
                                                    <div class="background_over_image">
                                                        @if($recent_middle_blogs->content_type  == 'audio')
                                                            <img src="{{url('upload/audio-image.png')}}" class="child_img_1"/>
                                                        @elseif($recent_middle_blogs->content_type  == 'video')
                                                            <img src="{{url('upload/video-image.png')}}" class="child_img_1"/>
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                            <span class="meta-category-small">
                                                @if($recent_middle_blogs->blog_category_data)
                                                <a class="post-category-color-text" style="background: {{$recent_middle_blogs->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$recent_middle_blogs->blog_category_data->category->slug}}" rel="category">{{$recent_middle_blogs->blog_category_data->category->name}}</a>
                                                @endif
                                            </span>
                                            <div class="jl_post_title_top jl_large_format">
                                                <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" rel="blog"> <?php echo substr(strip_tags($recent_middle_blogs->title),0,65); if(strlen($recent_middle_blogs->title)>64){ echo "..."; } ?></a></h3>
                                                <span class="single-post-meta-wrapper">
                                                    <span class="post-author">
                                                        <span>
                                                            @if($recent_middle_blogs->author)
                                                            <img src="{{url('upload/author/original')}}/{{$recent_middle_blogs->author->image}}" width="30" height="30" alt="{{url('upload/blog/banner/360/')}}/{{$recent_middle_blogs->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"/>
                                                            <a title="{{$recent_middle_blogs->author->name}}" rel="author">{{$recent_middle_blogs->author->name}}</a>
                                                            @endif
                                                        </span>
                                                    </span>
                                                    <span class="post-date updated"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($recent_middle_blogs->schedule_date))}}</span>
                                                </span>
                                            </div>
                                            <div class="post-entry-content">
                                                <div class="post-entry-content-wrapper">
                                                    <div class="large_post_content">
                                                        <p>
                                                            <?php echo substr(strip_tags($recent_middle_blogs->description),0,350);?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="box jl_grid_layout1 blog_grid_post_style post-2970 post type-post status-publish format-gallery has-post-thumbnail hentry category-business tag-inspiration tag-morning tag-racing post_format-post-format-gallery" data-aos="fade-up">
                                        <div class="post_grid_content_wrapper">
                                            <div class="image-post-thumb">
                                                <a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" class="link_image featured-thumbnail" title="{{$recent_middle_blogs->title}}" rel="blog">
                                                    <img width="780" height="450" src="{{url('upload/blog/banner/800/')}}/{{$recent_middle_blogs->blog_image}}" class="blog_image_resize attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image" alt="{{$recent_middle_blogs->title}}" onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"/>
                                                    <div class="background_over_image">
                                                                    
                                                        @if($recent_middle_blogs->content_type  == 'audio')
                                                            <img src="{{url('upload/audio-image.png')}}" class="child_img_1"/>
                                                        @elseif($recent_middle_blogs->content_type  == 'video')
                                                            <img src="{{url('upload/video-image.png')}}" class="child_img_1"/>
                                                        @endif

                                                    </div>
                                                </a>
                                                <span class="meta-category-small">
                                                    @if($recent_middle_blogs->blog_category_data)
                                                    <a class="post-category-color-text" style="background: {{$recent_middle_blogs->blog_category_data->category->color??null}};" href="{{url('category-blog')}}?category={{$recent_middle_blogs->blog_category_data->category->slug??null}}" rel="category">{{$recent_middle_blogs->blog_category_data->category->name??null}}</a>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="post-entry-content">
                                                <div class="post-entry-content-wrapper">
                                                    <div class="large_post_content">
                                                        <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" rel="blog"> <?php echo substr(strip_tags($recent_middle_blogs->title),0,72); if(strlen($recent_middle_blogs->title)>71){ echo "..."; } ?></a></h3>
                                                        <span class="jl_post_meta">
                                                            <span class="jl_author_img_w">
                                                                @if($recent_middle_blogs->author)
                                                                <img src="{{url('upload/author/original')}}/{{$recent_middle_blogs->author->image}}" width="30" height="30" alt="{{$recent_middle_blogs->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';" />
                                                                <a title="{{$recent_middle_blogs->author->name}}" rel="author">{{$recent_middle_blogs->author->name}}</a>
                                                                @endif
                                                            </span>
                                                            <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($recent_middle_blogs->schedule_date))}}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                 @endif
                                <?php $i++;?>
                            @endforeach
                        </div>
                    </div>
                    <!-- start sidebar -->
                    @include('../site/layout/components/side_content')
                    <!-- end sidebar -->
                </div>
            </div>
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
