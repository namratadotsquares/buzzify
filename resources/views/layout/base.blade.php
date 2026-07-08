<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $theme }}">
    <head>
        <meta charset="utf-8" />
        <link href="{{url('upload/favicon/'.setting('site_favicon'))}}" rel="shortcut icon" />
        <meta name="theme-color" content="#ffffff" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="" />
        <meta name="keywords" content="" />
        <script>
            var base_url = "<?php echo url(''); ?>";
        </script>
        <!-- <style>
            .pagination{
                display:block !important;
            }
        </style> -->
        <style type="text/css">
            .required{
                color: red;
            }
        </style>
        <link rel="stylesheet" href="{{ asset('plugin/toastr/toastr.min.css')}}" rel="stylesheet" type="text/css" />
        @yield('head')
        <link rel="stylesheet" href="{{ asset('dist/css/app.css') }}" />
        <link href="{{ asset('plugin/magnific-popup/dist/magnific-popup.css') }}" rel="stylesheet" type="text/css" />
        <script src="{{ asset('plugin/ckeditor/ckeditor.js') }}"></script>
    </head>
    @yield('body')
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
    <script type="text/javascript">
        <?php if(Session::has('success')){ ?>
            toastr.success("<?php echo Session::get('success'); ?>");
        <?php }else if(Session::has('failure')){  ?>
            toastr.error("<?php echo Session::get('failure'); ?>");
        <?php } ?>
    </script>
</html>