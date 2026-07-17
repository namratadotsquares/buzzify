@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.social_list')}} - {{setting('site_name')}}</title>
@endsection



@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.settings_list')}}</h2>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 xxl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box bg-theme-1 p-5 mt-6">
                <div class="border-theme-3 dark:border-dark-5 mt-5 text-white">
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/site-setting" class="flex items-center px-3 py-2 rounded-md <?php if(Request::segment(4) == 'site-setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i> {{__('admin.site_settings')}} </a>

                     <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/global" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'global') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="phone"></i> {{__('admin.mobile_app_settings')}} </a> 

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/local" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'local') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="box"></i> {{__('admin.localization_settings')}} </a>
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/notification" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'notification') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="send"></i> {{__('admin.push_notification_settings')}} </a>


                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/social" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'social') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="activity"></i>{{__('admin.social_media_settings')}} </a> 

                    {{-- <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/font-setting" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'font-setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="type"></i>{{__('admin.font_setting')}} </a>  --}}
                  
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/live-News&E-News" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'live-News&E-News') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.live_enews_settings')}}</a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/admob" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'admob') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.admob')}}</a>

                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/fb_ad_settings" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'fb_ad_settings') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.fb_ad_settings')}}</a>
                    
                    <a href="{{url('/setting/')}}/{{$layout}}/{{$theme}}/wallet_setting" class="flex items-center px-3 py-2 mt-2 rounded-md <?php if(Request::segment(4) == 'wallet_setting') { echo ' bg-theme-22 dark:bg-dark-1 font-medium'; } ?>"> <i class="w-4 h-4 mr-2" data-feather="settings"></i>{{__('admin.wallet_setting')}}</a>

                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 xxl:col-span-9">
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                    <h2 class="font-medium text-base mr-auto">
                        @if(isset($title)){{$title}}@endif
                    </h2>
                    <a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_social')}}</a>
                  
                </div>
                <div class="p-5">
                    <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                        <table class="table table-report -mt-2">
                            <thead>
                                <tr>
                                    <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                                    <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                                    <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                                    <!-- <th class="whitespace-no-wrap">Icon</th> -->
                                    <th class="whitespace-no-wrap width-20"  >{{__('admin.created_at')}}</th>
                                    <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                                    <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                                </tr>
                            </thead>
                          
                            <tbody id="tablecontents">
                                @if(count($category))
                                    <?php
                                        $page = (isset($_GET['page']))?$_GET['page']:1;
                                        if($page>1){
                                            $i = $category->perPage() * ($category->currentPage() - 1) + 1;
                                        }
                                        else{
                                            $i=1;
                                        }
                                    ?>
                                    @foreach ($category as $row)
                                        <tr class="intro-x row1" data-id="{{ $row->id }}">
                                            <td class="w-40">
                                                {{$i}}
                                            </td>
                                            <td>{{ $row->name }}</td>
                                            <td>
                                                <?php
                                                    if(isset($row->image) && file_exists(public_path()."/upload/social/".$row->image) && $row->image!='') {
                                                        $socialurl = url('upload/social').'/'.$row->image;
                                                    }else{
                                                        $socialurl = url('upload/no-image.png');
                                                    }
                                                ?>
                                                <img src="{{$socialurl}}" class="thumb-img-list" alt="{{$row->image}}" style="width: 40px; height: 40px; object-fit: contain;" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                            </td>


                                            <!-- <td><i class="fa {{ $row->icon }}"></i></td> -->
                                            
                                            <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif</td>
                                            <td class="w-40">
                                                @if($row->status==1)
                                                    <a href="{{url('change-status-social/')}}/{{$row->id}}/0">
                                                        <div class="flex items-center justify-center text-theme-9">
                                                            <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                                        </div>
                                                    </a> 
                                                @else
                                                    <a href="{{url('change-status-social/')}}/{{$row->id}}/1">
                                                        <div class="flex items-center justify-center text-theme-6">
                                                            <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                                        </div>
                                                    </a>
                                                @endif                               
                                            </td>
                                            <td class="table-report__action w-56">
                                                <div class="flex justify-center items-center">
                                                    <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$row->id}}">
                                                        <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                                    </a>
                                                    <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}">
                                                        <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> {{__('admin.delete')}}
                                                    </a>
                                                </div>
                                                <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                                    <div class="modal__content">
                                                        <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                            <h2 class="font-medium text-base mr-auto">{{__('admin.edit_social')}}</h2>
                                                        </div>
                                                

                                                        <form id="editsocialform_{{$row->id}}">
                                                            <input type="hidden" name="id" value="{{$row->id}}">
                                                            <div class="grid grid-cols-12 gap-4 row-gap-3 social-design">
                                                                <div class="col-span-12 sm:col-span-12">
                                                                    <label>{{__('admin.name')}}</label>
                                                                    <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.name_placeholder')}}" value="{{$row->name}}">
                                                                </div>
                                                            </div>
                                                            <div class="social-design grid grid-cols-12 gap-4 row-gap-3">
                                                                <div class="col-span-12 sm:col-span-12">
                                                                    <label>{{__('admin.url')}}</label>
                                                                    <input type="text" class="input w-full border mt-2 flex-1" name="url" placeholder="{{__('admin.url_placeholder')}}" value="{{$row->url}}">
                                                                </div>
                                                            </div>
                                                            <div class="social-design grid grid-cols-12 gap-4 row-gap-3">
                                                                <div class="col-span-12 sm:col-span-12">
                                                                    <label>{{__('admin.icon')}}</label>
                                                                    <input type="text" class="input w-full border mt-2 flex-1" name="icon" placeholder="{{__('admin.icon_placeholder')}}" value="{{$row->icon}}">
                                                                </div>
                                                            </div>
                                                            <?php
                                                                if(isset($row->image) && file_exists(public_path()."/upload/social/".$row->image) && $row->image!='') {
                                                                    $socialurl = url('upload/social').'/'.$row->image;
                                                                }else{
                                                                    $socialurl = url('upload/no-image.png');
                                                                }
                                                            ?>
                                                            <div class="social-design grid grid-cols-12 gap-4 row-gap-3">
                                                                <div class="col-span-12 sm:col-span-12">
                                                                    <label>{{__('admin.image')}} <span class="text-danger">*</span></label>
                                                                    <input type="hidden" name="thumb_image" id="social_image_{{$row->id}}" value="{{$row->image}}">
                                                                    <div class="mt-2 flex items-center">
                                                                        <input type="button" class="button w-30 bg-theme-1 text-white mr-3" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('socialimageuploadBtn_{{$row->id}}')">
                                                                        <input class="socialimageuploadBtn_{{$row->id}} hide" type="file" name="social_image_file" onchange="uploadSocialImage(this,'social_image_preview_{{$row->id}}','update','{{$row->id}}');" accept="image/jpg, image/jpeg, image/png"/>
                                                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';" id="social_image_preview_{{$row->id}}" src="{{$socialurl}}" class="width-30" style="max-height: 100px; object-fit: contain;">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                                <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                                <input type="button" id="createBtn{{$row->id}}" class="button w-20 bg-theme-1 text-white" value="{{__('admin.update')}}" onclick="add_social(event,'editsocialform_{{$row->id}}')">
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
                                                            <a href="{{url('delete-social')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
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

                        <ul class="pagination">
                            {!! $category->appends(request()->except('page'))->render() !!}
                        </ul>

                    </div>
                    <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
                        
                    </div>
                    <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">
                        
                        <p class="text-right"><?php if ($category->firstItem() != null) { ?> {{__('admin.showing')}} {{ $category->firstItem() }} {{__('admin.to')}} {{ $category->lastItem() }} {{__('admin.of')}} {{ $category->total() }} {{__('admin.entries')}} <?php }?></p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="header-footer-modal-preview">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_social')}}</h2>
            </div>
            

            <form id="addsocialform">
                <div class="grid grid-cols-12 gap-4 row-gap-3 social-design">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.name')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.name_placeholder')}}">
                    </div>
                </div>
                <div class="social-design grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.url')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="url" placeholder="{{__('admin.url_placeholder')}}">
                    </div>
                </div>
                <div class="social-design grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.icon')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="icon" placeholder="{{__('admin.icon_placeholder')}}">
                    </div>
                </div>
                <div class="social-design grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.image')}} <span class="text-danger">*</span></label>
                        <input type="hidden" name="thumb_image" id="social_image" value="">
                        <div class="mt-2 flex items-center">
                            <input type="button" class="button w-30 bg-theme-1 text-white mr-3" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('socialimageuploadBtn')">
                            <input class="socialimageuploadBtn hide" type="file" name="social_image_file" onchange="uploadSocialImage(this,'social_image_preview','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                            <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';" id="social_image_preview" src="{{url('upload/no-image.png')}}" class="width-30" style="max-height: 100px; object-fit: contain;">
                        </div>
                    </div>
                </div>

                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                    <input type="button" id="createBtn" class="button w-20 bg-theme-1 text-white" value="{{__('admin.create')}}" onclick="add_social(event,'addsocialform')">
                </div>
            </form>            
        </div>
    </div> 

@endsection