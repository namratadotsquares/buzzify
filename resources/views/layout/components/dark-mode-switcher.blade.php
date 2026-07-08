<?php

$theme = $theme == 'dark' ? 'light' : 'dark';

//$theme = ($theme == 'dark') ? 'dark' : 'light';

if (Request::segment(1) == 'languages') {
    $segment_1 = '';

    $segment_2 = '';

    $segment_3 = '';

    if (Request::segment(1) != '') {
        $segment_1 = '/' . Request::segment(1);
    }

    if (Request::segment(2) != '') {
        $segment_2 = '/' . Request::segment(2);
    }

    if (Request::segment(3) != '') {
        $segment_3 = '/' . Request::segment(3);
    }

    $dataUrl = url('/') . $segment_1 . $segment_2 . $segment_3 . '?layout=' . $layout . '&theme=' . $theme;
} else {
    $segment_4 = '';

    $segment_5 = '';

    if (Request::segment(4) != '') {
        $segment_4 = '/' . Request::segment(4);
    }

    if (Request::segment(5) != '') {
        $segment_5 = '/' . Request::segment(5);
    }

    $dataUrl = url(Request::segment(1)) . '/' . $layout . '/' . $theme . $segment_4 . $segment_5;
}

?>



@if (Auth::user())
    <div data-url="{{ $dataUrl }}" style="margin-top: -0.4rem;height: 2.6rem;"
        class="dark-mode-switcher cursor-pointer shadow-md fixed right-0 box dark:bg-dark-2 border rounded-full w-40 h-12 flex items-center justify-center z-50 mb-10 mr-10">

        <div class="mr-4 text-gray-700 dark:text-gray-300">{{ __('admin.darkmode') }}</div>
        <div
            class="dark-mode-switcher__toggle {{ $theme == 'light' ? 'dark-mode-switcher__toggle--active' : '' }} border">
        </div>
    </div>
@endif
