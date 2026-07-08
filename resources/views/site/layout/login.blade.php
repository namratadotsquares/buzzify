@extends('../layout/base')

@section('body')
    <body class="login">
        @yield('content')
        @include('../layout/components/dark-mode-switcher')
        <script src="{{ asset('dist/js/app.js') }}"></script>
        <script type="text/javascript" src="{{ url('plugin/toastr/toastr.min.js')}}"></script>
        @yield('script')
    </body>
@endsection