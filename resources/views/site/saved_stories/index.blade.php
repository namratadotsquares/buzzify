@extends('../site/layout/main') @section('content')
<link rel="stylesheet" href="{{ asset('site/css/main_new.css')}}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ asset('site/css/shop_new.css')}}" type="text/css" media="all" />
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform" >
    <div class="options_layout_container full_layout_enable_front no_transform" >
        <!-- Start header -->@include('../site/layout/components/header_1')
        <!-- end header -->@include('../site/layout/components/overlay-menu')
        <!-- begin content -->
        <div class="main_title_wrapper category_title_section jl_cat_img_bg">
            <div class="category_image_bg_image" style="background-image: url('img/1920x982.png');"></div>
            <div class="category_image_bg_ov"></div>
            <div class="jl_cat_title_wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 main_title_col">
                            <div class="jl_cat_mid_title">
                                <h3 class="categories-title title">{{ __('frontend.my_saved_stories') }}</h3>
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
                                @foreach($data as $saved_blogs)
                                <div
                                    class="box jl_grid_layout1 blog_grid_post_style post-2968 post type-post status-publish format-audio has-post-thumbnail hentry category-gaming tag-inspiration tag-relaxing tag-shooting post_format-post-format-audio aos-init aos-animate"
                                    data-aos="fade-up"
                                >
                                    <div class="post_grid_content_wrapper">
                                        <div class="image-post-thumb">
                                            <a href="{{url('/blog-details')}}/{{$saved_blogs->blog->slug}}" class="link_image featured-thumbnail" title="{{$saved_blogs->blog->title}}">
                                                <img
                                                    width="780"
                                                    height="450"
                                                    src="{{url('upload/blog/banner/original/')}}/{{ $saved_blogs->blog->blog_image }}"
                                                    class="attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image"
                                                    alt="{{$saved_blogs->title}}"
                                                    onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                                />
                                                <div class="background_over_image"></div>
                                            </a>
                                            <div class="background_over_image"></div>
                                            <span class="meta-category-small">
                                                @if($saved_blogs->blog->category)
                                                <a class="post-category-color-text" style="background: {{$saved_blogs->blog->category['color']}};" href="{{url('category-blog')}}?category={{$saved_blogs->blog->category['slug']}}">
                                                    {{$saved_blogs->blog->category['name']}}
                                                </a>
                                                @endif
                                            </span>
                                            <a title="{{ __('frontend.delete') }}" onclick="deletebtnModal('id01_{{$saved_blogs->id}}',true)" class="cursor_pointer">
                                                <span class="jl_post_type_icon"><i class="la la-trash"></i></span>
                                            </a>
                                        </div>
                                        <div class="post-entry-content">
                                            <div class="post-entry-content-wrapper">
                                                <div class="large_post_content">
                                                    <h3 class="image-post-title">
                                                        <a href="{{url('blog-detail')}}/{{$saved_blogs->blog->slug}}"> {{$saved_blogs->blog->title}}</a>
                                                    </h3>
                                                    <span class="jl_post_meta">
                                                        <span class="jl_author_img_w">
                                                            @if($saved_blogs->blog->author)
                                                            <img
                                                                src="{{url('upload/author/original')}}/{{$saved_blogs->blog->author->image}}"
                                                                width="30"
                                                                height="30"
                                                                alt="{{$saved_blogs->blog->author->name}}"
                                                                class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                            />
                                                            <a title="{{$saved_blogs->blog->author->name}}" rel="author" onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';">
                                                                {{$saved_blogs->blog->author->name}}
                                                            </a>
                                                            @endif
                                                        </span>
                                                        <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($saved_blogs->blog->schedule_date))}}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="id01_{{$saved_blogs->id}}" class="modal">
                                    <span onclick="deletebtnModal('id01_{{$saved_blogs->id}}',false);" class="close">×</span>
                                    <form class="modal-content" action="/action_page.php">
                                        <div class="container">
                                            <h1>{{ __('frontend.delete_saved_story') }}</h1>
                                            <p>{{ __('frontend.delete_story_confirmation') }}</p>
                                            <div class="clearfix">
                                                <button type="button" onclick="deletebtnModal('id01_{{$saved_blogs->id}}',false);" class="cancelbtn">{{ __('frontend.cancel') }}</button>
                                                <a href="{{url('delete-story')}}/{{$saved_blogs->id}}" class="deletebtn deletebutton">{{ __('frontend.delete') }}</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @endforeach

                            </div>
                            @if(count($data)==0)
                            	<div class="pagination_infinite_style_cat text-center required">
                            		{{ __('frontend.no_story_saved') }}
                            	</div>
                            @endif
                            <nav class="jellywp_pagination"></nav>
                        </div>
                    </div>
                    <!-- start sidebar -->@include('../site/layout/components/side_content')
                    <!-- end sidebar -->
                </div>
            </div>
        </div>
        <!-- end content -->
        <!-- Start footer -->@include('../site/layout/components/footer')
        <!-- End footer -->
    </div>
</div>
<div id="go-top">
    <a href="#go-top"><i class="fa fa-angle-up"></i></a>
</div>
@endsection