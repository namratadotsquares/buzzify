@extends('../site/layout/main') @section('content')
<link rel="stylesheet" href="{{ asset('site/css/slider_for_home_3.css')}}" type="text/css" media="all" />
<div class="options_layout_wrapper jl_none_box_styles jl_border_radiuss">
    <div class="options_layout_container full_layout_enable_front">
        <!-- Start header -->
        @include('../site/layout/components/header_3')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')

        <div class="jl_home_section jl_car_h5">
            <!-- start carousel -->
            <div class="jelly_homepage_builder jl_car_home jl_nonav_margin">
                <div class="jl_wrapper_row" style="padding-right: 1px;
    padding-left: 1px;">
                    <div class="row jelly_loading_pro jelly_cus_h563 jl_fontsize22 jl_builder_4carousel car_style4 jl_remove_border jl_hide_author jl_hide_author_img jl_hide_date jl_hide_arrow jl_hide_dots">
                        @foreach($slider_post as $slider_blog)
                        <div class="col-md-3">
                            <div class="jl_car_wrapper">
                                <div class="jl_car_img_front">
                                    <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/360/')}}/{{$slider_blog->blog_image}}');"></span>
                                    <a href="{{url('/blog-details')}}/{{$slider_blog->slug}}" class="link_grid_header_absolute" title="{{$slider_blog->title}}" rel="blog"></a>
                                    @if($slider_blog->category)
                                    <span class="meta-category-small"><a class="post-category-color-text" style="background: {{$slider_blog->category['color']}};" href="{{url('category-blog')}}?category={{$slider_blog->category['slug']}}" rel="category">{{$slider_blog->category['name']}}</a></span>
                                    @endif
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
                                            <img src="{{url('upload/author/original')}}/{{$slider_blog->author->image}}" width="30" height="30" alt="{{$slider_blog->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';" />
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
            <!-- end carousel -->
        </div>

        <div class="jl_home_section">
            <div class="container">
                <div class="row">
                    <div class="col-md-8" id="content">
                        <!-- start main grid -->
                        <div class="jl_nonav_margin jelly_homepage_builder jl_large_grid jl-post-block-100728">
                            <div class="row jl_lg_row">
                                <div class="homepage_builder_title">
                                    <div class="col-md-12">
                                        <h2 class="builder_title_home_page">
                                            {{ __('frontend.recent_posts') }}
                                        </h2>
                                        <span class="jl_hsubt">This is sample subtitle blog post section</span>
                                    </div>
                                </div>
                                <?php $i=0;?>
                                @foreach($recent_middle_post as $recent_middle_blogs)
                                    @if($i==0)
                                        <div class="col-md-12">
                                            <div class="jl_large_builder jelly_homepage_builder">
                                                <div class="box jl_grid_layout1 blog_large_post_style">
                                                    <div class="jl_front_l_w">
                                                        <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/800/')}}/{{$recent_middle_blogs->blog_image}}');"></span>

                                                        <div class="background_over_image">
                                                                @if($recent_middle_blogs->content_type  == 'audio')
                                                                <img src="{{url('upload/audio-image.png')}}" class="child_img_3"/>
                                                              @elseif($recent_middle_blogs->content_type  == 'video')
                                                                <img src="{{url('upload/video-image.png')}}" class="child_img_3"/>
                                                              @endif
                                                        </div>


                                                        <a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" class="link_grid_header_absolute" title="{{$recent_middle_blogs->title}}" rel="blog"></a>
                                                        <span class="meta-category-small">
                                                            @if($recent_middle_blogs->blog_category_data)
                                                            <a class="post-category-color-text" style="background: {{$recent_middle_blogs->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$recent_middle_blogs->blog_category_data->category->slug}}" rel="category">{{$recent_middle_blogs->blog_category_data->category->name}}</a>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="jl_post_title_top jl_large_format">
                                                        <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" rel="blog"><?php echo substr(strip_tags($recent_middle_blogs->title),0,40); if(strlen($recent_middle_blogs->title)>35){ echo "..."; } ?> </a></h3>
                                                        <span class="single-post-meta-wrapper">
                                                            <span class="post-author">
                                                                <span>
                                                                    @if($recent_middle_blogs->author)
                                                                    <img src="{{url('upload/author/original')}}/{{$recent_middle_blogs->author->image}}" width="30" height="30" alt="{{$recent_middle_blogs->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';" />

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
                                                                    <?php echo substr(strip_tags($recent_middle_blogs->description),0,350);?>...
                                                                </p>
                                                                <div class="jl_large_sw">
                                                                    <a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" class="jl_large_more" rel="blog">{{ __('frontend.read_more') }}</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="jelly_homepage_builder homepage_builder_3grid_post jl_cus_grid2 jl_fontsize22 colstyle{{$i}}">
                                            <div class="col-md-4 blog_grid_post_style jl_row_2">
                                                <div class="jl_grid_box_wrapper">
                                                    <div class="image-post-thumb">
                                                        <a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" class="link_image featured-thumbnail" title="{{$recent_middle_blogs->title}}" rel="blog">
                                                            <img width="780" height="450" src="{{url('upload/blog/banner/360/')}}/{{$recent_middle_blogs->blog_image}}" class="attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image" alt="{{$recent_middle_blogs->title}}" onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"/>
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
                                                            <a class="post-category-color-text" style="background: {{$recent_middle_blogs->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$recent_middle_blogs->blog_category_data->category->slug}}" rel="category">{{$recent_middle_blogs->blog_category_data->category->name}}</a>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="post-entry-content">
                                                        <h3 class="image-post-title"><a href="{{url('/blog-details')}}/{{$recent_middle_blogs->slug}}" rel="blog"> <?php echo substr(strip_tags($recent_middle_blogs->title),0,65); if(strlen($recent_middle_blogs->title)>64){ echo "..."; }?></a></h3>
                                                        <span class="jl_post_meta">
                                                            <span class="jl_author_img_w">
                                                                @if($recent_middle_blogs->author)
                                                                <img src="{{url('upload/author/original')}}/{{$recent_middle_blogs->author->image}}" width="30" height="30" alt="{{$recent_middle_blogs->author->name}}" class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"/>
                                                                <a title="{{$recent_middle_blogs->author->name}}" rel="author">{{$recent_middle_blogs->author->name}}</a>
                                                                @endif
                                                            </span>
                                                            <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($recent_middle_blogs->schedule_date))}}</span>
                                                        </span>
                                                        <div class="content_post_grid">
                                                            <p><?php echo substr(strip_tags($recent_middle_blogs->description),0,80);?>..</p>
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
                        <!-- end main grid -->
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
<div id="go-top">
    <a href="#go-top"><i class="fa fa-angle-up"></i></a>
</div>
@endsection
