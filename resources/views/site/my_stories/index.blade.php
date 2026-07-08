@extends('../site/layout/main') @section('content')
<link rel="stylesheet" href="{{ asset('site/css/main_new.css')}}" type="text/css" media="all" />
<link rel="stylesheet" href="{{ asset('site/css/shop_new.css')}}" type="text/css" media="all" />

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
    #myTable th {
        font-size: 13px; 
    }
</style>

<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform" >
    <div class="options_layout_container full_layout_enable_front no_transform" >
        @include('../site/layout/components/header_1')
        @include('../site/layout/components/overlay-menu')
        
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
        
        <div class="main_title_wrapper category_title_section jl_cat_img_bg">
             <div class="category_image_bg_image" style="background-image: url('{{ asset('upload/newsban.jpg') }}');"></div>
             <!--
             <div class="category_image_bg_image" style="background-image: url('{{ asset('upload/news-baner.jpg') }}');"></div>
            
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
                        <br><br><div style="text-align:left">                            
                            <a class="btn btn-custom" href="{{ route('add_my_stories') }}" style="text-decoration:none; background-color: black; color: white;">Add Story</a>
                        </div> <br><br>   
                        <!-- <table class="table align-middle mb-0 bg-white"> -->
                        <table class="table table-fluid" id="myTable">
                            <thead class="bg-light">
                                <tr  style="text-align:left;">
                                <th>S. No.</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Story</th>
                                <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($stories))
                                <?php $i=1;  ?>   
                                    @foreach($stories as $s)
                                        <tr style="text-align:left;">
                                            <td>{{$i}} </td>                                    
                                            <td> 
                                                <?php
                                                   $storyId = $s->id;
                                                    $file = $s->file;
                                                    $fileType = pathinfo($file, PATHINFO_EXTENSION);
                                                    if($fileType) {
                                                            if (in_array($fileType, ['mp4', 'webm', 'ogg'])) {
                                                                echo '<video width="130" height="80" controls>';
                                                                echo '<source src="' . $file . '" type="video/' . $fileType . '">';
                                                                echo '</video>';
                                                            } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                                echo '<img src="' . $file . '" width="130" height="80" alt="Image">';
                                                            } else {
                                                                echo '<a href="' . $file . '" target="_blank">View Document</a>';
                                                            }
                                                    }else{
                                                        echo "No any media images, videos, and document found!";
                                                    }
                                                ?> 
                                            </td>
                                            <td>{{$s->name}}</td>
                                            <td>{{$s->phone}}</td>
                                            <td> {!! strip_tags( \Illuminate\Support\Str::words($s->story, 5,'...')) !!}</td>
                                            <td>
                                               
                                                <a class="btn btn-custom" href="{{ route('view_my_stories', ['id' => $storyId]) }}" style="text-decoration:none; background-color: #ba4df5; color: white; width:80px;height:27px;padding-top:6px;font-size:11px;">View Story</a>


                                            </td>                                   
                                        </tr> 
                                    <?php $i++; ?>
                                    @endforeach
                                @else
                                <tr class="intro-x text-center text-danger">
                                    <td class="w-40" colspan="7">
                                       No Record Found
                                    </td>
                                </tr>
                                @endif    
                            </tbody>
                            
                        </table>
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
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>


<script>   
   jQuery.noConflict();
    jQuery(document).ready(function($) {
        $('#myTable').DataTable({
            //"pagingType": "simple",
            "pagingType": "simple_numbers",            
            //"pagingType": "full_numbers",            
            "searching": false,
            //"lengthMenu": [2, 10, 25, 50],  
            "lengthChange": false,   
             "info": false,
        });

        setTimeout(function(){
            $('.alert').fadeOut('slow');
        }, 5000);
    });

</script>