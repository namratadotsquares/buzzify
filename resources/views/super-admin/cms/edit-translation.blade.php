@extends('../layout/' . $layout)

@section('subhead')
    <title> {{$data->page_title}} {{__('admin.translation')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{$data->page_title}} {{__('admin.translation')}} </h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <form id="addUpdateBlog">
                <div class="intro-y box p-5">
                    
                        <input type="hidden" name="cms_id" value="{{$data->id}}">
                        <div class="col-span-12 sm:col-span-12">
                            <label class="mb-2">{{__('admin.language')}}</label>
                            <div class="mt-2">
                                <select data-placeholder="{{__('admin.select_language')}}" name="language_code" class="tail-select w-full " onchange="getCmsTranslation('{{$data->id}}',this.value)">
                                    <option value="" >{{__('admin.select_language')}}</option>
                                    @foreach($languages as $lang)
                                        <option @if(isset($page_translate->language_code) && $page_translate->language_code == $lang->language) selected @endif value="{{$lang->language}}">{{$lang->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    
                        <div class="mt-3">
                            <label>{{__('admin.title')}}</label>
                            <input type="text" class="input w-full border mt-2" id="title" name="title"  placeholder="{{__('admin.title_placeholder')}}" @if(isset($data->title)) value="{{$data->title}}" @endif>
                        </div>
                        
                        <div class="mt-3">
                            <label>{{__('admin.description')}}</label>
                            <div class="mt-2">
                                <div class="preview">
                                    <textarea name="description" id="description" >@if(isset($data->description)) {{$data->description}} @endif</textarea>
                                </div>
                            </div>
                        </div>
                      
                        
                        <div class="text-right mt-5">
                            <a href="{{url('cms-pages/side-menu/light')}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                            <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="translateCmsPage(event,'addUpdateBlog')">{{__('admin.update')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>  
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->  
    <script>
        CKEDITOR.replace( 'description');
  </script>
@endsection