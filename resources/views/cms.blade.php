<!DOCTYPE html>
<html>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="generator" content="Mobirise v4.3.5, mobirise.com">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="assets/images/logo2.png" type="image/x-icon">
    <meta name="description" content="HTML Blog Template - Free Download">
    <title>{{setting('site_name')}}</title>
    <link rel="stylesheet" href="{{ asset('site/web/assets/mobirise-icons/mobirise-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('site/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('site/dropdown/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('site/theme/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('site/mobirise/css/mbr-additional.css') }}" type="text/css"> </head>
<body>
    <noscript>
        <iframe src="http://www.googletagmanager.com/ns.html?id=GTM-PFK425" height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <script>
    (function(w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src = '../../www.googletagmanager.com/gtm5445.html?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-PFK425');
    </script>

    <section class="menu cid-qv1frvgcz3" once="menu" id="menu1-7v" data-rv-view="8334">
        <nav class="navbar navbar-expand beta-menu navbar-dropdown align-items-center navbar-fixed-top navbar-toggleable-sm bg-color transparent">
            <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <div class="hamburger"> <span></span> <span></span> <span></span> <span></span> </div>
            </button>
            <div class="menu-logo">
                <div class="navbar-brand"> 
                    <span class="navbar-caption-wrap">
                        <a class="navbar-caption text-white display-4">
                            <img src="{{asset('site/logo.png')}}">
                        </a>
                    </span> 
                </div>
            </div>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav nav-dropdown" data-app-modern-menu="true"></ul>
            </div>
        </nav>
    </section>

        <?php 
        if(file_exists(public_path()."/upload/cms/original/".$data->image) && $data->image!='') { ?>
            <style>
                .newBg {
                    padding-top: 120px;
                    padding-bottom: 90px;
                    background-image: url('{{url('upload/cms/original').'/'.$data->image}}');
                }
            </style>
         <?php $class = 'newBg';
        }else{
            $class = 'cid-qvbjfGj2Ze';
        }
    ?>


    <section class="mbr-section content5 {{$class}} mbr-parallax-background" id="content5-7w" data-rv-view="8336">
        <div class="mbr-overlay" style="opacity: 0.6; background-color: rgb(35, 35, 35);"> </div>
        <div class="container">
            <div class="media-container-row">
                <div class="title col-12 col-md-8">
                    <h1 class="align-center mbr-bold mbr-white pb-3 mbr-fonts-style display-1">{{$data->page_title}}</h1>
            </div>
        </div>
    </section>

    <section class="mbr-section content4 {{$class}}" id="content4-7x" data-rv-view="8339">
        <div class="container">
            <div class="media-container-row">
                <div class="title col-12 col-md-8">
                    <h2 class="align-center pb-3 mbr-fonts-style display-5">{{$data->title}}</h2>
            </div>
        </div>
    </section>
    <section class="mbr-section article content1 cid-qvbjomyZfb" id="content1-7y" data-rv-view="8341">
        <div class="container">
            <div class="media-container-row">
                <div class="mbr-text col-12 col-md-8 mbr-fonts-style display-7">
                    <?php echo $data->description; ?>
                </div>
            </div>
        </div>
    </section>
    <section once="" class="cid-qvbpqoTMWJ" id="footer6-8d" data-rv-view="8370">
        <div class="container">
            <div class="media-container-row align-center mbr-white">
                <div class="col-12">
                    <p class="mbr-text mb-0 mbr-fonts-style display-7"> © Copyright {{date('Y')}} <a class="text-white">Blog</a> - All Rights Reserved </p>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ asset('site/web/assets/jquery/jquery.min.js')}}"></script>
    <script src="{{ asset('site/popper/popper.min.js')}}"></script>
    <script src="{{ asset('site/bootstrap/js/bootstrap.min.js')}}"></script>
    <script src="{{ asset('site/dropdown/js/script.min.js')}}"></script>
</body>
</html>