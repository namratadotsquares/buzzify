@extends('../site/layout/base')

@section('body')
    <body class="woocommerce-account app mobile_nav_class jl-has-sidebar">
        @yield('content')
              <script src="{{ asset('site/js/jquery-v3.6.0.js') }}"></script>
             <script src="{{ asset('site/js/fluidvids.js') }}"></script>
             <script src="{{ asset('site/js/infinitescroll.js') }}"></script>
             <script src="{{ asset('site/js/justified.js') }}"></script>
             <script src="{{ asset('site/js/slick.js') }}"></script>
             <script src="{{ asset('site/js/theia-sticky-sidebar.js') }}"></script>
             <script src="{{ asset('site/js/aos.js') }}"></script>
             <script src="{{ asset('site/js/custom.js') }}"></script>
             <script src="{{ asset('js/site.js') }}"></script>
             <script src="{{ asset('js/check-theme-mode.js') }}"></script>
        @yield('script')
        
    </body>
@endsection