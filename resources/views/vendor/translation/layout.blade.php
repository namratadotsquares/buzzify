<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('/vendor/translation/css/main.css') }}">
</head>
<body>
    
    <div id="app">
        
        @include('translation::nav')
        @include('translation::notifications')
        
        @yield('body')
        
    </div>
    
    <script src="{{ asset('/vendor/translation/js/app.js') }}"></script>
</body>
</html>
 -->
<?php $theme = 'light'; $layout= 'side-menu'; $page_name ='Language'; ?>

 @extends('../layout/base')
@section('body')
<link rel="stylesheet" href="{{ asset('/vendor/translation/css/main.css') }}">

    <body class="app">

        @yield('content')
        @include('../layout/components/dark-mode-switcher')
        <script src="{{ asset('dist/js/app.js') }}"></script>
        <script src="{{asset('js/jquery-v3.6.0.min.js')}}"></script>
        <script type="text/javascript" src="{{ url('plugin/toastr/toastr.min.js')}}"></script>
        <script src="{{ url('plugin/magnific-popup/dist/jquery.magnific-popup.min.js') }}"></script>
        <script src="{{asset('js/jquery-ui.js')}}"></script>
        <script src="{{ asset('/vendor/translation/js/app.js') }}"></script>
        @yield('script')
    </body>
@endsection