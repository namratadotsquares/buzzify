@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.live_new_list')}} - {{setting('site_name')}}</title>
@endsection

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.live_new_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            @can('live-news-create')
            <a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" data-backdrop="static" data-keyboard="false" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_live_new')}}</a>
            @endcan

            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['company_name'])) value="{{$_GET['company_name']}}" @endif name="company_name" placeholder="{{__('admin.search_by_company')}}">
                        <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a>
                        <input type="submit" id="search" class="hide" name="search">
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
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.company')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.url')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        @can('live-news-status')
                        <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                        @endcan
                        @if(Gate::check('live-news-edit') || Gate::check('live-news-delete') || Gate::check('live-news-translate'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endcan
                    </tr>
                </thead>

                <tbody >
                    @if(count($News))
                        <?php
                        $page = (isset($_GET['page']))?$_GET['page']:1;
                        if($page>1){
                            $i = $News->perPage() * ($News->currentPage() - 1) + 1;
                        }
                        else{
                            $i=1;
                        }
                        ?>
                        @foreach ($News as $row)
                            <tr class="intro-x row1" data-id="{{ $row->id }}" data-type="news">
                                <td class="">
                                    {{$i}}
                                </td>

                                <?php
                                    if(file_exists(public_path()."/upload/company-logo/original/".$row->image) && $row->image!='') {
                                        $url = url('upload/company-logo/original').'/'.$row->image;
                                    }else{
                                        $url = url('upload/no-image.png');
                                    }
                                ?>

                                <td>
                                    <a href="{{$url}}" class="image-popup" title="{{$row->image}}">
                                        <img src="{{$url}}" class="thumb-img-list" alt="{{$row->image}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </a>
                                </td>

                                <td>{{ $row->company_name }}</td>
                                <td>{{ $row->url }}</td>
                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif</td>

                                @can('live-news-status')
                                <td class="w-40">
                                    @if($row->status==1)
                                        <a href="{{url('change-status-live-news/')}}/{{$row->id}}/0">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                            </div>
                                        </a>
                                    @else
                                        <a href="{{url('change-status-live-news/')}}/{{$row->id}}/1">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                            </div>
                                        </a>
                                    @endif
                                </td>
                                @endcan
                                
                                @if(Gate::check('live-news-edit') || Gate::check('live-news-delete') || Gate::check('live-news-translate'))
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                    @can('live-news-translate')
                                        <a class="flex items-center mr-3 text-theme-3 font-size23" href="javascript:;" data-toggle="modal" data-target="#translate_live_new_edit_{{$row->id}}"  title="{{__('admin.translate')}}" data-backdrop="static" data-keyboard="false">
                                            <i data-feather="edit-3" class="w-4 h-4 mr-1"></i>
                                            <small class="ml-2 font-size15">{{__('admin.translate')}}</small>
                                        </a>
                                    @endcan
                                    @can('live-news-edit')
                                        <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$row->id}}"  title="{{__('admin.edit')}}">
                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                        </a>
                                    @endcan
                                    @can('live-news-delete')
                                        <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}"  title="{{__('admin.delete')}}">
                                            <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> {{__('admin.delete')}}
                                        </a>
                                    @endcan
                                    </div>

                                     <div data-backdrop="static" data-keyboard="false" class="modal" id="translate_live_new_edit_{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                <h2 class="font-medium text-base mr-auto">{{__('admin.translate_live_news')}}</h2>
                                            </div>

                                            <form id="translateLiveNewsform_{{$row->id}}">
                                                <input type="hidden" name="live_news_id" value="{{$row->id}}">
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label class="mb-2">{{__('admin.language')}}</label>

                                                        <select data-placeholder="{{__('admin.select_language')}}" name="language_code" class="tail-select w-full " onchange="getLiveNewsTranslation('{{$row->id}}',this.value)">
                                                            <option value="" >{{__('admin.select_language')}}</option>
                                                            @foreach($languages as $lang)
                                                                <option @if($row->language_code == $lang->language) selected @endif  value="{{$lang->language}}">{{$lang->name}}</option>
                                                            @endforeach
                                                        </select>

                                                    </div>
                                                </div>
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.company')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1 height-40" name="company_name" id="company_name_{{$row->id}}" placeholder="{{__('admin.company_placeholder')}}" value="{{$row->company_name_trans}}">
                                                    </div>
                                                </div>

                                                 <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.youtube_url')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1" name="url" id="youtube_url_{{$row->id}}" placeholder="{{__('admin.youtube_url_placeholder')}}" value="{{$row->url_trans}}">
                                                    </div>
                                                </div>

                                                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                  @can('live-news-edit')
                                                        <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.update')}}" onclick="translateLiveNews(event,'translateLiveNewsform_{{$row->id}}')">
                                                    @endcan
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                <h2 class="font-medium text-base mr-auto">{{__('admin.edit_live_news')}}</h2>
                                            </div>

                                            <form id="editcategoryform_{{$row->id}}">
                                                <input type="hidden" name="id" value="{{$row->id}}">
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.company')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1" name="company_name" placeholder="{{__('admin.company_placeholder')}}" value="{{$row->company_name}}">
                                                    </div>
                                                </div>
                                                 <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.youtube_url')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1" name="url" placeholder="{{__('admin.youtube_url_placeholder')}}" value="{{$row->url}}">
                                                    </div>
                                                </div>
                                                <?php
                                                    if(file_exists(public_path()."/upload/company-logo/original/".$row->image) && $row->image!='') {
                                                        $bannerurl = url('upload/company-logo/original').'/'.$row->image;
                                                    }else{
                                                        $bannerurl = url('upload/no-image.png');
                                                    }
                                                ?>
                                                <div class="p-5 grid col-span-12 sm:col-span-4">
                                                    <input type="hidden" name="thumb_image" id="thumb_image_{{$row->id}}" value="{{$row->image}}">
                                                    <label>({{__('admin.thumb_image_resolution')}})</label>
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_thumb_image')}}" onclick="triggerFileInput('thumbimageuploadBtn_{{$row->id}}')">
                                                        <input class="thumbimageuploadBtn_{{$row->id}} hide" type="file" name="thumbimage" onchange="uploadLogo(this,'thumbimage_image_add_{{$row->id}}','add','{{$row->id}}');" accept="image/jpg, image/jpeg, image/png"/>
                                                    </div>
                                                    <div class="col-span-12 sm:col-span-12 mt-3" >
                                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="thumbimage_image_add_{{$row->id}}" src="{{$bannerurl}}" class="width-30" >
                                                    </div>
                                                </div>


                                                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                   @can('live-news-edit')
                                                        <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.update')}}" onclick="add_livenews(event,'editcategoryform_{{$row->id}}')">
                                                    @endcan
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
                                                <a href="{{url('delete-live-news')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger" >
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
                {!! $News->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">

        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

            <p class="text-right"><?php if ($News->firstItem() != null) { ?> {{__('admin.showing')}} {{ $News->firstItem() }} {{__('admin.to')}} {{ $News->lastItem() }} {{__('admin.of')}} {{ $News->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>
    </div>
    <div class="modal" id="header-footer-modal-preview" tabindex="-1" role="dialog">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_live_new')}}</h2>
            </div>
            <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide">

            </div>
            <form id="addcategoryform">
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.company')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="company_name" placeholder="{{__('admin.company_placeholder')}}">
                    </div>
                </div>
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.youtube_url')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="url" placeholder="{{__('admin.youtube_url_placeholder')}}">
                    </div>
                </div>
                <div class="p-5 grid col-span-12 sm:col-span-4">
                    <input type="hidden" name="thumb_image" id="thumb_image" value="">
                    <label>({{__('admin.thumb_image_resolution')}})</label>
                    <div class="col-span-12 sm:col-span-12">
                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_thumb_image')}}" onclick="triggerFileInput('thumbimageuploadBtn')">
                        <input class="thumbimageuploadBtn hide" type="file" name="thumbimage" onchange="uploadLogo(this,'thumbimage_image_add','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                    </div>
                    <div class="col-span-12 sm:col-span-12 mt-3" >
                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="thumbimage_image_add" src="" class="width-30">
                    </div>
                </div>
                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                   @can('live-news-create')
                        <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.create')}}" onclick="add_livenews(event,'addcategoryform')">
                    @endcan
                </div>
            </form>
        </div>
    </div>

@endsection
