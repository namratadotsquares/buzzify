@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.authors_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.authors_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">

    @can('author-create')

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            <a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_author')}}</a>
            <div class="hidden md:block mx-auto text-gray-600"> </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['name'])) value="{{$_GET['name']}}" @endif name="{{__('admin.name')}}" placeholder="{{__('admin.search_by_name')}}">
                        <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a> 
                        <input type="submit" id="search" class="hide" name="search">
                    </form>
                </div>
            </div>
        </div>
    @endcan
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.email')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.designation')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                    </tr>
                </thead>
               
                <tbody>
                    @if(count($author))
                        <?php
                        $page = (isset($_GET['page']))?$_GET['page']:1;
                        if($page>1){
                            $i = $author->perPage() * ($author->currentPage() - 1) + 1;
                        }
                        else{
                            $i=1;
                        }
                        ?>
                        @foreach ($author as $row)
                            <tr class="intro-x">
                                <td class="w-40">
                                    {{$i}}
                                </td>
                                <?php 
                                    if(file_exists(public_path()."/upload/author/original/".$row->image) && $row->image!='') { 
                                        $url = url('upload/author/original').'/'.$row->image;
                                    }else{
                                        $url = url('upload/no-image.png');
                                    }
                                ?>
                                <td>
                                    <a href="{{$url}}" class="image-popup" title="{{$row->name}}">
                                        <img src="{{$url}}" class="thumb-img-list" alt="{{$row->name}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </a>
                                </td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->designation }}</td>
                                <td>{{ date(setting('date_format'),strtotime($row->created_at)) }}</td>
                                <td class="w-40">
                                    @if($row->status==1)
                                        <a href="{{url('change-status-author/')}}/{{$row->id}}/0">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                            </div>
                                        </a> 
                                    @else
                                        <a href="{{url('change-status-author/')}}/{{$row->id}}/1">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                            </div>
                                        </a>
                                    @endif                               
                                </td>
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">

                                    @can('author-edit')

                                        <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$row->id}}">
                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                        </a>
                                    @endcan

                                    @can('author-delete')

                                        <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}">
                                            <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> {{__('admin.delete')}}
                                        </a>
                                    @endcan

                                    </div>
                                       <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                            <div class="modal__content">
                                                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                    <h2 class="font-medium text-base mr-auto">{{__('admin.edit_author')}}</h2>
                                                </div>

                                                <form id="editcategoryform_{{$row->id}}">
                                                    <input type="hidden" name="authorimage" id="authorimage{{$row->id}}" value="">
                                                    <input type="hidden" name="id"  value="{{$row->id}}">
                                                    <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <label>{{__('admin.name')}}</label>
                                                            <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.name_placeholder')}}" value="{{$row->name}}">
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <label>{{__('admin.email')}}</label>
                                                            <input type="text" class="input w-full border mt-2 flex-1" name="email" placeholder="{{__('admin.email_placeholder')}}" value="{{$row->email}}">
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <label>{{__('admin.designation')}}</label>
                                                            <input type="text" class="input w-full border mt-2 flex-1" name="designation" placeholder="{{__('admin.designation_placeholder')}}" value="{{$row->designation}}">
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <p>({{__('admin.thumb_image_resolution')}})</p>
                                                            <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('uploadBtn_{{$row->id}}')">
                                                            <input class="uploadBtn_{{$row->id}} hide" type="file" name="image" onchange="uploadauthorImage(this,'image_add','update','{{$row->id}}');" accept="image/jpg, image/jpeg"/>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="image_update_{{$row->id}}" src="{{$url}}" clasa="width-20" >
                                                        </div>
                                                    </div>                                            
                                                    <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                        <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                        <input type="button" class="button w-20 bg-theme-1 text-white" id="createBtn{{$row->id}}" value="Update" onclick="addUpdateAuthor(event,'editcategoryform_{{$row->id}}')">
                                                    </div>
                                                </form>            
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
                                                <a href="{{url('deleteAuthor')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                     @else
                        <tr class="intro-x text-center text-danger" >
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
                {!! $author->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">            
        </div>
        <div class="intro-y col-span-3 flex flex-wrap sm:flex-row sm:flex-no-wrap items-right">
            <p><?php if ($author->firstItem() != null) { ?> {{__('admin.showing')}} {{ $author->firstItem() }} {{__('admin.to')}} {{ $author->lastItem() }} {{__('admin.of')}} {{ $author->total() }} {{__('admin.entries')}} <?php }?></p>
        </div>
    </div>   
    <div class="modal" id="header-footer-modal-preview">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_author')}}</h2>
            </div>
                                            
            <form id="addAuthorForm">
                <input type="hidden" name="authorimage" id="authorimage" value="">
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.name')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.name_placeholder')}}">
                    </div>
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.email')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="email" placeholder="{{__('admin.email_placeholder')}}">
                    </div>
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.designation')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="designation" placeholder="{{__('admin.designation_placeholder')}}">
                    </div>
                    <div class="col-span-12 sm:col-span-12">
                        <p>({{__('admin.thumb_image_resolution')}})</p>
                        <input type="button" class="button w-30 bg-theme-1 text-white" value="Upload Image" onclick="triggerFileInput('uploadBtn')">
                        <input class="uploadBtn hide" type="file" name="image" onchange="uploadauthorImage(this,'image_add','add',0);" accept="image/jpg, image/jpeg"/>
                    </div>
                    <div class="col-span-12 sm:col-span-12">
                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="image_add" src="" class="width-20" >
                    </div>
                </div>             
                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                    <input type="button" class="button w-20 bg-theme-1 text-white" id="createBtn" value="{{__('admin.create')}}" onclick="addUpdateAuthor(event,'addAuthorForm')">
                </div>
            </form>            
        </div>
    </div>
@endsection