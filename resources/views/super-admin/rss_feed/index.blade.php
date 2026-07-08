@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.rss_feed_list')}} - {{setting('site_name')}}</title>
@endsection
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" /> -->
@section('subcontent')
    @include('../layout/components/top-bar')
    

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.rss_feed_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">

                @can('rss-feed-create')
                <a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_rss_feed')}}</a>
                @endcan
                <div class="hidden md:block mx-auto text-gray-600">  </div>
                <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                    <div class="w-56 relative text-gray-700 dark:text-gray-300">
                        <form method="GET">
                            <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['name'])) value="{{$_GET['name']}}" @endif name="name" placeholder="{{__('admin.search_by_name')}}">
                            <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a> 
                            <input type="submit" id="search" class="hide" name="search"> 
                        </form>
                    </div>
                </div>
                <a class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" href="{{url('/rss-feed-src')}}/{{$layout}}/{{$theme}}">{{__('admin.reset')}}</a>
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.title_and_src')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.category')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        @can('rss-feed-status')
                        <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                        @endcan
                        @if(Gate::check('rss-feed-edit') || Gate::check('rss-feed-delete'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endif
                    </tr>
                </thead>
            
                <tbody>
                    @if(count($feed))
                        <?php
                            $page=(isset($_GET['page']))?$_GET['page']:1;
                            if($page>1){
                                $i=$feed->perPage() * ($feed->currentPage() - 1) + 1;
                            }
                            else{
                                $i=1;
                            }
                        ?>
                        @foreach($feed as $row)
                            <tr class="intro-x row1" data-id="{{ $row->id }}">
                                <td class="w-40">
                                    {{$i}}
                                </td> 
                                <td>
                                    <a class="font-medium whitespace-no-wrap">{{ $row->rss_name }}</a> 
                                    <div class="text-gray-600 text-xs whitespace-no-wrap">{{ $row->rss_url }}</div>                                    
                                </td>
                                <td>@if(isset($row->categoryData->name)) {{ $row->categoryData->name }} @endif</td>
                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif
                                </td>
                                @can('rss-feed-status')
                                <td class="w-40">
                                    @if($row->status==1)
                                        <a href="{{url('change-status-rss-feed-src/')}}/{{$row->id}}/0">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                            </div>
                                        </a> 
                                    @else
                                        <a href="{{url('change-status-rss-feed-src/')}}/{{$row->id}}/1">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                            </div>
                                        </a>
                                    @endif                               
                                </td>
                                @endcan
                                @if(Gate::check('rss-feed-edit') || Gate::check('rss-feed-delete'))
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                        @can('rss-feed-edit')
                                        <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$row->id}}"  title="{{__('admin.edit')}}">
                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                        </a>
                                        @endcan
                                        @can('rss-feed-delete')
                                        <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}"  title="{{__('admin.delete')}}">
                                            <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> {{__('admin.delete')}}
                                        </a>
                                        @endcan
                                    </div>
                                    <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                <h2 class="font-medium text-base mr-auto">{{__('admin.edit_rss_feed')}}</h2>
                                            </div>
                                            <form id="editrssfeedform_{{$row->id}}">
                                                <input type="hidden" name="id" value="{{$row->id}}">
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.select_category')}}</label>
                                                        <select class="input w-full border mt-2 flex-1" name="category_id">
                                                            <option value="">{{__('admin.select_category')}}</option>
                                                            @foreach($category as $detail)
                                                                <option value="{{$detail->id}}" <?php if($row->category_id==$detail->id){ echo "selected" ; }?>>{{$detail->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.name')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1 height-40" name="rss_name" placeholder="{{__('admin.name_placeholder')}}" value="{{$row->rss_name}}">
                                                    </div>
                                                </div>
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.url')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1 height-40" name="rss_url" placeholder="{{__('admin.url_placeholder')}}" value="{{$row->rss_url}}">
                                                    </div>
                                                </div> 
                                                
                                                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                    <input type="button" class="button w-20 bg-theme-1 text-white" value="Update" onclick="add_rss_feed_src(event,'editrssfeedform_{{$row->id}}')">
                                                </div>
                                            </form>            
                                        </div>
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
                                                <a href="{{url('delete-rss-feed-src')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
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
                            <td class="w-40" colspan="6">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
                {!! $feed->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center" style="color:black">
            <?php $entry_count = (isset($_GET['per_page']))?$_GET['per_page']:config('constant.paginate.num_per_page'); ?>
            <form>
                <select id="pagination" class="form-select">
                    <option value="5" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 5){ echo "selected"; }else{ if($entry_count==5) { echo "selected"; } } ?> >5</option>
                    <option value="10" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 10){ echo "selected"; }else{ if($entry_count==10) { echo "selected"; } } ?>>10</option>
                    <option value="25" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 25){ echo "selected"; }else{ if($entry_count==25) { echo "selected"; } } ?>>25</option>
                    <option value="50" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 50){ echo "selected"; }else{ if($entry_count==50) { echo "selected"; } } ?>>50</option>
                    <option value="100" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 100){ echo "selected"; }else{ if($entry_count==100) { echo "selected"; } } ?>>100</option>
                </select> 
            </form>
        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

            <p class="text-right"><?php if ($feed->firstItem() != null) { ?> {{__('admin.showing')}} {{ $feed->firstItem() }} {{__('admin.to')}} {{ $feed->lastItem() }} {{__('admin.of')}} {{ $feed->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>
    </div>
    <div class="modal" id="header-footer-modal-preview">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_rss_feed')}}</h2>
            </div>
            

            <form id="addrssfeedform">
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.select_category')}}</label>
                        <select class="input w-full border mt-2 flex-1" name="category_id">
                            <option value="">{{__('admin.select_category')}}</option>
                            @foreach($category as $detail)
                                <option value="{{$detail->id}}">{{$detail->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.name')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1 height-40" name="rss_name" placeholder="{{__('admin.name_placeholder')}}">
                    </div>
                </div>
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.url')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1 height-40" name="rss_url" placeholder="{{__('admin.url_placeholder')}}">
                    </div>
                </div>               

                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                    <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.create')}}" onclick="add_rss_feed_src(event,'addrssfeedform')">
                </div>
            </form>            
        </div>
    </div>
    <script>
        document.getElementById('pagination').onchange = function() { 
        window.location = "{!! $feed->url(1) !!}&per_page=" + this.value; 
       };  
    </script>
@endsection