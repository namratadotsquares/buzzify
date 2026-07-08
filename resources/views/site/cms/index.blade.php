@extends('../site/layout/main') @section('content')
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform">
    <div class="options_layout_container full_layout_enable_front no_transform">
        @include('../site/layout/components/header_1') @include('../site/layout/components/overlay-menu')
        <div class="jl_single_style3">
            <div class="single_content_header single_captions_overlay_image_full_width">
                <div class="image-post-thumb" style="background-image: url('{{$content->image}}')"></div>
                <div class="single_post_entry_content">
                    <h1 class="single_post_title_main">
                        {{$content->title}}
                    </h1>
                </div>
            </div>
        </div>
        <section id="content_main" class="clearfix jl_spost no_transform">
            <div class="container no_transform">
                <div class="row main_content justify_center">
                    <div class="col-md-12 loop-large-post">
                        <div class="widget_container content_page">
                            <div class="post-2965 post type-post status-publish format-quote has-post-thumbnail hentry category-active tag-gaming tag-inspiration tag-racing post_format-post-format-quote" id="post-2965">
                                <div class="single_section_content box blog_large_post_style">
                                    <div class="post_content">
                                        <p><?php echo $content->description; ?></p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="brack_space"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('../site/layout/components/footer')
    </div>
</div>
<div id="go-top">
    <a href="#go-top"><i class="fa fa-angle-up"></i></a>
</div>
@endsection