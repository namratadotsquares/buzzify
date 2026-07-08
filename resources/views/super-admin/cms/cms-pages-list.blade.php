@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.cms_pages_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.cms_pages_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
        @can('cms-pages-create')
        <a href="{{route('addcms',['side-menu','light'])}}" class="button text-white bg-theme-1 shadow-md mr-2">Add CMS</a>
        @endcan

        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.title')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        @if(Gate::check('cms-pages-edit') || Gate::check('cms-pages-delete') || Gate::check('cms-pages-translate'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endif
                    </tr>
                </thead>
              
                <tbody>
                    @if(count($cms))
                        <?php
                        $page = (isset($_GET['page']))?$_GET['page']:1;
                        if($page>1){
                            $i = $cms->perPage() * ($cms->currentPage() - 1) + 1;
                        }
                        else{
                            $i=1;
                        }
                        ?>
                        @foreach ($cms as $row)
                            <tr class="intro-x">
                                <td class="w-40">
                                    {{$i}}
                                </td>
                         

                                <?php 
                                    if(file_exists(public_path()."/upload/cms/original/".$row->image) && $row->image!='') { 
                                        $url = url('upload/cms/original').'/'.$row->image;
                                    }else{
                                        $url = url('upload/no-image.png');
                                    }
                                ?>

                                <td>
                                    <a href="{{$url}}" class="image-popup" title="{{$row->image}}">
                                        <img src="{{$url}}" class="thumb-img-list" alt="{{$row->image}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </a>
                                </td>


                                <td>{{ $row->title }}</td>
                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@elseif($row->updated_at!=null){{ date(setting('date_format'),strtotime($row->updated_at))}} @else -- @endif</td>
                               @if(Gate::check('cms-pages-edit') || Gate::check('cms-pages-delete') || Gate::check('cms-pages-translate'))
                                <td class="table-report__action w-56">

                                        <div class="flex justify-center items-center">
                                            @can('cms-pages-translate')
                                            <a class="flex items-center mr-3 text-theme-3 font-size23" href="{{url('edit-cms-page-translation')}}/{{$layout}}/{{$theme}}/{{$row->id}}"  title="{{__('admin.translate')}}">
                                            <i data-feather="edit-3" class="w-4 h-4 mr-1"></i>
                                            <small class="font-size15 ml-2">{{__('admin.translate')}}</small>
                                            </a>
                                            @endcan

                                            @can('cms-pages-edit')
                                                <a class="flex items-center mr-3" href="{{url('edit-cms-page')}}/{{$layout}}/{{$theme}}/{{$row->id}}"  title="{{__('admin.edit')}}">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                                </a>
                                            @endcan
                                             @can('cms-pages-delete')
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
                                                <a href="{{url('/delete-cms')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
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
                            <td class="w-40" colspan="5">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
       
    </div>

@endsection