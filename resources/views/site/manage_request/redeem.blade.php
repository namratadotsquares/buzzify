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
                                <h3 class="categories-title title">{{ __('frontend.redeem_request') }}</h3>
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
                                <h3 class="categories-title title">{{ __('frontend.redeem_request') }}</h3>
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
                        <br><br>
                        <h4>Redeem Requests</h4> <br>

                        @if(session('success'))
                            <div class="alert alert-success mt-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger mt-3">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif

                        <table class="table table-fluid" id="myTable">
                            <thead class="bg-light">
                                <tr style="text-align:left;">
                                    <th>S.No.</th>
                                    <th>Image</th>
                                    <th>Voucher Name</th>
                                    <th>Redeemed</th>
                                    <th>Points</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($redeemReq))
                                    <?php $i=1;  ?>
                                    @foreach($redeemReq as $m)
                                        <tr style="text-align:left;">
                                            <td>{{$i}} </td>
                                            <?php
                                                $prouctId = $m->id;
                                                if(file_exists(public_path()."/upload/e-paper/original/".$m->img) && $m->img!='') {
                                                    $url = url('upload/e-paper/original').'/'.$m->img;
                                                }else{
                                                    $url = url('upload/no-image.png');
                                                }
                                            ?>
                                            <td>
                                                <img src="{{$url}}" alt="" style="width:45px;height:45px" class="rounded-circle"/>
                                            </td>
                                            <td>{{$m->name}}</td>
                                            <td>
                                                @if($m->redeem==0)
                                                    <span class="meta-category-small">
                                                        <a class="btn btn-custom" href="" style="text-decoration:none;width:80px;height:25px;padding-top:8px;">One Time</a>
                                                    </span>
                                                @else
                                                    <span class="meta-category-small">
                                                        <a class="btn btn-custom" href="" style="text-decoration:none;width:80px;height:25px;padding-top:8px;">Many Time</a>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{$m->point}}</td>
                                            <td>{{ \Carbon\Carbon::parse($m->created_at)->format('d-m-Y') }}</td>
                                            <td>
                                                <span class="meta-category-small">
                                                    <a class="btn btn-custom" href="{{ route('redemm_points', ['product_id' => $prouctId]) }}" style="text-decoration:none; background-color: #ba4df5; color: white; width:80px;height:25px;padding-top:8px;">Redeem</a>
                                                </span>
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

                    @include('../site/layout/components/side_content')
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
