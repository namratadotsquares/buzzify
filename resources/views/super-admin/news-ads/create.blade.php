@extends('../layout/' . $layout)
@section('subhead')
    <title>{{__('admin.add_ads')}} - {{setting('site_name')}}</title>
@endsection
@section('subcontent')
    @include('../layout/components/top-bar')
    <style>
        .error{
            color: #ff0000;
        }
    </style>
    <link rel="stylesheet" href="https://demo.learncodeweb.com/web-development/drag-drop-images-with-bootstrap-4-and-reorder-using-php-jquery-and-ajax/dropzone/dropzone.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" type="text/css">
    <link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Create Ad</h2>
    </div>
    <form method="post" action="{{url('news-ads/')}}/{{$layout}}/{{$theme}}" enctype="multipart/form-data">
        @csrf
        <div id="showInputs"></div>
        <div id="showInputsImages"></div>
        <div id="showInputsImagesName"></div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12 bg_">
                <div class="intro-y box p-5">
                    <div class="mt-3">
                        <label>{{__('admin.url')}}</label>
                        <input type="text" class="input w-full border mt-2" name="url"
                               placeholder="Url" value="{{old('url')}}">
                        @if($errors->has('url'))
                            <div class="error">Url Is Required</div>
                        @endif
                    </div>
                    <div class="mt-3">
                        <label>Frequency</label>
                        <input type="number" min="1" step="1" class="input w-full border mt-2" name="frequency"
                               placeholder="Frequency" value="{{ old('frequency', 1) }}">
                        @if($errors->has('frequency'))
                            <div class="error">Frequency Is Required</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <div class="intro-y box p-5">
                    <div class="mt-3">
                        <div class="col-span-12 sm:col-span-4">
                            <label>({{__('admin.images')}}) <span class="required">*</span></label>
                            <div class="col-span-12 sm:col-span-12">
                                <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_images')}}" onclick="triggerFileInput('imageuploadBtn')">
                                <input class="imageuploadBtn hide" id="images" type="file" name="image" onchange="uploadMultipleAdsImages(this,'image_image_add','add',0);" accept="image/*"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 lg:col-span-8 xxl:col-span-9">
                        <div style="margin-top: 30px;">
                            <div class="grid grid-cols-12 gap-5 display_images" id="display_images">                    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12 bg_">
                <div class="intro-y box p-5">
                    <div class="text-right">
                        <a href="{{url('ads/')}}/{{$layout}}/{{$theme}}"
                           class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                        <button type="submit" id="" class="button w-24 bg-theme-1 text-white">{{__('admin.create')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
