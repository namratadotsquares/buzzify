@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.quotes_list')}} - {{setting('site_name')}}</title>
@endsection

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.quotes_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            @can('quote-create')
            <a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_quote')}}</a>
            @endcan
            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['quote'])) value="{{$_GET['quote']}}" @endif name="quote" placeholder="{{__('admin.search_by_quote')}}">
                        <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a> 
                        <input type="submit" id="search"  class="hide" name="search">
                    </form>
                </div>
            </div>
            <button class="button ml-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>
            
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.quotes')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.schedule_date')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        @can('quote-status')
                        <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                        @endcan
                        @if(Gate::check('quote-edit') || Gate::check('quote-delete'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endif
                    </tr>
                </thead>
               
                <tbody id="tablecontents">
                    @if(count($quotes))
                        <?php
                        $page = (isset($_GET['page']))?$_GET['page']:1;
                        if($page>1){
                            $i = $quotes->perPage() * ($quotes->currentPage() - 1) + 1;
                        }
                        else{
                            $i=1;
                        }
                        ?>
                        @foreach ($quotes as $row)
                            <tr class="intro-x row1" data-id="{{ $row->id }}">
                                <td class="">
                                    {{$i}}
                                </td>

                                <td>{{ substr($row->quote, 0, 10) }}...</td>

                                <td>@if($row->schedule_date!=null){{ date(setting('date_format'),strtotime($row->schedule_date)) }}@else -- @endif</td>


                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif</td>
                                @can('quote-status')
                                <td class="w-40">
                                    @if($row->status==1)
                                        <a href="{{url('change-status-quote/')}}/{{$row->id}}/0">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                            </div>
                                        </a> 
                                    @else
                                        <a href="{{url('change-status-quote/')}}/{{$row->id}}/1">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                            </div>
                                        </a>
                                    @endif                               
                                </td>
                                @endcan
                                @if(Gate::check('quote-edit') || Gate::check('quote-delete'))
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">

                                        <!-- <a class="flex items-center mr-3 text-theme-3 font-size23" href="javascript:;" data-toggle="modal" data-target="#translate_e_paper_edit_{{$row->id}}"  title="{{__('admin.translate')}}">
                                            <i data-feather="edit-3" class="w-4 h-4 mr-1"></i>
                                            <small class="font-size15 ml-1" >{{__('admin.translate')}}</small>
                                        </a> -->

                                        @can('quote-edit')
                                        <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$row->id}}"  title="{{__('admin.edit')}}">
                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                        </a>
                                        @endcan

                                        @can('quote-delete')
                                        <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}"  title="{{__('admin.delete')}}">
                                            <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> {{__('admin.delete')}}
                                        </a>
                                        @endcan
                                    </div>




                                    <div class="modal" id="translate_e_paper_edit_{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                <h2 class="font-medium text-base mr-auto">{{__('admin.edit_quote')}}</h2>
                                            </div>
                                           
                                            <form id="addcategoryform" method="post" action="{{url('translate-quote')}}">

                                                @csrf

                                                <input type="hidden" id="quote_id" name="quote_id" value="{{$row->id}}">


                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label class="mb-2">{{__('admin.language')}}</label>
                                                        <select data-placeholder="{{__('admin.select_language')}}" name="language_code" class="tail-select w-full " onchange="getQuoteTranslation('{{$row->id}}',this.value)">
                                                            <option value="" >{{__('admin.select_language')}}</option>
                                                            @foreach($languages as $lang)
                                                                <option  @if($row->language_code == $lang->language) selected @endif value="{{$lang->language}}">{{$lang->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.quote')}}</label>
                                                        <textarea name="quote" id="quote_{{$row->id}}" class="input w-full border mt-2 flex-1" required>{{$row->quote}}</textarea>
                                                    </div>
                                                </div>

                                                
                                                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                    <input type="submit" class="button w-20 bg-theme-1 text-white" value="{{__('admin.save')}}">
                                                </div>
                                            </form>  
                                        </div>
                                    </div>



                                    <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                <h2 class="font-medium text-base mr-auto">{{__('admin.edit_quote')}}</h2>
                                            </div>
                                           
                                            <form id="addcategoryform" method="post" action="{{url('add-update-quote')}}">

                                                @csrf

                                                <input type="hidden" id="quote_id" name="id" value="{{$row->id}}">

                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.quote')}}</label>
                                                        <textarea name="quote" class="input w-full border mt-2 flex-1" required>{{$row->quote}}</textarea>
                                                    </div>
                                                </div>

                                                 <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.schedule_date')}} </label>
                                                        <input type="text" name="schedule_date" class="input w-full border mt-2 flex-1 datepicker" name="schedule_date" placeholder="{{__('admin.schedule_date_placeholder')}}" value="{{date('Y-m-d',strtotime($row->schedule_date))}}" required>
                                                    </div>
                                                </div>

                                                 <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.schedule_time')}} </label>
                                                        <input value="{{date('H:i',strtotime($row->schedule_date))}}" type="time" name="schedule_time" class="input w-full border mt-2 flex-1" name="schedule_time" placeholder="{{__('admin.schedule_time_placeholder')}}"  required>
                                                    </div>
                                                </div>
                                                
                                                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                    <input type="submit" class="button w-20 bg-theme-1 text-white" value="{{__('admin.create')}}">
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
                                                <a href="{{url('delete-quote')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
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
                            <td class="w-40" colspan="7">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
                {!! $quotes->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            
        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

             <p class="text-right"><?php if ($quotes->firstItem() != null) { ?> {{__('admin.showing')}} {{ $quotes->firstItem() }} {{__('admin.to')}} {{ $quotes->lastItem() }} {{__('admin.of')}} {{ $quotes->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>
    </div>


    <div class="modal" id="header-footer-modal-preview">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_quote')}}</h2>
            </div>
            <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide">
                
            </div>

            <form id="addcategoryform" method="post" action="{{url('add-update-quote')}}">

                @csrf

                <input type="hidden" id="quote_id" name="id" value="0">

                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.quote')}}</label>
                        <textarea name="quote" class="input w-full border mt-2 flex-1" required></textarea>
                    </div>
                </div>

                 <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.schedule_date')}}</label>
                        <input type="text" name="schedule_date" class="input w-full border mt-2 flex-1 datepicker" name="schedule_date" placeholder="{{__('admin.schedule_date_placeholder')}}" required>
                    </div>
                </div>

                 <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.schedule_time')}}</label>
                        <input type="time" name="schedule_time" class="input w-full border mt-2 flex-1" name="schedule_time" placeholder="{{__('admin.schedule_time_placeholder')}}">
                    </div>
                </div>
                
                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                    <input type="submit" class="button w-20 bg-theme-1 text-white" value="{{__('admin.create')}}">
                </div>
            </form>            
        </div>
    </div>
@endsection