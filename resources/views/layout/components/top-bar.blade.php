<div class="top-bar">



    <?php
    
    $layout = isset($layout) ? $layout : 'side-menu';
    
    $theme = isset($theme) ? $theme : 'light';
    
    $userData = Auth::user();
    
    $language = DB::table('languages')->get();
    
    ?>



    <div class="-intro-x breadcrumb mr-auto hidden sm:flex">

        <?php if (isset($breadcrumb)) {
            echo $breadcrumb;
        } ?>

        @if(request()->segment(1) == 'user-feedback')
        @else
        <div style="margin-left:20px;">
            @can('blog-create')
            <a href="{{ url('/add-blog') }}/{{ $layout }}/{{ $theme }}"
                class="button text-white bg-theme-1 shadow-md mr-2">{{ __('admin.create_post') }}</a>
            @endcan

        </div>
        @endif
    </div>



    <div class="intro-x w-8 h-8 header-elements">

        <div style="display:flex; ">



            <div>@include('../layout/components/dark-mode-switcher')</div>

        </div>

        <div class="dropdown">

            <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in">

                @php
                    $userPhoto = optional($userData)->photo ?? '';
                    $hasUserPhoto = is_string($userPhoto) && trim($userPhoto) !== '';
                @endphp

                @if ($hasUserPhoto)
                    <img
                        src="{{ url('upload/user') }}/{{ $userPhoto }}"
                        alt="{{ optional($userData)->name ?? 'User' }}"
                        onerror="this.onerror=null;this.style.display='none';var f=this.nextElementSibling;if(f){f.classList.remove('hidden');f.classList.add('flex');}"
                    >
                    <div class="hidden absolute inset-0 items-center justify-center bg-gray-300 dark:bg-dark-3 text-gray-800 dark:text-gray-100">
                        <i data-feather="user" class="w-5 h-5"></i>
                    </div>
                @else
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-300 dark:bg-dark-3 text-gray-800 dark:text-gray-100">
                        <i data-feather="user" class="w-5 h-5"></i>
                    </div>
                @endif

            </div>

            <div class="dropdown-box w-56">

                <div class="dropdown-box__content box bg-theme-38 dark:bg-dark-6 text-white">

                    <div class="p-4 border-b border-theme-40 dark:border-dark-3">

                        <div class="font-medium"><a
                                href="{{ url('/profile/') }}/{{ $layout }}/{{ $theme }}">{{ $userData->name }}</a>
                        </div>

                        <div class="text-xs text-theme-41 dark:text-gray-600"><a
                                href="{{ url('/profile/') }}/{{ $layout }}/{{ $theme }}">{{ $userData->email }}</a>
                        </div>

                    </div>

                    <div class="p-2 border-t border-theme-40 dark:border-dark-3">

                        <a href="{{ url('/admin_logout') }}"
                            class="flex items-center block p-2 transition duration-300 ease-in-out hover:bg-theme-1 dark:hover:bg-dark-3 rounded-md">

                            <i data-feather="toggle-right" class="w-4 h-4 mr-2"></i> {{ __('admin.logout') }}

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>



</div>
