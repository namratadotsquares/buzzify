@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.sub_admin_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')
    <style type="text/css">
        .padiing-5{
            padding-top: 0.7rem;
            padding-right: 1.25rem;
            padding-bottom: 0.7rem;
            padding-left: 1.25rem;
        }
    </style>

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.sub_admin_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
                @can('sub-admin-create')
                    <a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_sub_admin')}}</a>
                @endcan
                @can('role-list')
                    <!-- <a href="{{url('roles')}}/{{$layout}}/{{$theme}}/4/" class="button text-white bg-theme-6 shadow-md mr-2">{{__('admin.set_sub_admin_permission')}}</a> -->
                    <a href="{{ route('roles.index') }}" class="button text-white bg-theme-6 shadow-md mr-2">{{__('admin.manage_roles')}}</a>
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
            <button class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>

            </div>
        
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.email')}}</th>
                        <th class="whitespace-no-wrap">Role</th>
                        <th class="whitespace-no-wrap" style="width: 280px;">{{__('admin.created_at')}}</th>
                        @can('sub-admin-status')
                        <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                        @endcan
                        @if(Gate::check('sub-admin-edit') || Gate::check('sub-admin-delete'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endif
                    </tr>
                </thead>
             
                <tbody>
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
                            <tr class="intro-x">
                                <td class="w-40">
                                    {{$i}}
                                </td>

                                <?php 
                                    if(file_exists(public_path()."/upload/user/".$row->photo) && $row->photo!='') { 
                                        $url = url('upload/user').'/'.$row->photo;
                                    }else{
                                        $url = url('upload/no-image.png');
                                    }
                                ?>

                                <td>
                                    <a href="{{$url}}" class="image-popup" title="{{$row->photo}}">
                                        <img src="{{$url}}" class="thumb-img-list" alt="{{$row->photo}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </a>
                                </td>


                                <td>{{ $row->name }}</td>
                                <td>{{ $row->email }}</td>
                                <td>
                                    {{isset($row->roleName)?$row->roleName->name:'-'}}
                                </td>
                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif</td>

                                @can('sub-admin-status')
                                <td class="w-40">
                                    @if($row->active==1)
                                        <a href="{{url('change-status-sub-admin/')}}/{{$row->id}}/0">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                            </div>
                                        </a> 
                                    @else
                                        <a href="{{url('change-status-sub-admin/')}}/{{$row->id}}/1">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                            </div>
                                        </a>
                                    @endif                               
                                </td>
                                @endcan
                                @if(Gate::check('sub-admin-edit') || Gate::check('sub-admin-delete'))
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                    @can('sub-admin-edit')
                                        <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$row->id}}"  title="{{__('admin.edit')}}">
                                            <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                        </a>
                                    @endcan

                                    @can('sub-admin-delete')

                                        <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}"  title="{{__('admin.delete')}}">
                                            <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> {{__('admin.delete')}}
                                        </a>
                                    @endcan
                                    
                                    </div>
                                    <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                <h2 class="font-medium text-base mr-auto">{{__('admin.edit_sub_admin')}}</h2>
                                            </div>
                                            <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>
                    

                                            <form id="editsubadminform_{{$row->id}}">
                                                <input type="hidden" name="id" value="{{$row->id}}">

                                                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.name')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.name_placeholder')}}" value="{{$row->name}}">
                                                    </div>
                                                </div>
                                                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.email')}}</label>
                                                        <input type="email" class="input w-full border mt-2 flex-1" name="email" placeholder="{{__('admin.admin_placeholder')}}" value="{{$row->email}}">
                                                    </div>
                                                </div>
                            
                                                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.assign_role')}}</label>
                                                        <select data-placeholder="{{__('admin.select_role')}}" name="role_id" class="tail-select w-full">
                                                            <option value="" >{{__('admin.select_role')}}</option>
                                                            @foreach($roles as $role)
                                                            <option @if($row->role_id == $role->id) selected @endif  value="{{$role->id}}">{{$role->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.password')}}</label>
                                                        <input type="password" class="input w-full border mt-2 flex-1" name="password" placeholder="{{__('admin.password_placeholder')}}" value="">
                                                    </div>
                                                </div>

                                                <?php 
                                                    if(file_exists(public_path()."/upload/user/".$row->photo) && $row->photo!='') { 
                                                        $imageurl = url('upload/user').'/'.$row->photo;
                                                    }else{
                                                        $imageurl = url('upload/no-image.png');
                                                    }
                                                ?>
                                                <div class="padiing-5 grid col-span-12 sm:col-span-4">
                                                    <input type="hidden" name="image" id="image_{{$row->id}}" value="{{$row->photo}}">
                                                    <label>({{__('admin.thumb_image_resolution')}})</label>
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('imageuploadBtn_{{$row->id}}')">
                                                        <input class="imageuploadBtn_{{$row->id}} hide"  type="file" name="thumbimage" onchange="uploadSubadminThumbImage(this,'image_add_{{$row->id}}','add','{{$row->id}}');" accept="image/jpg, image/jpeg, image/png"/>
                                                    </div>
                                                    <div class="col-span-12 sm:col-span-12 mt-3" >
                                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="image_add_{{$row->id}}" src="{{$imageurl}}" class="width-30" >
                                                    </div>
                                                </div>
                                                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                    <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.update')}}" onclick="add_subadmin(event,'editsubadminform_{{$row->id}}')">
                                                </div>
                                            </form>            
                                        </div>
                                    </div>
                                    <div class="modal" id="delete-confirmation-modal-{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="padiing-5 text-center">
                                                <i data-feather="x-circle" class="w-16 h-16 text-theme-6 mx-auto mt-3"></i>
                                                <div class="text-3xl mt-5">{{__('admin.sure_warning')}}</div>
                                                <div class="text-gray-600 mt-2">{{__('admin.delete_warning')}}</div>
                                            </div>
                                            <div class="px-5 pb-8 text-center">
                                                <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1">{{__('admin.cancel')}}</button>
                                                <a href="{{url('delete-sub-admin')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
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
                            <td class="w-40" colspan="8">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
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
    <div class="modal" id="header-footer-modal-preview">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_sub_admin')}}</h2>
            </div>
            <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>
            <form id="addsubadminform">
                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.name')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.name_placeholder')}}">
                    </div>
                </div>
                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.email')}}</label>
                        <input type="email" class="input w-full border mt-2 flex-1" name="email" placeholder="{{__('admin.email_placeholder')}}">
                    </div>
                </div>
                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.assign_role')}}</label>
                        <select data-placeholder="{{__('admin.select_role')}}" name="role_id" class="tail-select w-full">
                            <option value="" >{{__('admin.select_role')}}</option>
                            @foreach($roles as $role)
                            <option value="{{$role->id}}">{{$role->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="padiing-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.password')}}</label>
                        <input type="password" class="input w-full border mt-2 flex-1" name="password" placeholder="{{__('admin.password_placeholder')}}">
                    </div>
                </div>

                <div class="padiing-5 grid col-span-12 sm:col-span-4">
                    <input type="hidden" name="image" id="image" value="">
                    <label>({{__('admin.thumb_image_resolution')}})</label>
                    <div class="col-span-12 sm:col-span-12">
                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('imageuploadBtn')">
                        <input class="imageuploadBtn hide" type="file" name="image" onchange="uploadSubadminThumbImage(this,'image_add','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                    </div>
                    <div class="col-span-12 sm:col-span-12 mt-3" >
                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="image_add" src="" class="width-30" >
                    </div>
                </div>

                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1" onclick="resetForm('addsubadminform')">{{__('admin.cancel')}}</button>
                    <input type="button" class="button w-20 bg-theme-1 text-white" id="createBtn" value="{{__('admin.create')}}" onclick="add_subadmin(event,'addsubadminform')">
                </div>
            </form>            
        </div>
    </div>
@endsection