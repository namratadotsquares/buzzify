<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Title-->
                @if(isset($blog_detail))
                        <title>{{$blog_detail->seo_title}}</title>
                        <meta name="author" content="{{$blog_detail->seo_tag}}">
                        <meta name="description" content="{{$blog_detail->seo_description}}">
                        <meta name="keywords" content="{{$blog_detail->seo_keyword}}">
                        <meta property="og:title" content="{{ $blog_detail->seo_title ?? $blog_detail->title }}" />
                        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($blog_detail->description),200) }}" />
                        <meta property="og:type" content="article" />
                        <meta property="og:site_name" content="{{ setting('site_seo_title') }}" />
                        <meta property="og:url" content="{{ url('/blog-details/'.$blog_detail->slug) }}" />
                        <meta property="og:image" content="{{ url('upload/blog/banner/original/'. $blog_detail->blog_image) }}" />
                        <meta property="og:image:secure_url" content="{{ (config('app.env') === 'production') ? secure_asset('upload/blog/banner/original/'. $blog_detail->blog_image) : url('upload/blog/banner/original/'. $blog_detail->blog_image) }}" />
                        <meta property="og:image:width" content="1200">
                        <meta property="og:image:height" content="630">
                        <meta property="og:image:alt" content="{{ $blog_detail->title }}">
        @elseif(isset($content))
            <title>{{$content->title}}</title>
            <meta name="author" content="{{$content->title}}">
            <meta name="description" content="{{$content->meta_desc}}">
            <meta name="keywords" content="{{$content->meta_char}}">
        @else
            <title>{{setting('site_seo_title')}}</title>
            <meta name="author" content="{{setting('site_seo_title')}}">
            <meta name="description" content="{{setting('site_seo_description')}}">
            <meta name="keywords" content="{{setting('site_seo_keyword')}}">
        @endif
        
        @yield('head')
      	<link rel="stylesheet" href="{{ asset('site/css/image_new_design.css')}}" type="text/css" media="all" />
        <link rel="stylesheet" href="{{ asset('site/css/bootstrap.css')}}" type="text/css" media="all" />
        <link rel="stylesheet" href="{{ asset('site/css/style.css')}}" type="text/css" media="all" />
        <link rel="stylesheet" href="{{ asset('site/css/responsive.css')}}" type="text/css" media="all" />
        @if(setting('homepage_theme')=='home_2')
        <link rel="stylesheet" href="{{ asset('site/css/demo4.css')}}" type="text/css" media="all" />
        @elseif(setting('homepage_theme')=='home_3')
        <link rel="stylesheet" href="{{ asset('site/css/demo5.css')}}" type="text/css" media="all" />
        @elseif(setting('homepage_theme')=='home_4')
        <link rel="stylesheet" href="{{ asset('site/css/demo6.css')}}" type="text/css" media="all" />
        @else
        <link rel="stylesheet" href="{{ asset('site/css/main.css')}}?v='.date('YmdHis').'')}}" type="text/css" media="all" />
        @endif
        <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
        <script>
        var base_url = "<?php echo url(''); ?>";
        </script>
        <script>
function redirectToStore() {
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;

    const playStore = "https://play.google.com/store/apps/details?id=com.buzzify";
    const appStore  = "https://apps.apple.com/app/buzzify/id6478440886";

    const isIOS = /iPad|iPhone|iPod/.test(userAgent) ||
                  (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    const isAndroid = /Android/i.test(userAgent);
    if (isIOS) {
        window.location.href = appStore;
    } else if (isAndroid) {
        window.location.href = playStore;
    }else{

    }
}
// Run only on blog-details page
if (window.location.pathname.includes('blog-details')) {
  setTimeout(function () {
    redirectToStore();
}, 2000);
}
</script>
        <!-- Its is required-inline CSS to put here because following css are making dynamic from the admin setting -->
        <style>
        h1{
            font-size: {{setting('h_1_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        h2{
            font-size: {{setting('h_2_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        h3{
            font-size: {{setting('h_3_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        h4{
            font-size: {{setting('h_4_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }
        p{
            font-size: {{setting('p_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        span{
            font-size: {{setting('span_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        label{
            font-size: {{setting('lable_size')}}px!important;
            font-family:{{setting('font_family')}}!important;
        }

        body{
            font-family:{{setting('font_family')}}!important;
            
        }
        
        /*{
            font-family:{{setting('font_family')}}!important;
        }*/
        </style>
       <!-- end head -->
        <link href="{{url('upload/favicon/'.setting('site_favicon'))}}" rel="shortcut icon">
        <meta name="csrf-token" content="{{ csrf_token() }}">
       <?php echo setting('google_analytics_code'); ?>
    </head>
@yield('body')
</html>