@extends('../site/layout/main') @section('content')
<div class="options_layout_wrapper jl_none_box_styles jl_border_radiuss">
    <div class="options_layout_container full_layout_enable_front">
        <!-- Start header -->
        @include('../site/layout/components/header_4')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')

        <div class="jl_home_section jl_head_car">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 jl_mid_main_3col">
                        <div class="jl_3col_wrapin">
                            <div id="pl-4609" class="panel-layout">
                                <div id="pg-4609-0" class="panel-grid panel-no-style">
                                    <div id="pgc-4609-0-0" class="panel-grid-cell">
                                        <div class="jelly_homepage_builder jl_car_home jl_nonav_margin">
                                            <div class="jl_wrapper_row">
                                                <div class="row jelly_loading_pro jelly_cus_h601 jl_fontsize22 jl_builder_3carousel car_style4 jl_remove_border jl_hide_arrow">
                                                    @foreach($slider_post as $slider_blog)
                                                    <div class="col-md-3">
                                                        <div class="jl_car_wrapper">
                                                            <div class="jl_car_img_front">
                                                                <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/800/')}}/{{$slider_blog->blog_image}}');"></span>
                                                                <a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" class="link_grid_header_absolute" title="{{$slider_blog->title}}" rel="blog"></a>
                                                                <span class="meta-category-small">
                                                                    @if($slider_blog->blog_category_data)
                                                                    <span class="meta-category-small"><a class="post-category-color-text" style="background: {{$slider_blog->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$slider_blog->blog_category_data->category->slug}}" rel="category">{{$slider_blog->blog_category_data->category->name}}</a></span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="post-entry-content">
                                                                <span class="meta-category-small">
                                                                    @if($slider_blog->blog_category_data)
                                                                    <span class="meta-category-small"><a class="post-category-color-text" style="background: {{$slider_blog->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$slider_blog->blog_category_data->category->slug}}" rel="category">{{$slider_blog->blog_category_data->category->name}}</a></span>
                                                                    @endif
                                                                </span>
                                                                <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" rel="blog"> {{$slider_blog->title}}</a></h3>
                                                                <span class="jl_post_meta">
                                                                    <span class="jl_author_img_w">
                                                                        @if($slider_blog->author)
                                                                        <img src="{{url('upload/author/original')}}/{{$slider_blog->author->image}}" width="30" height="30" alt="{{$slider_blog->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"/>

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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="jl_home_section jl_home_slider">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-8 grid-sidebar" id="content">
                                <!-- start grid sidebar -->
                                <div class="jelly_homepage_builder jl_nonav_margin homepage_builder_3grid_post jl_fontsize22 jl_cus_grid2 colstyle1">
                                    <div class="jl_wrapper_row jl-post-block-882504">
                                        <div class="row">
                                            @foreach($recent_middle_post as $recent_middle_blogs)
                                            <div class="box blog_grid_post_style jl_row_1">
                                                <div class="jl_grid_box_wrapper">
                                                    <div class="image-post-thumb">
                                                        <a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" class="link_image featured-thumbnail" title="{{$recent_middle_blogs->title}}" rel="blog">
                                                            <img width="780" height="450" src="{{url('upload/blog/banner/360/')}}/{{$recent_middle_blogs->blog_image}}" class="blog_image_resize attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image" alt="{{$recent_middle_blogs->title}}" onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';" />
                                                            <div class="background_over_image">
                                                                    
                                                                @if($recent_middle_blogs->content_type  == 'audio')
                                                                    <img src="{{url('upload/audio-image.png')}}" class="child_img_4"/>
                                                                @elseif($recent_middle_blogs->content_type  == 'video')
                                                                    <img src="{{url('upload/video-image.png')}}" class="child_img_4"/>
                                                                @endif

                                                            </div>
                                                        </a>
                                                        <span class="meta-category-small">
                                                            @if($recent_middle_blogs->blog_category_data)
                                                            <a class="post-category-color-text" style="background: {{$recent_middle_blogs->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$recent_middle_blogs->blog_category_data->category->slug}}" rel="category">{{$recent_middle_blogs->blog_category_data->category->name}}</a>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="post-entry-content">
                                                        <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" rel="blog"> <?php echo substr(strip_tags($recent_middle_blogs->title),0,65); if(strlen($recent_middle_blogs->title)>64){ echo "..."; } ?></a></h3>
                                                        <span class="jl_post_meta">
                                                            <span class="jl_author_img_w">
                                                                @if($recent_middle_blogs->author)
                                                                <img src="{{url('upload/author/original')}}/{{$recent_middle_blogs->author->image}}" width="30" height="30" alt="{{$recent_middle_blogs->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';" />
                                                                <a title="{{$recent_middle_blogs->author->name}}" rel="author">{{$recent_middle_blogs->author->name}}</a>
                                                                @endif
                                                            </span>
                                                            <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($recent_middle_blogs->schedule_date))}}</span>
                                                        </span>
                                                        <div class="content_post_grid">
                                                            <p><?php echo substr(strip_tags($recent_middle_blogs->description),0,150);?>..</p>
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

            </div>
            <!-- end content -->
            <!-- Start footer -->
            @include('../site/layout/components/footer')
            <!-- End footer -->
        </div>
    </div>
</div>
<div id="go-top">
    <a href="#go-top"><i class="fa fa-angle-up"></i></a>
</div>
@endsection
