@extends('../layout/' . $layout)
@if($page_name=='preview')
    @section('subhead')
        <title>{{__('admin.ads_media_preview')}} - {{setting('site_name')}}</title>
    @endsection
    @section('subcontent')
        @include('../layout/components/top-bar')
        <div class="grid grid-cols-12 gap-6 ">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2"></div>
            <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                <div class="intro-y box mt-5">
                    <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">
                        {{__('admin.ads_media_preview')}}
                        </h2>
                        <div class="w-full sm:w-auto flex items-center sm:ml-auto mt-3 sm:mt-0">
                            
                        </div>
                    </div>
                    <div class="p-5" id="responsive-slider">
                        <div class="preview">
                            <div class="mx-6 pb-8">
                                <div class="responsive-mode">
                                    @if(count($media))
                                        @foreach ($media as $row)
                                            <div class="h-32 px-2">
                                                <div class="h-full bg-gray-200 dark:bg-dark-1 rounded-md">
                                                    <h3 class="h-full font-medium flex items-center justify-center text-2xl">
                                                        
                                                        <img src="{{url('storage/ads').'/'.$row->file}}" class="rounded-md" alt="{{url('storage/ads').'/'.$row->file}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                                       
                                                    </h3>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>No media added yet.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    @section('subhead')
        <title>{{__('admin.ads_media_list')}} - {{setting('site_name')}}</title>
    @endsection
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />
    @section('subcontent')
        @include('../layout/components/top-bar')
        <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.ads_media_list')}}</h2>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2"></div>
            <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                            <th class="whitespace-no-wrap">{{__('admin.file')}}</th>
                            <th class="whitespace-no-wrap">{{__('admin.redirected_url')}}</th>
                            <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        </tr>
                    </thead>
                    <input type="hidden" id="ad_id" value="{{$ads->id}}">
                    <tbody id="tablecontents_ads_images">
                        @if(count($media))
                            <?php
                            $page = (isset($_GET['page']))?$_GET['page']:1;
                            if($page>1){
                                $i = $media->perPage() * ($media->currentPage() - 1) + 1;
                            }
                            else{
                                $i=1;
                            }
                            ?>
                            @foreach ($media as $row)
                                <tr class="intro-x row1" data-id="{{ $row->id }}">
                                    <td class="">
                                        {{$i}}
                                    </td>
                                    <?php
                                        if(file_exists(public_path()."/storage/ads/original/".$row->image) && $row->image!='') {
                                            $url = url('upload/category/original').'/'.$row->image;
                                        }else{
                                            $url = url('upload/no-image.png');
                                        }
                                    ?>
                                    <td>
                                        
                                        <a href="{{url('storage/ads').'/'.$row->file}}" class="image-popup" title="{{$row->file}}">
                                            <img src="{{url('storage/ads').'/'.$row->file}}" class="thumb-img-list" alt="{{url('storage/ads').'/'.$row->file}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';" style="width: 150px;">
                                        </a>
                                    </td>
                                    <td >@if($row->redirected_url!=''){{ $row->redirected_url }}@else -- @endif</td>
                                    <td class="table-report__action w-56">
                                        <div class="flex justify-center items-center">
                                            <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$row->id}}"  title="{{__('admin.edit_redirected_url')}}">
                                                <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                            </a>
                                        </div>
                                        <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                            <div class="modal__content">
                                                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                    <h2 class="font-medium text-base mr-auto">{{__('admin.edit_redirected_url')}}</h2>
                                                </div>
                                                <form id="edit_redirected_url_form_{{$row->id}}">
                                                    <input type="hidden" name="id" value="{{$row->id}}">
                                                    <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <label>{{__('admin.redirected_url')}}</label>
                                                            <input type="text" class="input w-full border mt-2 flex-1" name="redirected_url" placeholder="{{__('admin.edit_redirected_url_placeholder')}}" value="{{$row->redirected_url}}">
                                                        </div>
                                                    </div>
                                                    <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                        <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                        <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.update')}}" onclick="add_edit_redirected_url(event,'edit_redirected_url_form_{{$row->id}}')">
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
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
                    {!! $media->appends(request()->except('page'))->render() !!}
                </ul>
            </div>
            <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">

            </div>
            <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">
                <p class="text-right"><?php if ($media->firstItem() != null) { ?> {{__('admin.showing')}} {{ $media->firstItem() }} {{__('admin.to')}} {{ $media->lastItem() }} {{__('admin.of')}} {{ $media->total() }} {{__('admin.entries')}} <?php }?></p>
            </div>
        </div>
    @endsection
@endif
