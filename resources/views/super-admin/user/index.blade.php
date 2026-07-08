@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.users_list')}} - {{setting('site_name')}}</title>
@endsection
@section('subcontent')
    @include('../layout/components/top-bar')
    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.users_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap mt-2">
            <form id="bulkDeleteFormUsers" method="POST" action="{{ url('/delete-multiple-user') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <button type="button" id="deleteSelectedUsers" class="button w-48 bg-theme-6 text-white mr-2" style="margin-top:6px">{{ __('admin.delete_selected') ?? 'Delete Selected' }}</button>
            </form>
            <div class="hidden md:block text-gray-600" style="margin-left: 2%;margin-right: 2%;">
                <form id="filter" class="form-group" method="get" >
                   <select data-placeholder="Select Category" name="category[]" class="tail-select w-full" multiple="">
                            <!--<option value="" >{{__('admin.Category')}}</option>-->
                            @foreach(DB::table('category')->where('status',1)->whereNull('deleted_at')->get() as $item)
                            <option value="{{$item->id}}">{{$item->name}}</option>
                        @endforeach
                   </select>
                </form>
            </div>
            <div class="hidden md:block text-gray-600" style="margin-left: 2%;margin-right: 2%;">
                <button type="submit" class="button w-30 bg-theme-1 text-white" onclick="$('#filter').submit()">Filter</button>
            </div>
            <div class="w-full sm:w-auto sm:mt-0 sm:ml-auto md:ml-0 right">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['name'])) value="{{$_GET['name']}}" @endif name="name" placeholder="{{__('admin.search_by_name')}}">
                        <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a>
                        <input type="submit" id="search" class="hide" name="search">
                    </form>
                </div>
            </div>
             <div class="hidden md:block text-gray-600" style="margin-left: 2%;margin-right: 2%;">
                <button class="button w-30 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>
            </div>

        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap"><input type="checkbox" id="select_all_users" /></th>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.phone')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.email')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.login_from')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        @can('user-status')
                        {{-- <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th> --}}
                        @endcan
                        @if(Gate::check('user-delete') || Gate::check('user-personalization'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @if(count($user))
                        @foreach ($user as $row)
                            <tr class="intro-x">
                                        <td class="w-10">
                                            <input type="checkbox" class="row_checkbox_user" name="ids[]" form="bulkDeleteFormUsers" value="{{ $row->id }}" />
                                        </td>
                                        <td class="w-40">
                                            {{$i}}
                                        </td>

                                <?php
                                    if(file_exists(public_path()."/user/".$row->image) && $row->image!='') {
                                        $url = url('user').'/'.$row->image;
                                    }else{
                                        $url = url('upload/no-image.png');
                                    }
                                ?>

                                <td>
                                    <a href="{{$row->photo}}" class="image-popup" title="{{$row->name}}">
                                        <img src="{{$row->photo}}" class="thumb-img-list" alt="{{$row->name}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </a>
                                </td>

                                <td>{{ $row->name }}</td>
                                <td> @if($row->phone != '') {{ $row->phone }} @else -- @endif</td>
                                <td>{{ $row->email }}</td>

                                <td> @if($row->login_from != '') {{ ucfirst($row->login_from) }} @else Manual @endif</td>

                                <td>{{ date(setting('date_format'),strtotime($row->created_at)) }}</td>
                                @can('user-status')
                                {{-- <td class="w-40">
                                    @if($row->active==1)
                                        <a href="{{url('change-status-user/')}}/{{$row->id}}/0">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                            </div>
                                        </a>
                                    @else
                                        <a href="{{url('change-status-user/')}}/{{$row->id}}/1">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                            </div>
                                        </a>
                                    @endif
                                </td> --}}
                                @endcan
                                @if(Gate::check('user-delete') || Gate::check('user-personalization'))
                                <td class="table-report__action w-56">

                                    @can('user-delete')

                                    <div class="flex justify-center items-center">
                                        <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}"  title="{{__('admin.delete')}}">
                                            <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> {{__('admin.delete')}}
                                        </a>
                                    </div>

                                    @endcan
                                        @can('user-personalization')
                                        <div class="flex justify-center items-center">
                                            <a href="{{url('personalization-user')}}/{{$row->id}}" class="button w-14 bg-theme-6 text-white">Personalization</a>

                                        </div>
                                        @endcan


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
                                                            <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('uploadBtn_{{$row->id}}')">
                                                            <input class="uploadBtn_{{$row->id}} hide" type="file" name="image" onchange="uploadauthorImage(this,'image_add','update','{{$row->id}}');" accept="image/jpg, image/jpeg, image/png"/>
                                                        </div>

                                                        <div class="col-span-12 sm:col-span-12">
                                                            <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="image_update_{{$row->id}}" src="{{$url}}" class="width-20" >
                                                        </div>
                                                    </div>

                                                    <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                        <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                        <input type="button" class="button w-20 bg-theme-1 text-white" id="createBtn{{$row->id}}" value="{{__('admin.update')}}" onclick="addUpdateAuthor(event,'editcategoryform_{{$row->id}}')">
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
                                                <a href="{{url('delete-user')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
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
                {!! $user->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">

        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

            <p class="text-right"><?php if ($user->firstItem() != null) { ?> {{__('admin.showing')}} {{ $user->firstItem() }} {{__('admin.to')}} {{ $user->lastItem() }} {{__('admin.of')}} {{ $user->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>
    </div>
    <script>
        // Users table select all
        var selectAllUsers = document.getElementById('select_all_users');
        if (selectAllUsers) {
            selectAllUsers.addEventListener('change', function() {
                var rows = document.querySelectorAll('.row_checkbox_user');
                for (var i = 0; i < rows.length; i++) {
                    rows[i].checked = this.checked;
                }
            });
        }

        var deleteBtnUsers = document.getElementById('deleteSelectedUsers');
        if (deleteBtnUsers) {
            deleteBtnUsers.addEventListener('click', function() {
                var rows = document.querySelectorAll('.row_checkbox_user:checked');
                if (rows.length === 0) {
                    alert('{{ __('admin.select_at_least_one_record') ?? 'Please select at least one record.' }}');
                    return;
                }
                if (confirm('{{ __('admin.sure_warning') ?? 'Are you sure?' }}')) {
                    document.getElementById('bulkDeleteFormUsers').submit();
                }
            });
        }
    </script>
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
                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('uploadBtn')">
                        <input class="uploadBtn hide" type="file" name="image" onchange="uploadauthorImage(this,'image_add','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                    </div>
                    <div class="col-span-12 sm:col-span-12">
                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="image_add" src=""  class="width-20" >
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
