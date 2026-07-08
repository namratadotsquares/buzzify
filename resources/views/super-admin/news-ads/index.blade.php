@extends('../layout/' . $layout)



@section('subhead')

    <title>Ads  - {{setting('site_name')}}</title>

@endsection



@section('subcontent')

    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">Ads List</h2>

    <div class="grid grid-cols-12 gap-6 mt-5">

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">







            <div class="hidden md:block mx-auto text-gray-600">  </div>



            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">

                <div class="relative text-gray-700 dark:text-gray-300">

                    <!--<form method="GET">-->



                    <!--        <label>Start Date</label>-->

                    <!--        <input type="date" class="input w-56 box pr-10 placeholder-theme-13 mr-4" @if(isset($_GET['start_date'])) value="{{$_GET['start_date']}}" @endif name="start_date" placeholder="Start date">-->





                    <!--        <label>End Date</label>-->

                    <!--        <input type="date" class="input w-56 box pr-10 placeholder-theme-13 mr-4" @if(isset($_GET['end_date'])) value="{{$_GET['end_date']}}" @endif name="end_date" placeholder="end date">-->





                    <!--    <input type="submit" id="search" value="Search" class="button text-white bg-theme-1 shadow-md mr-2" name="search">-->

                    <!--</form>-->

                </div>

            </div>



            <a class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" href="{{url('/ads')}}/{{$layout}}/{{$theme}}">{{__('admin.reset')}}</a>



        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">

            <table class="table table-report -mt-2">

                <thead>

                    <tr>

                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>

                        <th class="whitespace-no-wrap">{{__('admin.url')}}</th>

                        <th class="whitespace-no-wrap">Frequency</th>

                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>


                        <th class="whitespace-no-wrap width-20">{{__('admin.status')}}</th>


                        @if(Gate::check('ads-edit') || Gate::check('ads-delete') || Gate::check('ads-analytics'))

                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>

                        @endif

                    </tr>

                </thead>



                <tbody id="tablecontentsads">

                    <?php $query = '';

                        if(isset($_GET['post'])){

                            $query = '?post='.$_GET['post'];

                        }

                    ?>

                    <span data-type="ads" id="tableData"></span>

                    @if(count($ads))

                        <?php

                        $page = (isset($_GET['page']))?$_GET['page']:1;

                        if($page>1){

                            $i = $ads->perPage() * ($ads->currentPage() - 1) + 1;

                        }

                        else{

                            $i=1;

                        }

                        ?>

                        @foreach ($ads as $row)

                            <tr class="intro-x row1" data-id="{{ $row->id }}">

                                <td >

                                    {{$i}}

                                </td>

                                <td>{{ $row->url }}</td>

                                <td>{{ $row->frequency ?? 1 }}</td>

                                <td>

                                    <!--<img src="https://buzzifyhub.com/storage/newsads/{{$row->image}}" class="thumb-img-list" alt="{{$row->title}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">-->
                                     <img src="{{url('storage/newsads/'.$row->image)}}" class="thumb-img-list" alt="{{$row->title}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">

                                </td>

                                <td>

                                   

                                        @if($row->status == 1)

                                            <a href="{{url('changeNews-status-ad/')}}/{{$row->id}}/0">

                                                <div class="flex items-center justify-center text-theme-9">

                                                    <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}

                                                </div>

                                            </a>

                                        @else

                                            <a href="{{url('changeNews-status-ad/')}}/{{$row->id}}/1">

                                                <div class="flex items-center justify-center text-theme-6">

                                                    <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}

                                                </div>

                                            </a>

                                        @endif

                           

                                </td>



                                

                                

                                @if(Gate::check('ads-edit') || Gate::check('ads-delete') || Gate::check('ads-analytics'))

                                <td class="table-report__action w-40">

                                    <div class="flex justify-center items-center">

                                        <!--<a class="flex items-center mr-3" href="{{url('change-order/')}}/{{$layout}}/{{$theme}}/{{$row->id}}" title="{{__('admin.change_order')}}">-->

                                        <!--    <i data-feather="list" class="w-4 h-4 mr-1"></i>-->

                                        <!--</a>-->

                                        <!--<a class="flex items-center mr-3" href="{{url('show-preview/')}}/{{$layout}}/{{$theme}}/{{$row->id}}" title="{{__('admin.preview')}}">-->

                                        <!--    <i data-feather="image" class="w-4 h-4 mr-1"></i>-->

                                        <!--</a>-->



                                        <!--@can('ads-analytics')-->

                                        <!--<a class="flex items-center mr-3 text-theme-9 font-size23" href="{{route('analytics-ads',[$row->id,$layout,$theme])}}"  title="{{__('admin.analytics ')}}">-->

                                        <!--     <i data-feather="activity" class="w-4 h-4 mr-1"></i>-->

                                        <!--</a>-->

                                        <!--@endcan-->



                                        @if (setting('instagram_share') == 1)

                                        <a class="flex items-center mr-3" title="{{__('admin.instagram_image')}}" href="{{asset('upload/social-media-post/instagram/')}}/{{$row->scial_media_image}}" download>

                                           <i data-feather="download" class="w-4 h-4 mr-1"></i>

                                        </a>

                                        @endif

                                        

                                        @can('ads-edit')

                                        <a class="flex items-center mr-3" href="{{url('edit-Newsad/')}}/{{$layout}}/{{$theme}}/{{$row->id}}" title="{{__('admin.edit')}}">

                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i>

                                            </a>

                                        @endcan    



                                        @can('ads-delete')

                                            <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}" title="{{__('admin.delete')}}">

                                                <i data-feather="trash-2" class="w-4 h-4 mr-1"></i>

                                            </a>

                                        @endcan

                                    </div>

                                    <div class="modal" id="delete-confirmation-modal-{{$row->id}}">

                                        <div class="modal__content">

                                            <div class="p-5 text-center">

                                                <i data-feather="x-circle" class="w-16 h-16 text-theme-6 mx-auto mt-3"></i>

                                                <div class="text-3xl mt-5">{{__('admin.sure_warning')}}</div>

                                                <div class="text-gray-600 mt-2">{{__('admin.delete_warning')}}</div>

                                            </div>

                                            <div class="px-5 pb-8 text-center">

                                                <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1">{{__('admin.cancel')}}</button>

                                                <a href="{{url('delete-newsad')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>

                                            </div>

                                        </div>

                                    </div>

                                </td>

                                @endif

                            </tr>

                            <?php $i++; ?>

                        @endforeach

                    @else

                        <tr class="intro-x text-center text-danger">

                            <td class="w-40" colspan="10">

                                {{__('admin.no_record_found')}}

                            </td>

                        </tr>

                    @endif

                </tbody>

            </table>

        </div>

        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">

            <ul class="pagination">

                {!! $ads->appends(request()->except('page'))->render() !!}

            </ul>

        </div>

        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">

             

        </div>

        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

            <p class="text-right"><?php if ($ads->firstItem() != null) { ?> {{__('admin.showing')}} {{ $ads->firstItem() }} {{__('admin.to')}} {{ $ads->lastItem() }} {{__('admin.of')}} {{ $ads->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>

    </div>

@endsection
