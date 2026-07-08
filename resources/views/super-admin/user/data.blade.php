@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.settings_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')
<style type="text/css">
    .tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}
.tab button.active {
  background-color: #ccc;
}
</style>
    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.settings_list')}}</h2>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 xxl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box bg-theme-1 p-5 mt-6">
                <div class="border-theme-3 dark:border-dark-5  text-white tab">
                    <button class="flex items-center px-3 py-2 rounded-md tablinks" onclick="openTab(event, 'personalization')"> <i class="w-4 h-4 mr-2" data-feather="slack"></i> My Personalization </button>
{{--                    <button class="flex items-center px-3 py-2 rounded-md tablinks" onclick="openTab(event, 'viewpoll')"> <i class="w-4 h-4 mr-2" data-feather="eye"></i> Blog View </button>--}}


                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 xxl:col-span-9 tabcontent" id="personalization" style="display: block;">
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">


                    <h2 class="font-medium text-base mr-auto">

                    </h2>
                </div>
                <div class="p-5">

                        <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">




                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <!-- <i data-feather="layers"></i> -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers report-box__icon text-theme-10"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                        <div class="ml-auto">
                                        </div>
                                    </div>
                                    <div class="text-3xl font-bold leading-8 mt-6">{{count($users['personalization'])}}</div>
                                    <div class="text-base text-gray-600 mt-1">Total Personalization</div>
                                </div>
                            </div>



                    </div>


                     <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">

            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">Category</th>


                    </tr>
                </thead>

                <tbody>
                  @if($users['personalization'])
                    @foreach($users['personalization'] as $key=>$item)

                            <tr class="intro-x" >
                                <td >
                                  {{$key+1}}
                                </td>

                                <td><a href="/category/side-menu/light">{{$item['category_data']['name']}}</a></td>


                            </tr>
                            @endforeach
                     @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="10">

                                @if(!isset($analytics['voting']->is_voting_enable))
                                 Voting Not Enable
                                 @else
                                    {{__('admin.no_record_found')}}
                             @endif
                            </td>
                        </tr>
                        @endif

                </tbody>
            </table>
        </div> </div></div></div></div>
{{--        <div class="col-span-12 lg:col-span-8 xxl:col-span-9 tabcontent" id="viewpoll">--}}
{{--            <div class="intro-y box lg:mt-5">--}}
{{--                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">--}}


{{--                    <h2 class="font-medium text-base mr-auto">--}}
{{--                        @if(isset($title)){{$title}}@endif--}}
{{--                    </h2>--}}
{{--                </div>--}}
{{--                <div class="p-5">--}}

{{--                        <div class="grid grid-cols-12 gap-6 mt-5">--}}


{{--                    <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">--}}

{{--                        <a href="http://localhost:8000/users/side-menu/light">--}}



{{--                            <div class="report-box zoom-in">--}}
{{--                                <div class="box p-5">--}}
{{--                                    <div class="flex">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user report-box__icon text-theme-9"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>--}}
{{--                                        <div class="ml-auto">--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="text-3xl font-bold leading-8 mt-6">{{count($users['personalization'])}}</div>--}}
{{--                                    <div class="text-base text-gray-600 mt-1">Total Personalization</div>--}}

{{--                                </div>--}}

{{--                            </div>--}}
{{--                                                    </a>--}}

{{--                    </div>--}}
{{--                     <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">--}}
{{--            <table class="table table-report -mt-2">--}}
{{--                <thead>--}}
{{--                    <tr>--}}
{{--                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>--}}
{{--                        <th class="whitespace-no-wrap">Name</th>--}}



{{--                        <th class="text-center whitespace-no-wrap">Created At</th>--}}
{{--                    </tr>--}}
{{--                </thead>--}}

{{--                <tbody>--}}


{{--                </tbody>--}}
{{--            </table>--}}
{{--        </div>--}}
{{--                </div>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
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
