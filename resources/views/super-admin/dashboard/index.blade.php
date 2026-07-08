@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')
    
    @if(!empty($openAiQuotaExpired))
        <div class="intro-y mt-4">
            <div class="box px-5 py-4 bg-red-50 border border-red-200">
                <div class="flex items-start">
                    <div class="flex-1">
                        <div class="font-medium text-red-700">ChatGPT limit expired</div>
                        <div class="text-sm text-red-700 mt-1">Limit is expired. Load your wallet to continue using AI features.</div>
                        @if(!empty($openAiQuotaExpiredMessage))
                            <div class="text-xs text-red-600 mt-2">{{ $openAiQuotaExpiredMessage }}</div>
                        @endif
                    </div>
                    <div class="ml-4">
                        <a href="{{ url('/wallet-product/'.$layout.'/'.$theme) }}" class="button text-white bg-red-600 shadow-md">Load Wallet</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xxl:col-span-12 grid grid-cols-12 gap-6">
            <div class="col-span-12 mt-8">
                <!--<div class="intro-y flex items-center h-10">-->
                <!--    <h2 class="text-lg font-medium truncate mr-5">{{__('admin.general_report')}}</h2>-->
                <!--    <a href="" class="ml-auto flex text-theme-1 dark:text-theme-10">-->
                <!--        <i data-feather="refresh-ccw" class="w-4 h-4 mr-3"></i> {{__('admin.reload_data')}}-->
                <!--    </a>-->
                <!--</div>-->
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        @can('category-list')

                        <a href="{{url('/category')}}/{{$layout}}/{{$theme}}">

                            @endcan

                            <div class="report-box zoom-in">
                                <div class="box p-5" style="background-color: #a7d887;">
                                    <div class="flex">
                                        <!-- <i data-feather="layers"></i> -->
                                        <i data-feather="layers" class="report-box__icon text-theme-10"></i>
                                        <div class="ml-auto">
                                        </div>
                                    </div>
                                    <div class="text-3xl font-bold text-white leading-8 mt-6">{{$category}}</div>
                                    <div class="text-base text-white mt-1">{{__('admin.total_categories')}}</div>
                                </div>
                            </div>
                        @can('category-list')

                        </a>
                            @endcan

                    </div>
                    <!-- <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        @can('author-list')
                        <a href="{{url('/author')}}/{{$layout}}/{{$theme}}">
                            @endcan
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="edit" class="report-box__icon text-theme-11"></i>
                                        <div class="edit">
                                        </div>
                                    </div>
                                    <div class="text-3xl font-bold leading-8 mt-6">{{$author}}</div>
                                    <div class="text-base text-gray-600 mt-1">{{__('admin.total_authors')}}</div>
                                </div>
                            </div>
                        @can('author-list')
                        </a>
                        @endcan

                    </div> -->
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">

                        @can('blog-list')

                        <a href="{{url('/blog')}}/{{$layout}}/{{$theme}}">

                        @endcan

                            <div class="report-box zoom-in">
                                <div class="box p-5" style="background-color: #e87198;">
                                    <div class="flex">
                                        <i data-feather="monitor" class="report-box__icon text-theme-12"></i>
                                        <div class="ml-auto">
                                        </div>
                                    </div>
                                    <div class="text-3xl font-bold text-white leading-8 mt-6">{{$blog}}</div>
                                    <div class="text-base text-white mt-1">{{__('admin.total_blogs')}}</div>
                                </div>
                            </div>

                        @can('blog-list')

                        </a>
                        @endcan

                    </div>
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        @can('user-list')

                        <a href="{{url('/users')}}/{{$layout}}/{{$theme}}">

                        @endcan


                            <div class="report-box zoom-in">
                                <div class="box p-5" style="background-color: #75ccc6;">
                                    <div class="flex">
                                        <i data-feather="user" class="report-box__icon text-theme-6"></i>
                                        <div class="ml-auto">
                                        </div>
                                    </div>
                                    <div class="text-3xl font-bold text-white leading-8 mt-6">{{$user}}</div>
                                    <div class="text-base text-white mt-1">{{__('admin.total_users')}}</div>
                                </div>
                            </div>
                        @can('user-list')
                            </a>
                        @endcan

                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 xxl:col-span-3 xxl:border-l border-theme-5 -mb-10 pb-10">
            <div class="xxl:pl-6 grid grid-cols-12 gap-6">
            </div>
        </div>
    </div>
@endsection
