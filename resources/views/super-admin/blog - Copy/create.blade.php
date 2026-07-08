@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.add_blog')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')


<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.add_blog')}}</h2>
    </div>

    <form id="addUpdateBlog">

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12 bg_" >
            <div class="intro-y box p-5">
                <div class="mt-3">
                    <!--<div class="grid grid-cols-12 gap-4 row-gap-3">-->
                    <!--    <div class="col-span-12 sm:col-span-6">-->
                    <!--        <label>{{__('admin.accent')}}</label>-->
                    <!--        <div class="mt-2">-->
                    <!--            <select data-placeholder="Select Accent" name="blog_accent_code" class="tail-select w-full">-->
                    <!--                <option value="" >{{__('admin.accent_plceholder')}}</option>-->
                    <!--                @foreach($voice_accent as $accent)-->
                    <!--                    <option @if($accent == setting('blog_accent_code')) selected  @endif value="{{$accent}}">{{$accent}}</option>-->
                    <!--                @endforeach-->
                    <!--            </select>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--    <div class="col-span-12 sm:col-span-6">-->


                    <!--        <label>{{__('admin.category')}}</label>-->
                    <!--        <div class="mt-2">-->
                    <!--            <select data-placeholder="{{__('admin.select_category')}}" name="category_id" class="tail-select w-full">-->
                    <!--                <option value="" >{{__('admin.select_category')}}</option>-->
                    <!--                @foreach($category as $cat)-->
                    <!--                    <option value="{{$cat->id}}">{{$cat->name}}</option>-->
                    <!--                @endforeach-->
                    <!--            </select>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->

                </div>

                <div class="mt-3">
                    <div class="grid grid-cols-12 gap-4 row-gap-3">
                        <div class="col-span-12 sm:col-span-6">
                            <label>{{__('admin.language')}} <span class="required">*</span></label>
                            <div class="mt-2">
                                <select data-placeholder="{{__('admin.select_language')}}"  id="language" name="language[]" multiple class="tail-select w-full">
                                    @foreach($languages as $lang)
                                        <option @if($lang->language == 'en') selected
                                        @endif  value="{{$lang->language}}">{{$lang->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label>{{__('admin.category')}} <span class="required">*</span></label>
                            <div class="mt-2">
                                <select data-placeholder="{{__('admin.select_category')}}" id="category_id" name="category_id[]" multiple class="tail-select w-full">
                                    <!-- <option value="" >{{__('admin.select_category')}}</option> -->
                                    @foreach($category as $cat)
                                        <option value="{{$cat->id}}">{{$cat->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label>{{__('admin.title')}} <span class="required">*</span></label>
                    <input type="text" class="input w-full border mt-2" name="title" placeholder="{{__('admin.title_placeholder')}}" onkeyup="convertToSlug(this.value)" onblur="convertToSlug(this.value)">
                </div>

                <!--<div class="mt-3">-->
                <!--    <label>{{__('admin.slug')}}</label>-->
                <!--    <input type="text" class="input w-full border mt-2" id="slug" name="slug" placeholder="{{__('admin.slug_placeholder')}}" onblur="validateSlug(this.value)">-->
                <!--</div>-->

                <!--<div class="mt-3">-->
                <!--    <label>{{__('admin.tags')}} <font class="font-size10 text-danger">({{__('admin.comma_saperate')}})</font></label>-->
                <!--    <input type="text" class="input w-full border mt-2" name="tags" data-role="tagsinput" placeholder="{{__('admin.tags_placeholder')}}" style="display:none;">-->
                <!--</div>-->
                <div class="mt-3">
                    <label>{{__('admin.description')}} </label>
                    <div class="mt-2">
                        <div class="preview">
                            <textarea name="description" id="blogdescription"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <div class="col-span-12 sm:col-span-4">
                        <input type="hidden" name="banner_image" id="banner_image" value="">
                        <label>({{__('admin.banner_resolution')}}) <span class="required">*</span></label>
                        <div class="col-span-12 sm:col-span-12">
                            <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_banner_image')}}" onclick="triggerFileInput('BannerimageuploadBtn')">

                            <input class="BannerimageuploadBtn hide" id="image" type="file" multiple="multiple" name="image[]" onchange="uploadMultipleBannerImage(this,'Bannerimage_image_add','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                        </div>

                    </div>
                </div>

                <div class="mt-3">
                    <div class="col-span-12 sm:col-span-12 mt-3 width-float">
                        <div id="Bannerimage_image_add hide pull-left" >
                            <div id='preview'></div>
                        </div>
                    </div>
                </div>

                <div class="text-right mt-8">
                    <a href="{{url('blog/')}}/{{$layout}}/{{$theme}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                    @can('blog-create')
                        <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="addUpdateBlog(event,'addUpdateBlog','create')">{{__('admin.create')}}</button>
                    @endcan
                    <button type="button" id="createBtn" class="button bg-theme-1 text-white" onclick="addUpdateBlog(event,'addUpdateBlog','draft')">{{__('admin.save_draft')}}</button>
                </div>

                <div class="grid grid-cols-12 gap-4 row-gap-3">
                </div>
            </div>
        </div>
    </div>

    <div></div>
    
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <div class="mt-3">
                    <div class="col-span-12 sm:col-span-4" >
                        <input type="hidden" name="audio_file_upload" id="audio_file_upload" value="">
                        <label>({{__('admin.mp3_allowed')}})</label>
                        <div class="col-span-12 sm:col-span-12">
                            <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.upload_audio_image')}}" onclick="triggerFileInput('audio_file')">

                            <input class="audio_file hide" id="audio_file" type="file" name="audio_file" onchange="uploaudiofile(this,'audio_file_add','add',0);" accept="audio/mp3"/>
                        </div>

                    </div>
                </div>

                <div class="mt-3">
                    <div class="col-span-12 sm:col-span-12 mt-3" >
                        <div id="audio_file_add hide" >
                            <div id='audiopreview'></div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label>{{__('admin.youtube_url')}}</label>
                    <input type="text" class="input w-full border mt-2" name="video_url" placeholder="{{__('admin.youtube_url_placeholder')}}" >
                </div>

                <div class="mt-3">
                    <label>{{__('admin.blog_url')}}</label>
                    <input type="text" class="input w-full border mt-2" name="url" placeholder="{{__('admin.blog_url_placeholder')}}" >
                </div>

                
                <div class="mt-3">
                    <div class="grid grid-cols-12 gap-4 row-gap-3">
                        <div class="col-span-12 sm:col-span-4">
                            <label>{{__('admin.schedule_date')}} </label>
                            <input type="date" class="input w-full border mt-2 form-control" name="schedule_date" placeholder="{{__('admin.schedule_date_placeholder')}}" >
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label>{{__('admin.schedule_time')}}</label>
                            <input type="time" class="input w-full border mt-2" name="schedule_time" placeholder="{{__('admin.schedule_time_placeholder')}}" >
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.text_speech')}}</h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <div class="mt-3">
                    <div class="grid grid-cols-12 gap-4 row-gap-3">
                        <div class="col-span-12 sm:col-span-6">
                            <label>{{__('admin.accent')}}</label>
                            <div class="mt-2">
                                <select data-placeholder="Select Accent" name="blog_accent_code" class="tail-select w-full">
                                    <option value="" >{{__('admin.accent_plceholder')}}</option>
                                    @foreach($voice_accent as $accent)
                                        <option @if($accent == setting('blog_accent_code')) selected  @endif value="{{$accent}}">{{$accent}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">

                            <label>{{__('admin.voice')}}</label>
                            <div class="mt-2">
                                <select data-placeholder="{{__('admin.voice')}}" id="voice" name="voice" class="tail-select w-full">
                                    @foreach(config('constant.speech_voice') as $key => $value)
                                        <option  value="{{$key}}">{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        
                    </div>
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
                    <label>{{__('admin.slug')}} <span class="required">*</span></label>
                    <input type="text" class="input w-full border mt-2" id="slug" name="slug" placeholder="{{__('admin.slug_placeholder')}}" onblur="validateSlug(this.value)">
                </div>

                <div class="mt-3">
                    <label>{{__('admin.tags')}} <font class="font-size10 text-danger">({{__('admin.comma_saperate')}})</font></label>
                    <input type="text" class="input w-full border mt-2" name="tags" data-role="tagsinput" placeholder="{{__('admin.tags_placeholder')}}" style="display:none;">
                </div>
                <div class="mt-3">
                    <label>{{__('admin.title')}} ({{__('admin.meta_tag')}})</label>
                    <input type="text" class="input w-full border mt-2" name="seo_title" placeholder="{{__('admin.title_placeholder')}}">
                </div>
                <div class="mt-3">
                    <label>{{__('admin.keywords')}} ({{__('admin.meta_tag')}})</label>
                    <input type="text" class="input w-full border mt-2" name="seo_keyword" placeholder="{{__('admin.keywords_placeholder')}}">
                </div>
                <div class="mt-3">
                    <label>{{__('admin.tags')}} ({{__('admin.meta_tag')}})</label>
                    <input type="text" class="input w-full border mt-2" name="seo_tag" data-role="tagsinput" placeholder="{{__('admin.tags_placeholder')}}" style="display:none;">
                </div>
                <div class="mt-3">
                    <label>{{__('admin.description')}} ({{__('admin.meta_tag')}})</label>
                    <div class="mt-2">
                        <div class="preview">
                            <textarea name="seo_description" class="input w-full border mt-2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div></div>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.visibility')}}</h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="intro-y box p-5">
                <div class="mt-3">
                    <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                        <label class="cursor-pointer select-none width-25" for="is_featured">{{__('admin.add_to_featured')}}</label>
                        <input type="checkbox" class="input border mr-2" id="is_featured" name="is_featured">
                    </div>
                </div>
                <!-- <div class="mt-3">
                    <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                        <label class="cursor-pointer select-none width-25" for="is_slider">{{__('admin.add_to_slider')}}</label>
                        <input type="checkbox" class="input border mr-2" id="is_slider" name="is_slider">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                        <label class="cursor-pointer select-none width-25" for="is_editor_picks">{{__('admin.add_to_editor_pick')}}</label>
                        <input type="checkbox" class="input border mr-2" id="is_editor_picks" name="is_editor_picks">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                        <label class="cursor-pointer select-none width-25" for="is_weekly_top_picks">{{__('admin.weekly_top_picks')}}</label>
                        <input type="checkbox" class="input border mr-2" id="is_weekly_top_picks" name="is_weekly_top_picks">
                    </div>
                </div> -->

            </div>
        </div>
    </div>
    <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">Voting question</h2>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <div class="intro-y box p-5">
                     <div class="col-span-12 sm:col-span-3">
                                <div class="mt-3">
                                    <label>{{__('admin.enable_voting')}}</label>
                                    <div class="mt-2">
                                        <input type="checkbox" name="is_voting_enable" id="is_voting_enable" class="input input--switch border" >
                                    </div>
                                </div>
                            </div>
                    <div class="mt-3 showTopicInput hide">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2" style="display: contents;">

                            <input type="text" class="input border mr-2" id="" name="VotingQuestion" value="" placeholder="Enter the topic" style="width: 50%">
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                            <label class=" select-none width-25" for="">Option  Type</label>
                            <br>
                            <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2"  style="margin-right: 10px;">

                              <input type="radio" class="input border mr-2" id="" name="optiontype" value="0"> Yes/No
                        </div>
                    </div>
                     <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">

                             <input type="radio" class="input border mr-2" id="" name="optiontype" value="1">  Agree/Disagree
                        </div>
                    </div>


                        </div>
                    </div>
                    <div class="text-right mt-8">
                        <a href="{{url('blog/')}}/{{$layout}}/{{$theme}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                        @can('blog-create')
                            <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="addUpdateBlog(event,'addUpdateBlog','create')">{{__('admin.create')}}</button>
                        @endcan
                        <button type="button" id="createBtn" class="button bg-theme-1 text-white" onclick="addUpdateBlog(event,'addUpdateBlog','draft')">{{__('admin.save_draft')}}</button>
                    </div>

                </div>
            </div>
        </div>
    </form>
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
    <script>
        CKEDITOR.replace( 'blogdescription' ,{
            height: '460px',
        });
    </script>

@endsection
