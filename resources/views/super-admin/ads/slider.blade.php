@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.add_blog')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <style>
        .error{
            color: #ff0000;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" type="text/css">
    <link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Add Banner</h2>
    </div>
    <div>
        <div class="container">
            <div class="dropzone dz-clickable" id="myDrop">
                <div class="dz-default dz-message" data-dz-message="">
                    <span>Drop files here to upload</span>
                </div>
            </div>
            <div class="text-right mt-8">
                @can('ads-edit')
                    <button type="button" id="add_file" value="Add" class="button w-24 bg-theme-1 text-white">{{__('Add')}}</button>
                @endcan
            </div>

        </div>
        <hr class="my-5">
        <div class="container">
            <div id="msg" class="mb-3 text-danger" ></div>
            @can('ads-edit')
            <a href="javascript:void(0);" class="button w-24 bg-theme-6 text-white reorder mb-4" id="updateReorder" adID="{{$ads['id']}}">Reorder Imgaes & Update url</a>
            @endcan
            <div id="reorder-msg" class="alert alert-warning mt-3" style="display:none;">
                <i class="fa fa-3x fa-exclamation-triangle float-right"></i> 1. Drag photos to reorder..<br>2. Click 'Save Reordering & Url' when finished.
            </div>
            <div class="gallery">
                <ul class="nav nav-pills" style="display: flex;">

                    {{--                                Fetch all images from database--}}
                    @php($images = DB::table('ads_images')->where('adID',$ads['id'])->orderBy('img_order','ASC')->get())

                    @if(!empty($images))
                        @foreach($images as $row)

                            <li id="image_li_{{$row->id}}" class="ui-sortable-handle mr-2 mt-2">
                                <div><img src="{{asset($row->location)}}" alt="" class="img-thumbnail" width="200"></div>
                                <a href="/deleteAdImage/{{$row->id}}">Delete</a>
                                <input type="text " placeholder="Redirect URL" class="urlInput input w-full border mt-2" value="{{$row->redirectUrl}}">
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div>
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
    <div class="text-right mt-8">
        <a href="{{url('ads/')}}/{{$layout}}/{{$theme}}"
           class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
    </div>

@endsection
