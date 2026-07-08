@extends('../layout/' . $layout) @section('subhead')
<title>{{__('admin.settings_list')}} - {{setting('site_name')}}</title>
@endsection @section('subcontent') @include('../layout/components/top-bar')
<style type="text/css">
    .tabcontent {
        display: none;
        padding: 6px 12px;
        /*border: 1px solid #ccc;*/
        border-top: none;
    }
    .tab button.active {
        background-color: #ccc;
    }
    
    .pr-pl{
        padding-left: 4.75rem;
        padding-right: 4.75rem;
    }
    .p-18{
       padding-top: 0.5rem;
       padding-bottom: 0.5rem;
        padding-right: 1.8rem;
        padding-left: 1.8rem;
    }
</style>
<div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
    <div class="hidden md:block mx-auto text-gray-600"></div>

    <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
        <div class="relative text-gray-700 dark:text-gray-300">
            <form method="GET">
                <label>Start Date</label>
                <input type="date" class="input w-56 box pr-10 placeholder-theme-13 mr-4" @if(isset($_GET['start_date'])) value="{{$_GET['start_date']}}" @endif name="start_date" placeholder="Start date">

                <label>End Date</label>
                <input type="date" class="input w-56 box pr-10 placeholder-theme-13 mr-4" @if(isset($_GET['end_date'])) value="{{$_GET['end_date']}}" @endif name="end_date" placeholder="end date">

                <input type="submit" id="search" value="Search" class="button text-white bg-theme-1 shadow-md mr-2" name="search" />
            </form>
        </div>
    </div>

    <a class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" href="{{route('analytics-ads',[$id,$layout,$theme])}}">{{__('admin.reset')}}</a>
</div>
<h2 class="intro-y text-lg font-medium mt-3">{{__('admin.settings_list')}}</h2>
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-12 xxl:col-span-12 flex lg:block flex-col-reverse">
        <div class="intro-y box  mt-6">
            <div class="p-18"> 
                <div class="border-theme-3 flex dark:border-dark-5 tab">
                    <button class="flex items-center  pr-pl py-2 rounded-md tablinks active" onclick="openTab(event, 'Analytics')"><i class="w-4 h-4 mr-2" data-feather="slack"></i> Ads Analytics</button>
                    <button class="flex items-center py-2 pr-pl rounded-md tablinks" onclick="openTab(event, 'click')"><i class="w-4 h-4 mr-2" data-feather="eye"></i> Ads Click</button>
                </div>
            </div>    

            <div class="tabcontent" id="Analytics" style="display: block;">
                <div class="p-5"> 
                    <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">
                            {{-- {{$analytics['Ads']}}--}}
                        </h2>
                    </div>
                </div>    
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i class="w-6 h-8 mr-6" data-feather="eye"></i>
                                        <div class="ml-auto"></div>
                                    </div>
                                    <div class="text-3xl font-bold leading-8 mt-6">{{count($ads_views)}}</div>
                                    <div class="text-base text-gray-600 mt-1">Total View</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="feather feather-user report-box__icon text-theme-9"
                                        >
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <div class="ml-auto"></div>
                                    </div>
                                    <div class="text-3xl font-bold leading-8 mt-6">{{count($ads_clicks)}}</div>
                                    <div class="text-base text-gray-600 mt-1">Total Click</div>
                                </div>
                            </div>
                        </div>
                        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                            <table class="table table-report -mt-2">
                                <thead>
                                    <tr>
                                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                                        <th class="whitespace-no-wrap">Name</th>
                                        <th class="whitespace-no-wrap">Created At</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @if($ads_views) @foreach($ads_views as $item)
                                    <tr class="intro-x">
                                        <td>
                                            {{$item['id']}}
                                        </td>
                                        <td>
                                            @if(isset($item['users']))
                                            <a href="/users/side-menu/light"> {{$item['users']['name'] }} </a>
                                            @endif
                                        </td>
                                        <td>{{\Carbon\Carbon::parse($item['created_at'])->diffForhumans()}}</td>
                                    </tr>
                                    @endforeach @else
                                    <tr class="intro-x text-center text-danger">
                                        <td class="w-40" colspan="10">
                                            {{__('admin.no_record_found')}}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="tabcontent" id="click" style="display: none;">
                <div class="p-5"> 
                    <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">
                            {{-- {{$analytics['Ads']}}--}}
                        </h2>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                            <table class="table table-report -mt-2">
                                <thead>
                                    <tr>
                                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                                        <th class="whitespace-no-wrap">Name</th>
                                        <th class="whitespace-no-wrap">Created At</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @if($ads_clicks) @foreach($ads_clicks as $item)
                                    <tr class="intro-x">
                                        <td>
                                            {{$item['id']}}
                                        </td>
                                        <td>
                                            @if(isset($item['users']))
                                            <a href="/users/side-menu/light"> {{$item['users']['name'] }} </a>
                                            @endif
                                        </td>
                                        <td>{{\Carbon\Carbon::parse($item['created_at'])->diffForhumans()}}</td>
                                    </tr>
                                    @endforeach @else
                                    <tr class="intro-x text-center text-danger">
                                        <td class="w-40" colspan="10">
                                            {{__('admin.no_record_found')}}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function openTab(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>
@endsection
