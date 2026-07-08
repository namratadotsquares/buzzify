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
       /* padding: 1.8rem;*/
       padding-top: 0.5rem;
       padding-bottom: 0.5rem;
        padding-right: 1.8rem;
        padding-left: 1.8rem;
    }

    
</style>
<h2 class="intro-y text-lg font-medium mt-10">@if(strlen($analytics['blogDetail']->title)>60) {{substr($analytics['blogDetail']->title,0,60)}}... @else {{$analytics['blogDetail']->title}} @endif  -  {{__('admin.analytics')}}</h2><div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-12 xxl:col-span-12 flex lg:block flex-col-reverse">
        <div class="intro-y box mt-6">
            <div class="p-18"> 
                <div class="border-theme-3 dark:border-dark-5 flex  tab">
                    <button class="flex items-center pr-pl py-2 rounded-md tablinks active " onclick="openTab(event, 'poll')"><i class="w-4 h-4 mr-2" data-feather="slack"></i> Poll</button>&nbsp;
                    <button class="flex items-center pr-pl py-2 rounded-md tablinks" onclick="openTab(event, 'viewpoll')"><i class="w-4 h-4 mr-2" data-feather="eye"></i> Blog View</button>
                </div>
            </div>
    
            <div class=" tabcontent" id="poll" style="display: block;">
                <div class="p-5"> 
                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">
                           {{-- {{$analytics['voting']->VotingQuestion}} --}}
                        </h2>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-6 mt-5">
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
                                    <div class="text-3xl font-bold leading-8 mt-6">{{count($analytics['vote'])}}</div>
                                    <div class="text-base text-gray-600 mt-1">Total React</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="layers"></i>
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
                                            class="feather feather-layers report-box__icon text-theme-10"
                                        >
                                            <!-- <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                            <polyline points="2 17 12 22 22 17"></polyline>
                                            <polyline points="2 12 12 17 22 12"></polyline> -->
                                        </svg>
                                        <div class="ml-auto"></div>
                                    </div>
                                    <div class="text-3xl font-bold leading-8 mt-6">{{$analytics['search_view']}}</div>
                                    <div class="text-base text-gray-600 mt-1">View By Search</div>
                                </div>
                            </div>
                        </div>
                        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                            <table class="table table-report -mt-2">
                                <thead>
                                    <tr>
                                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                                        <th class="whitespace-no-wrap">Name</th>

                                        <th class="text-center whitespace-no-wrap">Vote</th>
                                        <th class="text-center whitespace-no-wrap">Created At</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @if($analytics['vote']) @foreach($analytics['vote'] as $item)
                                    <tr class="intro-x">
                                        <td>
                                            {{$item['id']}}
                                        </td>
                                        <td>
                                            @if(isset($item['user']))
                                            <a href="/users/side-menu/light"> {{$item['user']['name'] }} </a>
                                            @endif
                                        </td>

                                        <td class="text-center">{{($analytics['blogDetail']->optiontype==0)?(($item['vote']==1)?'NO':'Yes'):(($item['vote']==1)?'Disagree':'Agree')}}</td>
                                        <td class="text-center">{{\Carbon\Carbon::parse($item['created_at'])->diffForhumans()}}</td>
                                    </tr>
                                    @endforeach @else
                                    <tr class="intro-x text-center text-danger">
                                        <td class="w-40" colspan="10">
                                            @if(!$analytics['voting']->is_voting_enable) Voting Not Enable @else {{__('admin.no_record_found')}} @endif
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
       
            <div class=" tabcontent" id="viewpoll">
                <div class="p-5"> 
                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">
                            {{-- @if(isset($title)){{$title}}@endif --}}
                        </h2>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="http://localhost:8000/users/side-menu/light">
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
                                        <div class="text-3xl font-bold leading-8 mt-6">{{count($analytics['blog_view'])}}</div>
                                        <div class="text-base text-gray-600 mt-1">Total View</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                            <table class="table table-report -mt-2">
                                <thead>
                                    <tr>
                                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                                        <th class="whitespace-no-wrap">Name</th>

                                        <th class="text-center whitespace-no-wrap">Created At</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @if($analytics['blog_view']) @foreach($analytics['blog_view'] as $item)

                                    <tr class="intro-x">
                                        <td>
                                            {{$item['id']}}
                                        </td>
                                        <td>
                                            @if(isset($item['user']))
                                            <a href="/users/side-menu/light"> {{$item['user']['name'] }} </a>
                                            @else
                                            <a href="javascript:;">Guest user </a>
                                            @endif
                                        </td>

                                        <td class="text-center">{{\Carbon\Carbon::parse($item['created_at'])->diffForhumans()}}</td>
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
