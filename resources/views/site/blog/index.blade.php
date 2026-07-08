@extends('../site/layout/main') @section('content')
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform">
    <div class="options_layout_container full_layout_enable_front no_transform">
        <!-- Start header -->
        @include('../site/layout/components/header_1')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')

        <div class="main_title_wrapper category_title_section jl_cat_img_bg">
            <div class="category_image_bg_image" style="background-image: url('{{url('upload/category/original/')}}/{{$category_blogs->image}}');"></div>
            <div class="category_image_bg_ov"></div>
            <div class="jl_cat_title_wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 main_title_col">
                            <div class="jl_cat_mid_title">
                                <h3 class="categories-title title">{{$category_blogs->name}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="jl_post_loop_wrapper no_transform">
            <div class="container no_transform" id="wrapper_masonry">
                <div class="row no_transform">
                    <div class="col-md-8 grid-sidebar" id="content">
                        <div class="jl_wrapper_cat">
                            <div id="content_masonry" class="pagination_infinite_style_cat">
                                @foreach($categoryBlog as $cat_post)
                                <div
                                    class="box jl_grid_layout1 blog_grid_post_style post-2970 post type-post status-publish format-gallery has-post-thumbnail hentry category-business tag-inspiration tag-morning tag-racing post_format-post-format-gallery aos-init aos-animate"
                                    data-aos="fade-up"
                                >
                                    <div class="post_grid_content_wrapper header_banner">
                                        <div class="image-post-thumb">
                                            <a href="{{url('/blog-details')}}/{{$cat_post->slug}}" class="link_image featured-thumbnail" title="{{$cat_post->title}}">
                                                <img
                                                    width="780"
                                                    height="450"
                                                    src="{{url('upload/blog/banner/360/')}}/{{ $cat_post->blog_image }}"
                                                    class="attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image fishes"
                                                    alt="{{$cat_post->title}}"
                                                    onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                                />

                                                <div class="background_over_image">
                                                    @if($cat_post->content_type == 'audio')
                                                    <img src="{{url('upload/audio-image.png')}}" class="child_img_1" />
                                                    @elseif($cat_post->content_type == 'video')
                                                    <img src="{{url('upload/video-image.png')}}" class="child_img_1" />
                                                    @endif
                                                </div>
                                            </a>
                                            <span class="meta-category-small">
                                                @if($cat_post->blog_category_data)
                                                <a class="post-category-color-text" style="background: {{$cat_post->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$cat_post->blog_category_data->category->slug}}">
                                                    {{$cat_post->blog_category_data->category->name}}
                                                </a>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="post-entry-content">
                                            <div class="post-entry-content-wrapper">
                                                <div class="large_post_content">
                                                    <h3 class="image-post-title"><a href="{{url('blog-details')}}/{{$cat_post->slug}}/"> {{$cat_post->title}}</a></h3>
                                                    <span class="jl_post_meta">
                                                        <span class="jl_author_img_w">
                                                            @if($cat_post->author)
                                                            <img
                                                                src="{{url('upload/author/original')}}/{{$cat_post->author->image}}"
                                                                width="30"
                                                                height="30"
                                                                alt="{{$cat_post->author->name}}"
                                                                class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                            />
                                                            <a title="{{$cat_post->author->name}}" rel="author" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';">{{$cat_post->author->name}}</a>
                                                            @endif
                                                        </span>
                                                        <span class="post-date m-r-10"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($cat_post->schedule_date))}}</span>
                                                        <span class="post-date" style="margin-left: 20px;"><i class="fa fa-eye"></i>{{$cat_post->viewcount}}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <nav class="jellywp_pagination">
                                {!! $categoryBlog->appends(request()->except('page'))->render() !!}
                            </nav>
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