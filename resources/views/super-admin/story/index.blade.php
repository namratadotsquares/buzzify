@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.story')}} - {{setting('site_name')}}</title>
@endsection

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.story')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            
            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <!--<input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['paper_name'])) value="{{$_GET['paper_name']}}" @endif name="paper_name" placeholder="{{__('admin.search_by_paper')}}">-->
                <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['name'])) value="{{$_GET['name']}}" @endif name="name" placeholder="Search by name">
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
                        <!--<th class="whitespace-no-wrap">{{__('admin.file')}}</th>-->
                        <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.email')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.phone')}}</th>
                        @can('epaper-status')
                        <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                        @endcan
                        
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        @if(Gate::check('epaper-edit') || Gate::check('epaper-delete') || Gate::check('epaper-translate'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endcan
                    </tr>
                </thead>

                <tbody id="">
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
                            <tr class="intro-x row1" data-id="{{ $row->id }}">
                                <td class="">
                                    {{$i}}
                                </td>

                                <?php
                                    if( $row->file!='') {
                                        $url = $row->file;
                                    }else{
                                        $url = url('upload/no-image.png');
                                    }


                                ?>

                                <!--<td>-->
                                <!--    <a href="{{$url}}" class="image-popup" title="{{$row->file}}">-->
                                <!--        <img src="{{$url}}" class="thumb-img-list" alt="{{$row->file}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">-->
                                <!--    </a>-->
                                <!--</td>-->


                                <td>{{ $row->name }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->phone }}</td>
                                
                               
                                @can('epaper-status')
                                <td class="w-40">
                                    @if($row->status==0)
                                        <a href="#">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.pending')}}
                                            </div>
                                        </a>
                                    @elseif($row->status==1)
                                        <a href="#">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.accept')}}
                                            </div>
                                        </a>
                                    @else
                                        <a href="#">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.reject')}}
                                            </div>
                                        </a>
                                    @endif
                                </td>
                                @endcan
                                 <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif</td>


                                @if(Gate::check('epaper-edit') || Gate::check('epaper-delete') || Gate::check('epaper-translate'))
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                      

                                       <a class="flex items-center mr-3 text-theme-9" href="{{url('viewstory')}}/{{$row->id}}/{{$layout}}/{{$theme}}" >
                                       <i data-feather="check-square" class="w-4 h-4 mr-1"></i> {{__('admin.edit')}}
                                        </a>
                                        @can('epaper-delete')
                                        <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}"  title="{{__('admin.delete')}}">
                                            <i data-feather="trash-2" class="w-4 h-4 mr-1"></i>  {{__('admin.delete')}}
                                        </a> 
                                        @endcan
                                    </div>

                                    <!-- translation model starts -->

                                        <div class="modal" id="translate_e_paper_edit_{{$row->id}}">
                                            <div class="modal__content">
                                                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                    <h2 class="font-medium text-base mr-auto"> {{__('admin.translate_e_paper')}}</h2>
                                                </div>

                                                <form id="translate_e_paperform_{{$row->id}}">

                                                    <input type="hidden" name="e_paper_id" value="{{$row->id}}">

                                                    <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <label class="mb-2">{{__('admin.language')}}</label>

                                                            <select data-placeholder="{{__('admin.select_language')}}" name="language_code" class="tail-select w-full " onchange="getEpaperTranslation('{{$row->id}}',this.value)">
                                                                <option value="" >{{__('admin.select_language')}}</option>
                                                                @foreach($languages as $lang)
                                                                    <option  @if($row->language_code == $lang->language) selected @endif value="{{$lang->language}}">{{$lang->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                        <div class="col-span-12 sm:col-span-12">
                                                            <label>{{__('admin.paper_name')}}</label>
                                                            <input type="text" class="input w-full border mt-2 flex-1" name="paper_name" id="paper_name_{{$row->id}}" placeholder="{{__('admin.paper_name_placeholder')}}" value="{{$row->paper_name_trans}}">
                                                        </div>
                                                    </div>

                                                    <div class="p-5 grid col-span-12 sm:col-span-4">

                                                        <input type="hidden" name="upload_file" id="upload_file_{{$row->id}}_translate" value="{{$row->pdf_name_trans}}">

                                                        <label>({{__('admin.only_pdf')}})</label>

                                                        <div class="col-span-12 sm:col-span-12">
                                                            <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_pdf')}}" onclick="triggerFileInput('TranslatePdfUpload_{{$row->id}}')">

                                                            <input class="TranslatePdfUpload_{{$row->id}} hide" type="file" name="thumbimage" onchange="uploadPdf(this,'translate_pdf_name_{{$row->id}}','add','{{$row->id}}_translate');" accept=".pdf"/>

                                                        </div>

                                                        <div class="col-span-12 sm:col-span-12 mt-3" >
                                                            @if($row->pdf_trans == false)
                                                                <p id="translate_pdf_name_{{$row->id}}">{{__('admin.no_file_selected')}}</p>

                                                            @else

                                                            <p id="translate_pdf_name_{{$row->id}}"><a href="{{$row->pdf_trans}}" target="_blank">{{__('admin.view')}}</a></p>

                                                            @endif
                                                        </div>

                                                    </div>

                                                    <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                        <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                      @can('epaper-edit')  <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.update')}}" onclick="translateEpaper(event,'translate_e_paperform_{{$row->id}}')">
                                                        @endcan
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    <!-- translation model end -->


                                    <div class="modal" id="header-footer-modal-preview_edit_{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                                <h2 class="font-medium text-base mr-auto"> {{__('admin.edit_w_product')}}</h2>
                                            </div>

                                            <form id="editcategoryform_{{$row->id}}">
                                                <input type="hidden" name="id" value="{{$row->id}}">
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.product_name')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.product_name')}}" value="{{$row->name}}">
                                                    </div>
                                                </div>
                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.point')}}</label>
                                                        <input type="text" class="input w-full border mt-2 flex-1" name="point" placeholder="{{__('admin.point')}}" value="{{$row->point}}">
                                                    </div>
                                                </div>
                                                

                                                <?php
                                                    if(file_exists(public_path()."/upload/e-paper/original/".$row->img) && $row->img!='') {
                                                        $bannerurl = url('upload/e-paper/original').'/'.$row->img;
                                                    }else{
                                                        $bannerurl = url('upload/no-image.png');
                                                    }
                                                ?>

                                                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <label>{{__('admin.redeem')}}</label>
                                                        <select class="input w-full border mt-2 flex-1" name="redeem">
                                                            <option value="1" @if($row->redeem == 1) selected @endif>{{__('admin.m_time')}}</option>
                                                            <option value="0" @if($row->redeem == 0) selected @endif>{{__('admin.o_time')}}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="p-5 grid col-span-12 sm:col-span-4">
                                                    <input type="hidden" name="thumb_image" id="thumb_image_{{$row->id}}" value="{{$row->img}}">
                                                    <label>({{__('admin.thumb_image_resolution')}})</label>
                                                    <div class="col-span-12 sm:col-span-12">
                                                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_thumb_image')}}" onclick="triggerFileInput('thumbimageuploadBtn_{{$row->id}}')">
                                                        <input class="thumbimageuploadBtn_{{$row->id}} hide" type="file" name="thumbimage" onchange="uploadEpaperLogo(this,'thumbimage_image_add_{{$row->id}}','add','{{$row->id}}');" accept="image/jpg, image/jpeg, image/png"/>
                                                    </div>
                                                    <div class="col-span-12 sm:col-span-12 mt-3" >
                                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="thumbimage_image_add_{{$row->id}}" src="{{$bannerurl}}" class="width-30" >
                                                    </div>
                                                </div>

                                                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                  @can('epaper-edit')
                                                        <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.update')}}" onclick="add_product(event,'editcategoryform_{{$row->id}}')">
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
                                                <a href="{{url('delete-product')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @endcan
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
                {!! $News->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">

        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">
            <p class="text-right"><?php if ($News->firstItem() != null) { ?> {{__('admin.showing')}} {{ $News->firstItem() }} {{__('admin.to')}} {{ $News->lastItem() }} {{__('admin.of')}} {{ $News->total() }} {{__('admin.entries')}} <?php }?></p>
        </div>
    </div>


    <div class="modal" id="header-footer-modal-preview">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_w_product')}}</h2>
            </div>
            <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>
            <form id="addcategoryform">
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.product_name')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.product_name')}}">
                    </div>
                </div>
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.point')}}</label>
                        <input type="number" class="input w-full border mt-2 flex-1" name="point" placeholder="{{__('admin.point')}}">
                    </div>
                </div>
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.redeem')}}</label>
                        <select class="input w-full border mt-2 flex-1" name="redeem">
                            <option value="1">{{__('admin.m_time')}}</option>
                            <option value="0">{{__('admin.o_time')}}</option>
                        </select>
                    </div>
                </div>
                
                <div class="p-5 grid col-span-12 sm:col-span-4">
                    <input type="hidden" name="thumb_image" id="thumb_image" value="">
                    <label>({{__('admin.upload_image')}})</label>
                    <div class="col-span-12 sm:col-span-12">
                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_image')}}" onclick="triggerFileInput('thumbimageuploadBtn')">
                        <input class="thumbimageuploadBtn hide" type="file" name="thumbimage" onchange="uploadEpaperLogo(this,'thumbimage_image_add','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                    </div>
                    <div class="col-span-12 sm:col-span-12 mt-3" >
                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="thumbimage_image_add" class="width-30" >
                    </div>
                </div>
                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                    @can('epaper-create')
                        <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.create')}}" onclick="add_product(event,'addcategoryform')">
                    @endcan
                </div>
            </form>
        </div>
    </div>
@endsection
