@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.edit_blog')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')


<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.edit_blog')}}</h2>
    </div>
    <form id="addUpdateBlog">
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">

                <?php
                    if(file_exists(public_path()."/upload/blog/banner/original/".$blog->banner_image) && $blog->banner_image!='') {
                        $bannerurl = url('upload/blog/banner/original').'/'.$blog->banner_image;
                    }else{
                        $bannerurl = url('upload/no-image.png');
                    }
                    if(file_exists(public_path()."/upload/blog/thumb/original/".$blog->thumb_image) && $blog->thumb_image!='') {
                        $thumburl = url('upload/blog/thumb/original').'/'.$blog->thumb_image;
                    }else{
                        $thumburl = url('upload/no-image.png');
                    }
                ?>
                <input type="hidden" name="id" id="productId" value="{{$blog->id}}">
                <input type="hidden" name="status" value="{{$blog->status}}">
                <div class="intro-y box p-5 width-float">
                    <div class="mt-3">
                        <div class="grid grid-cols-12 gap-4 row-gap-3">
                            <!--<div class="col-span-12 sm:col-span-6">-->
                            <!--    <label>{{__('admin.accent')}}</label>-->
                            <!--    <div class="mt-2">-->
                            <!--        <select data-placeholder="{{__('admin.accent_plceholder')}}" name="blog_accent_code" class="tail-select w-full">-->
                            <!--            <option value="" >{{__('admin.accent_plceholder')}}</option>-->
                            <!--            @foreach($voice_accent as $accent)-->
                            <!--                <option @if($accent == $blog->blog_accent_code) selected  @endif value="{{$accent}}">{{$accent}}</option>-->
                            <!--            @endforeach-->
                            <!--        </select>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="col-span-12 sm:col-span-6">
                                <label>{{__('admin.language')}} <span class="required">*</span></label>
                                <div class="mt-2">
                                    <select  data-placeholder="{{__('admin.language_plceholder')}}"  class="tail-select w-full" name="language">
                                        @foreach($language as $lang)
                                            <option selected value="{{$lang->language}}">{{$lang->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label>{{__('admin.category')}} <span class="required">*</span></label>
                                <div class="mt-2">
                                    <select data-placeholder="{{__('admin.select_category')}}" id="category_id" name="category_id[]" multiple class="tail-select w-full">
                                        @foreach($category as $cat)
                                            <option @if(in_array($cat->id,$blog->blog_category_id)) selected  @endif value="{{$cat->id}}">{{$cat->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--<div class="mt-3">-->
                    <!--    <div class="grid grid-cols-12 gap-4 row-gap-3">-->
                    <!--        <div class="col-span-12 sm:col-span-6">-->
                    <!--            <label>{{__('admin.category')}}</label>-->
                    <!--            <div class="mt-2">-->
                    <!--                <select data-placeholder="{{__('admin.select_category')}}" name="category_id" class="tail-select w-full">-->
                    <!--                    <option value="" >{{__('admin.select_category')}}</option>-->
                    <!--                    @foreach($category as $cat)-->
                    <!--                        <option @if($cat->id == $blog->category_id) selected  @endif value="{{$cat->id}}">{{$cat->name}}</option>-->
                    <!--                    @endforeach-->
                    <!--                </select>-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--        <div class="col-span-12 sm:col-span-6">-->

                    <!--            <label>{{__('admin.voice')}}</label>-->
                    <!--            <div class="mt-2">-->
                    <!--                <select data-placeholder="{{__('admin.voice')}}" id="voice" name="voice" class="tail-select w-full">-->
                    <!--                    @foreach(config('constant.speech_voice') as $key => $value)-->
                    <!--                        <option  @if($key == $blog->voice) selected  @endif  value="{{$key}}">{{$value}}</option>-->
                    <!--                    @endforeach-->
                    <!--                </select>-->
                    <!--            </div>-->

                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->
                    <div class="mt-3">
                        <label>{{__('admin.title')}} <span class="required">*</span></label>
                        <input type="text" class="input w-full border mt-2" name="title" placeholder="{{__('admin.title_placeholder')}}" value="{{$blog->title}}" onkeyup="convertToSlug(this.value)" onblur="convertToSlug(this.value)">
                    </div>

                    
                    <div class="mt-3">
                        <label>{{__('admin.description')}} </label>
                        <div class="mt-2">
                            <div class="preview">
                                <textarea name="description" id="blogdescription">{{$blog->description}}</textarea>
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
                        <div class="col-span-12 sm:col-span-12 mt-3 width-float" >
                            <div id="Bannerimage_image_add">
                                @include('super-admin.blog.blog-images-list')
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-8">
                        <a href="{{url('blog/')}}/{{$layout}}/{{$theme}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                         @can('blog-edit')
                            <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="addUpdateBlog(event,'addUpdateBlog')">{{__('admin.update')}}</button>
                        @endcan
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
                        <div class="col-span-12 sm:col-span-12 mt-3 width-float" >

                            <?php $url = url("/upload/blog/audio/".$blog->audio_file) ?>

                            <div id="audio_file_add">
                                <div id='audiopreview'>

                                    @if($blog->audio_file != '')
                                        <audio controls><source src="{{$url}}" type="audio/mp3"></audio>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label>{{__('admin.blog_url')}}</label>
                        <input type="text" class="input w-full border mt-2" name="url" placeholder="{{__('admin.blog_url_placeholder')}}" value="{{$blog->url}}">
                    </div>

                    <div class="mt-3">
                        <label>{{__('admin.youtube_url')}}</label>
                        <input type="text" class="input w-full border mt-2" name="video_url" placeholder="{{__('admin.youtube_url_placeholder')}}" value="{{$blog->video_url}}">
                    </div>

                    <div class="mt-3" >
                        <div class="grid grid-cols-12 gap-4 row-gap-3">
                            @if($blog->status==2)
                                <div class="col-span-12 sm:col-span-4">
                                    <label>{{__('admin.schedule_date')}}</label>
                                    <input type="date" class="input w-full border mt-2 form-control" name="schedule_date" placeholder="{{__('admin.schedule_date_placeholder')}}" >
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <label>{{__('admin.schedule_time')}}/label>
                                    <input type="time" class="input w-full border mt-2" name="schedule_time" placeholder="{{__('admin.schedule_time_placeholder')}}" >
                                </div>
                            @else
                                <div class="col-span-12 sm:col-span-4">
                                    <label>{{__('admin.schedule_date')}}</label>
                                    <p>{{$blog->schedule_date}}</p>
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <label>{{__('admin.schedule_time')}}</label>
                                    <p>{{$blog->schedule_time}}</p>
                                </div>
                            @endif
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
                                    <select data-placeholder="{{__('admin.accent_plceholder')}}" name="blog_accent_code" class="tail-select w-full">
                                        <option value="" >{{__('admin.accent_plceholder')}}</option>
                                        @foreach($voice_accent as $accent)
                                            <option @if($accent == $blog->blog_accent_code) selected  @endif value="{{$accent}}">{{$accent}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-span-12 sm:col-span-6">

                                <label>{{__('admin.voice')}}</label>
                                <div class="mt-2">
                                    <select data-placeholder="{{__('admin.voice')}}" id="voice" name="voice" class="tail-select w-full">
                                        @foreach(config('constant.speech_voice') as $key => $value)
                                            <option  @if($key == $blog->voice) selected  @endif  value="{{$key}}">{{$value}}</option>
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
                        <input type="text" class="input w-full border mt-2" id="slug" name="slug" placeholder="{{__('admin.slug_placeholder')}}" value="{{$blog->slug}}">
                    </div>

                    <div class="mt-3">
                        <label>{{__('admin.tags')}}<font class="font-size10 text-danger">({{__('admin.comma_saperate')}})</font></label>
                        <input type="text" class="input w-full border mt-2" name="tags" data-role="tagsinput" value="{{$blog->tags}}" placeholder="{{__('admin.tags_placeholder')}}" style="display:none;">
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.title')}} ({{__('admin.meta_tag')}})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_title" placeholder="{{__('admin.title_placeholder')}}" value="{{$blog->seo_title}}">
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.keywords')}} ({{__('admin.meta_tag')}})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_keyword" placeholder="{{__('admin.keywords_placeholder')}}" value="{{$blog->seo_keyword}}">
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.tags')}} ({{__('admin.meta_tag')}})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_tag" data-role="tagsinput" placeholder="{{__('admin.tags_placeholder')}}" value="{{$blog->seo_tag}}" style="display:none;">
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.description')}} ({{__('admin.meta_tag')}})</label>
                        <div class="mt-2">
                            <div class="preview">
                                <textarea name="seo_description" class="input w-full border mt-2">{{$blog->seo_description}}</textarea>
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
                            <input type="checkbox" class="input border mr-2" id="is_featured" name="is_featured" <?php if($blog->is_featured===1){ echo "checked='checked'"; } ?>>
                        </div>
                    </div>
                   {{-- <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                            <label class="cursor-pointer select-none width-25" for="is_slider">{{__('admin.add_to_slider')}}</label>
                            <input type="checkbox" class="input border mr-2" id="is_slider" name="is_slider" <?php if($blog->is_slider===1){ echo "checked='checked'"; } ?>>
                        </div>
                    </div> --}}
                   {{-- <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                            <label class="cursor-pointer select-none width-25" for="is_editor_picks">{{__('admin.add_to_editor_pick')}}</label>
                            <input type="checkbox" class="input border mr-2" id="is_editor_picks" name="is_editor_picks" <?php if($blog->is_editor_picks===1){ echo "checked='checked'"; } ?>>
                        </div>
                    </div>--}}
                   {{-- <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                            <label class="cursor-pointer select-none width-25" for="is_weekly_top_picks">{{__('admin.weekly_top_picks')}}</label>
                            <input type="checkbox" class="input border mr-2" id="is_weekly_top_picks" name="is_weekly_top_picks" <?php if($blog->is_weekly_top_picks===1){ echo "checked='checked'"; } ?>>
                        </div>
                    </div> --}}

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
                                        <input type="checkbox" name="is_voting_enable" id="is_voting_enable" class="input input--switch border" @if($blog->is_voting_enable==1)checked @endif >
                                    </div>
                                </div>
                            </div>
                    <div class="mt-3 showTopicInput @if($blog->is_voting_enable!=1) hide @endif">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">

                            <input type="text" class="input border mr-2" id="" name="VotingQuestion" value="{{$blog->VotingQuestion}}" placeholder="Enter the topic" style="width: 50%">
                        </div>
                    </div>
                    <div class="mt-3 ">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2" style="display: contents;">
                            <label class="c select-none width-25" for="">Option  Type</label>
                            <br>
                            <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2"  style="margin-right: 10px;">

                              <input type="radio" class="input border mr-2" id="" name="optiontype" value="0" <?php if($blog->optiontype===0){ echo "checked='checked'"; } ?>> Yes/No
                        </div>
                    </div>
                     <div class="mt-3">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">

                             <input type="radio" class="input border mr-2" id="" name="optiontype" value="1" <?php if($blog->optiontype===1){ echo "checked='checked'"; } ?>>  Agree/Disagree
                        </div>
                    </div>


                        </div>
                    </div>
                    <div class="text-right mt-5">
                        <a href="{{url('blog/')}}/{{$layout}}/{{$theme}}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                         @can('blog-edit')
                            <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="addUpdateBlog(event,'addUpdateBlog')">{{__('admin.update')}}</button>
                        @endcan
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
