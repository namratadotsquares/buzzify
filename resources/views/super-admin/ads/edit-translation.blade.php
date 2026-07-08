@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.edit_blog_translation')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.edit_blog_translation')}}</h2>
    </div>
    <form id="addUpdateBlog">
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
              
                <input type="hidden" name="blog_id" id="blog_id" value="{{$blog->id}}">
                <div class="intro-y box p-5 width-float">

                    <div class="col-span-12 sm:col-span-12">
                        <label class="mb-2">{{__('admin.language')}}</label>
                        <div class="mt-2">
                            <select data-placeholder="{{__('admin.select_language')}}" name="language_code" class="tail-select w-full language_code" id="language_code" onchange="getBlogTranslation('{{$blog->id}}',this.value)">
                                <option value="" >{{__('admin.select_language')}}</option>
                                @foreach($languages as $lang)
                                    <option @if(isset($data->language_code) && $data->language_code == $lang->language) selected @else @if(setting('preferred_site_language') == $lang->language) selected @endif @endif value="{{$lang->language}}">{{$lang->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label>{{__('admin.title')}}</label>
                        <input type="text" class="input w-full border mt-2" name="title" placeholder="{{__('admin.title_placeholder')}}" id="title" @if(isset($data->title)) value="{{$data->title}}" @endif >
                    </div>

                    <div class="mt-3">
                        <label>{{__('admin.tags')}}<font class="font-size10 text-danger">({{__('admin.comma_saperate')}})</font></label>
                        <input type="text" class="input w-full border mt-2" name="tags" id="tags" data-role="tagsinput" value="" placeholder="{{__('admin.tags_placeholder')}}" style="display:none;" @if(isset($data->tags)) value="{{$data->tags}}" @endif>
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.description')}}</label>
                        <div class="mt-2">
                            <div class="preview">
                                <textarea name="description" id="blogdescription">@if(isset($data->description)) {{$data->description}} @endif</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 row-gap-3">
                    </div>
                   
                </div>
            </div>
        </div>   
        <div></div>
        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">{{__('admin.seo_details')}}</h2>
        </div>    
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <div class="intro-y box p-5">
                    <div class="mt-3">
                        <label>{{__('admin.title')}} ({{__('admin.meta_tag')}})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_title" id="seo_title" placeholder="{{__('admin.title_placeholder')}}" @if(isset($data->seo_title)) value="{{$data->seo_title}}" @endif>
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.keywords')}} ({{__('admin.meta_tag')}})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_keyword" id="seo_keyword" placeholder="{{__('admin.keywords_placeholder')}}" @if(isset($data->seo_keyword)) value="{{$data->seo_keyword}}" @endif>
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.tags')}} ({{__('admin.meta_tag')}})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_tag" id="seo_tag" data-role="tagsinput" placeholder="{{__('admin.tags_placeholder')}}" style="display:none;" @if(isset($data->seo_tag)) value="{{$data->seo_tag}}" @endif >
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.description')}} ({{__('admin.meta_tag')}})</label>
                        <div class="mt-2">
                            <div class="preview">
                                <textarea name="seo_description" id="seo_description" class="input w-full border mt-2">@if(isset($data->seo_description)) {{$data->seo_description}} @endif</textarea>
                            </div>
                        </div>
                    </div>

                     <div class="text-right mt-5">
                        <a href="{{url('blog/')}}/{{$layout}}/{{$theme}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                        <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="translateBlog(event,'addUpdateBlog')">{{__('admin.update')}}</button>
                    </div>
                </div>
            </div>
        </div>   
    </form>
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
    <script>
        CKEDITOR.replace( 'blogdescription',{
            height: '460px',
        } );    
    </script>
@endsection