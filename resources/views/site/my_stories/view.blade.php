@extends('../site/layout/main') @section('content')
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform">
    <div class="options_layout_container full_layout_enable_front no_transform">
        <!-- Start header -->
        @include('../site/layout/components/header_1')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')

        <!-- begin content -->
        <div class="main_title_wrapper category_title_section jl_cat_img_bg">
            <div class="category_image_bg_image" style="background-image: url('img/1920x982.png');"></div>
            <div class="category_image_bg_ov"></div>
            <div class="jl_cat_title_wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 main_title_col">
                            <div class="jl_cat_mid_title">
                                <h3 class="categories-title title">{{ __('frontend.my_stories') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section id="content_main" class="clearfix jl_spost no_transform">
            <div class="container no_transform">
                <div class="row main_content no_transform">
                    <div class="col-md-8 loop-large-post" id="content">                        
                        <!-- <iframe width="420" height="345" src=""></iframe>
                        <audio controls class="width-100" style="width: 100%;"><source src="" type="audio/mp3" /></audio>
                         -->
                        <audio controls id="audio-123456" class="hide"></audio>

                        <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>

                        <div class="widget_container content_page">
                            <!-- start post -->
                            <div class="post-2961 post type-post status-publish format-standard has-post-thumbnail hentry category-sports tag-inspire tag-relaxing tag-shooting" id="post-2961">
                                <div class="single_section_content box blog_large_post_style">
                                    <div class="post_content" style="margin-top:90px;">

                                    <?php
                                            $file = $stories->file;
                                            $fileType = pathinfo($file, PATHINFO_EXTENSION);
                                            if (in_array($fileType, ['mp4', 'webm', 'ogg'])) {
                                                echo '<video width="720" height="240" controls>';
                                                echo '<source src="' . $file . '" type="video/' . $fileType . '">';
                                                echo '</video>';
                                            } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                echo '<img src="' . $file . '" width="720" height="240" alt="Image">';
                                            } else {
                                                echo '<a href="' . $file . '" target="_blank">View Document</a>';
                                            }
                                        ?>
                                        
                                    &nbsp;&nbsp;  <p><?php echo $stories->story; ?></p>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <!-- end post -->
                            <div class="brack_space"></div>
                        </div>
                    </div>
                    <!-- start sidebar -->
                    @include('../site/layout/components/side_content')
                    <!-- end sidebar -->
                </div>
            </div>
        </section>
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