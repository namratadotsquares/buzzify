@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.edit')}} {{$data->page_title}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.edit')}} {{$data->page_title}}</h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <form id="addUpdateBlog">
                <div class="intro-y box p-5">

                        <?php 
                            if(file_exists(public_path()."/upload/cms/original/".$data->image) && $data->image!='') { 
                                $bannerurl = url('upload/cms/original').'/'.$data->image;
                            }else{
                                $bannerurl = url('upload/no-image.png');
                            }
                        ?>

                        <input type="hidden" name="id" value="{{$data->id}}">
                    
                        <div class="mt-3">
                            <label>{{__('admin.title')}}</label>
                            <input type="text" class="input w-full border mt-2" name="title" placeholder="{{__('admin.title_placeholder')}}" value="{{$data->title}}">
                        </div>
                   
                        <div class="mt-3">
                            <label>{{__('admin.description')}}</label>
                            <div class="mt-2">
                                <div class="preview">
                                    <textarea name="blogdescription">{{$data->description}}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="grid grid-cols-12 gap-4 row-gap-3">
                                <div class="col-span-12 sm:col-span-4">
                                    <input type="hidden" name="banner_image" id="banner_image" value="">
                                    <div class="col-span-12 sm:col-span-12">
                                        <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_banner_image')}}" onclick="triggerFileInput('BannerimageuploadBtn')">
                                        <input class="BannerimageuploadBtn hide" type="file" name="Bannerimage" onchange="uploadCmsBannerImage(this,'Bannerimage_image_add','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                                    </div>
                                    <div class="col-span-12 sm:col-span-12 mt-3" >
                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="Bannerimage_image_add" src="{{$bannerurl}}" class="width-30" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-right mt-5">
                            <a href="{{url('cms-pages/side-menu/light')}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                            <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="addUpdateCmsPage(event,'addUpdateBlog')">{{__('admin.update')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>  
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->  
    <script>
        CKEDITOR.replace( 'blogdescription' );
        CKEDITOR.replace( 'short_description' );
    </script>
@endsection