@extends('../site/layout/main') @section('content')
<link rel="stylesheet" href="{{ asset('site/css/main_new.css')}}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ asset('site/css/shop_new.css')}}" type="text/css" media="all" />
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform" >
    <div class="options_layout_container full_layout_enable_front no_transform" >
        <!-- Start header -->@include('../site/layout/components/header_1')
        <!-- end header -->@include('../site/layout/components/overlay-menu')
        <!-- begin content -->
        
        <!--<div class="main_title_wrapper category_title_section jl_cat_img_bg">
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
        </div>-->
        
        </br></br></br></br>&nbsp;&nbsp;
         <div class="main_title_wrapper category_title_section jl_cat_img_bg">
             
             <div class="category_image_bg_image" style="background-image: url('{{ asset('upload/news-baner.jpg') }}');"></div>
             <!--
            <div class="category_image_bg_image" style="background-image: url('{{ asset('upload/newsban.jpg') }}');"></div>
             <div class="category_image_bg_image" style="height:500px;background-image: url('http://news.jaipurtesthouse.com/upload/news-banner.jpg');"></div>-->
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


        <div class="jl_post_loop_wrapper no_transform">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 grid-sidebar">
                        <h4>Add Your Story </h4> <br><br>
                        
                        @if(session('success'))
                            <div class="alert alert-success mt-3">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        <div style="text-align:left;">
                            <form action="{{ route('add_my_stories') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="inputAddress"  style="font-size: 70px;">Name</label>
                                    <input type="text" name="name" class="form-control" id="inputAddress" placeholder="Enter name" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" class="form-control" id="" placeholder="name@example.com" required>
                                </div>
                                <div class="form-group">
                                    <label for="inputAddress2">Phone</label>
                                    <input type="text" name="phone" class="form-control" id="inputAddress2" placeholder="Enter phone" required>
                                </div>                     
                                <div class="form-group">
                                    <label for="exampleFormControlTextarea1">Story</label>
                                    <textarea class="form-control" name="story" id="exampleFormControlTextarea1" rows="3" required></textarea>
                                </div>  
                                <div class="form-group">
                                    <label for="exampleFormControlTextarea1">Add Image/Video/Document</label>
                                    <input class="form-control" type="file" name="file">
                                </div>                        
                                <button type="submit" style="background-color: black; color: white; font-size: 13px; width: 110px; height:50px; border-radius: 10px;">Submit</button>                             
                            </form>
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

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>   
   jQuery.noConflict();
    jQuery(document).ready(function($) {
        
        setTimeout(function(){
            $('.alert').fadeOut('slow');
        }, 10000);
    });

</script>