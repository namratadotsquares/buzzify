@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ __('admin.edit_blog') }} - {{ setting('site_name') }}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')


    <style>
        .accordion-content {
            display: none;
            border: 1px solid #edebeb;
            padding: 10px;
            margin-top: 25px;
        }

        .image-container {
            display: inline-block;
            vertical-align: top;
            margin-right: 10px;
            /* Adjust as needed */
        }

        .image-checkbox-label {
            display: inline-block;
            margin-right: 5px;
            /* Adjust as needed */
        }

        .image-checkbox-label input[type="checkbox"] {
            margin: 0;
        }

        .delete-icon {
            display: inline-block;
            vertical-align: top;
            cursor: pointer;
        }

        a {
            cursor: pointer;
        }
    </style>
    <link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ __('admin.edit_blog') }}</h2>
    </div>
    <form id="addUpdateBlog">
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <?php
                if (file_exists(public_path() . '/upload/blog/banner/original/' . $blog->banner_image) && $blog->banner_image != '') {
                    $bannerurl = url('upload/blog/banner/original') . '/' . $blog->banner_image;
                } else {
                    $bannerurl = url('upload/no-image.png');
                }
                if (file_exists(public_path() . '/upload/blog/thumb/original/' . $blog->thumb_image) && $blog->thumb_image != '') {
                    $thumburl = url('upload/blog/thumb/original') . '/' . $blog->thumb_image;
                } else {
                    $thumburl = url('upload/no-image.png');
                }
                ?>
                <input type="hidden" name="id" id="productId" value="{{ $blog->id }}">
                <input type="hidden" name="status" value="{{ $blog->status }}">
                <input type="hidden" id="redirect_query_string" value="{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}">
                <div class="intro-y box p-5 width-float">
                    <div class="mt-3">
                        <div class="grid grid-cols-12 gap-4 row-gap-3">
                            <div class="col-span-12 sm:col-span-6">
                                <label>{{ __('admin.language') }} <span class="required">*</span></label>
                                <div class="mt-2">
                                    <select data-placeholder="{{ __('admin.language_plceholder') }}" id="language"
                                        class="tail-select w-full" name="language[]" multiple>
                                        @foreach ($language as $lang)
                                            <option
                                                @if (isset($selectedLanguageCodes) && in_array($lang->language, $selectedLanguageCodes)) selected @endif
                                                value="{{ $lang->language }}">{{ $lang->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label>{{ __('admin.category') }} <span class="required">*</span></label>
                                <div class="mt-2">
                                    <select data-placeholder="{{ __('admin.select_category') }}" id="category_id"
                                        name="category_id[]" multiple class="tail-select w-full">
                                        @foreach ($category as $cat)
                                            <option
                                                @if (isset($blog->blog_category_id) && count($blog->blog_category_id)) @if (in_array($cat->id, $blog->blog_category_id)) selected @endif
                                                @endif
                                                value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php
                        $hiTranslation = isset($blogTranslations) ? $blogTranslations->get('hi') : null;
                    @endphp
                    <input type="hidden" name="edit_lang" id="edit_lang" value="en">
                    <input type="hidden" name="title_en" id="title_en" value="{{ $blog->title }}">
                    <input type="hidden" name="title_hi" id="title_hi" value="{{ $hiTranslation->title ?? '' }}">
                    <textarea name="description_en" id="description_en" style="display:none;">{{ $blog->description }}</textarea>
                    <textarea name="description_hi" id="description_hi" style="display:none;">{{ $hiTranslation->description ?? '' }}</textarea>
                    <div class="mt-3">
                        <label>Edit Translation</label>
                        <div class="mt-2">
                            <select data-placeholder="Select language" class="tail-select w-full" id="edit_lang_select">
                                <option value="en" selected>English</option>
                                <option value="hi">Hindi</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>{{ __('admin.title') }} <span class="required">*</span></label>
                        <input type="text" class="input w-full border mt-2" name="title" id="blogTitle"
                            placeholder="{{ __('admin.title_placeholder') }}" value="{{ $blog->title }}"
                            onkeyup="convertToSlugIfEnglish(this.value)" onblur="convertToSlugIfEnglish(this.value)">
                    </div>
                    <div class="mt-3 float-right">
                        <button type="button" id="rewrite_title_button" onclick="showtitlerewrite();"
                            class="button w-35 bg-theme-1 text-white">+ Rewrite Title</button>
                    </div>
                    <div class="mt-3 p-5">
                        <div class="accordion">
                            <div class="accordion-content mt-3" id="accordionTitle">
                                <div class="grid grid-cols-12 gap-4 row-gap-3">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Creativity</label>
                                        <div class="mt-2">
                                            <select data-placeholder="Select creativity level" class="tail-select w-full"
                                                name="creativity" id="creativity_title">
                                                <option value="0">Repetitive</option>
                                                <option value="0.25"> Deterministic</option>
                                                <option value="0.5" selected=""> Original</option>
                                                <option value="0.75"> Creative</option>
                                                <option value="1"> Imaginative</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Tone of Voice</label>
                                        <div class="mt-2">
                                            <select id="tone_title" name="tone" data-placeholder="Select tone of voice"
                                                class="tail-select w-full">
                                                <option value="Professional" selected=""> Professional</option>
                                                <option value="Exciting"> Exciting</option>
                                                <option value="Friendly"> Friendly</option>
                                                <option value="Witty"> Witty</option>
                                                <option value="Humorous"> Humorous</option>
                                                <option value="Convincing"> Convincing</option>
                                                <option value="Empathetic"> Empathetic</option>
                                                <option value="Inspiring"> Inspiring</option>
                                                <option value="Supportive"> Supportive</option>
                                                <option value="Trusting"> Trusting</option>
                                                <option value="Playful"> Playful</option>
                                                <option value="Excited"> Excited</option>
                                                <option value="Positive"> Positive</option>
                                                <option value="Negative"> Negative</option>
                                                <option value="Engaging"> Engaging</option>
                                                <option value="Worried"> Worried</option>
                                                <option value="Urgent"> Urgent</option>
                                                <option value="Passionate"> Passionate</option>
                                                <option value="Informative"> Informative</option>
                                                <option value="Funny">Funny</option>
                                                <option value="Casual"> Casual</option>
                                                <option value="Sarcastic"> Sarcastic</option>
                                                <option value="Dramatic"> Dramatic</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Sentimate</label>
                                        <div class="mt-2">
                                            <select id="sentimate_title" name="sentimate"
                                                data-placeholder="Select tone of voice" class="tail-select w-full">
                                                <option value="Positive" selected>Positive</option>
                                                <option value="Negative">Negative</option>
                                                <option value="Neutral"> Neutral </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Translate</label>
                                        <div class="mt-2">
                                            <select id="translate_title" name="translate" class="input w-full border">
                                                <option value="no">No</option>
                                                <option value="yes" selected>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Max Result Length</label>
                                        <input type="number" class="input w-full border mt-2 " id="words_title"
                                            name="words" placeholder="e.g. 15" max="1000" value="15">
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <input type="button" class="button w-35 bg-theme-1 text-white" value="Submit"
                                            id="rewrite_submit">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label>{{ __('admin.description') }} </label>
                        <div class="mt-2">
                            <div class="preview">
                                <textarea name="description" id="blogdescription">{{ $blog->description }}</textarea>
                                <small id="wordCountMessage" style="color: red;"></small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 float-right">
                        <button type="button" id="rewrite_des_button" onclick="showdisrewrite();"
                            class="button w-35 bg-theme-1 text-white">+ Rewrite Description</button>
                    </div>
                    <div class="mt-3 p-5">
                        <div class="accordion">
                            <div class="accordion-content mt-3" id="accordionDes">
                                <div class="grid grid-cols-12 gap-4 row-gap-3">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Creativity</label>
                                        <div class="mt-2">
                                            <select data-placeholder="Select creativity level" class="tail-select w-full"
                                                name="creativity" id="creativity_des">
                                                <option value="0">Repetitive</option>
                                                <option value="0.25"> Deterministic</option>
                                                <option value="0.5" selected=""> Original</option>
                                                <option value="0.75"> Creative</option>
                                                <option value="1"> Imaginative</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Tone of Voice</label>
                                        <div class="mt-2">
                                            <select id="tone_des" name="tone" data-placeholder="Select tone of voice"
                                                class="tail-select w-full">
                                                <option value="Professional" selected=""> Professional</option>
                                                <option value="Exciting"> Exciting</option>
                                                <option value="Friendly"> Friendly</option>
                                                <option value="Witty"> Witty</option>
                                                <option value="Humorous"> Humorous</option>
                                                <option value="Convincing"> Convincing</option>
                                                <option value="Empathetic"> Empathetic</option>
                                                <option value="Inspiring"> Inspiring</option>
                                                <option value="Supportive"> Supportive</option>
                                                <option value="Trusting"> Trusting</option>
                                                <option value="Playful"> Playful</option>
                                                <option value="Excited"> Excited</option>
                                                <option value="Positive"> Positive</option>
                                                <option value="Negative"> Negative</option>
                                                <option value="Engaging"> Engaging</option>
                                                <option value="Worried"> Worried</option>
                                                <option value="Urgent"> Urgent</option>
                                                <option value="Passionate"> Passionate</option>
                                                <option value="Informative"> Informative</option>
                                                <option value="Funny">Funny</option>
                                                <option value="Casual"> Casual</option>
                                                <option value="Sarcastic"> Sarcastic</option>
                                                <option value="Dramatic"> Dramatic</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Sentimate</label>
                                        <div class="mt-2">
                                            <select id="sentimate_des" name="sentimate"
                                                data-placeholder="Select tone of voice" class="tail-select w-full">
                                                <option value="Positive" selected>Positive</option>
                                                <option value="Negative">Negative</option>
                                                <option value="Neutral"> Neutral </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Translate</label>
                                        <div class="mt-2">
                                            <select id="translate_des" name="translate" class="input w-full border">
                                                <option value="no">No</option>
                                                <option value="yes" selected>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Max Result Length</label>
                                        <input type="number" class="input w-full border mt-2 " id="words_des"
                                            name="words" placeholder="e.g. 80" max="1000" value="{{ $max_words }}">
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <input type="button" class="button w-35 bg-theme-1 text-white" value="Submit"
                                            id="rewrite_desSubmit">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>{{ __('admin.location') }}</label>
                        <input type="text" class="input w-full border mt-2" name="location" id="location"
                            data-role="locationinput" value=""
                            placeholder="{{ __('admin.location_placeholder') }}">
                        <input type="text" name="latitude" id="latitude" style="display:none;"
                            value="@if (isset($blog->latitude)) {{ $blog->latitude }} @endif">
                        <input type="text" name="longitude" id="longitude" style="display:none;"
                            value="@if (isset($blog->longitude)) {{ $blog->longitude }} @endif">
                    </div>

                    <div class="mt-3">
                        <label class="cursor-pointer select-none width-25"
                            for="check_location_radius">{{ __('admin.check_location_radius') }}</label>
                        <input type="checkbox" class="input border mt-2" name="is_location_radius"
                            id="check_location_radius" data-role="check_location_radiusinput" <?php if ($blog->is_location_radius === 1) {
                                echo "checked='checked'";
                            } ?>>
                    </div>
                    <div class="mt-3">

                        <label>Swipe Left Mode Details</label>
                        <input type="text" class="input w-full border mt-2" name="swipe_text" id="swipe_text"
                            placeholder="swipe Left More Details" value="swipe Left More Details">

                    </div>


                    <div class="mt-3">
                        <div class="col-span-12 sm:col-span-4">
                            <input type="hidden" name="banner_image" id="banner_image" value="">
                            <label>({{ __('admin.banner_resolution') }}) <span class="required">*</span></label>
                            <div class="col-span-12 sm:col-span-12">
                                <input type="button" class="button w-30 bg-theme-1 text-white"
                                    value="{{ __('admin.upload_banner_image') }}"
                                    onclick="triggerFileInput('BannerimageuploadBtn')">

                                <input class="BannerimageuploadBtn hide" id="image" type="file"
                                    multiple="multiple" name="image[]"
                                    onchange="uploadMultipleBannerImage(this,'Bannerimage_image_add','add',0);"
                                    accept="image/jpg, image/jpeg, image/png" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="col-span-12 sm:col-span-12 mt-3 width-float">
                            <div id="Bannerimage_image_add">
                                @include('super-admin.blog.blog-images-list')
                            </div>
                            <span id="reImage"></span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div id='preview'>
                            <div class="col-span-12">
                                <div class="col-span-12 sm:col-span-12 pull-left" id="resultImage">
                                    <div>
                                        <p>Reimagined Image</p>
                                        @if (!empty($reimages))
                                            @foreach ($reimages as $reimage)
                                                <div class="image-container">
                                                    <a href="{{ url('/upload/blog/banner/temp_banner/' . $reimage->image) }}"
                                                        class="image-popup" title="">
                                                        <img src="{{ url('/upload/blog/banner/temp_banner/' . $reimage->image) }}"
                                                            class="multipleUpload">
                                                    </a>
                                                    <p class="mt-5 text-center">
                                                        <input type="radio" class="image-radio"
                                                            value="{{ $reimage->id }}" id="rimg{{ $reimage->id }}"
                                                            name="reimage" title="Use this banner">
                                                    </p>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-8">
                        <a href="{{ url('blog/') }}/{{ $layout }}/{{ $theme }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                            class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{ __('admin.back') }}</a>
                        @can('blog-edit')
                            {{-- <button type="button" id="saveBtnTop" class="button w-24 bg-theme-1 text-white mr-1 js-blog-save-keep-status"
                                onclick="syncBlogTranslationFields(); addUpdateBlog(event,'addUpdateBlog','save_keep_status')">{{ __('admin.save') }}</button> --}}
                            <button type="button" id="createBtnTop" class="button w-24 bg-theme-1 text-white js-blog-update-basic"
                                onclick="syncBlogTranslationFields(); addUpdateBlog(event,'addUpdateBlog','update_only')">{{ __('admin.update') }}</button>
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
                        <div class="col-span-12 sm:col-span-4">
                            <input type="hidden" name="audio_file_upload" id="audio_file_upload" value="">
                            <label>({{ __('admin.mp3_allowed') }})</label>
                            <div class="col-span-12 sm:col-span-12">
                                <input type="button" class="button w-30 bg-theme-1 text-white"
                                    value="{{ __('admin.upload_audio_image') }}"
                                    onclick="triggerFileInput('audio_file')">
                                <input class="audio_file hide" id="audio_file" type="file" name="audio_file"
                                    onchange="uploaudiofile(this,'audio_file_add','add',0);" accept="audio/mp3" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="col-span-12 sm:col-span-12 mt-3 width-float">

                            <?php $url = url('/upload/blog/audio/' . $blog->audio_file); ?>

                            <div id="audio_file_add">
                                <div id='audiopreview'>

                                    @if ($blog->audio_file != '')
                                        <audio controls>
                                            <source src="{{ $url }}" type="audio/mp3">
                                        </audio>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label>{{ __('admin.blog_url') }}</label>
                        <input type="text" class="input w-full border  source_url_input mt-2 mb-3" name="url"
                            placeholder="{{ __('admin.blog_url_placeholder') }}" value="{{ $blog->url }}">
                        <a href="{{ $blog->url }}" target="_blank" class="button w-30 bg-theme-1 text-white  source_url_button"
                            style="white-space: nowrap;">
                            View Source News
                        </a>
                    </div>
                    <div class="mt-3">
                        <label>{{ __('admin.source_name') }}</label>
                        <input type="text" class="input w-full border mt-2" name="source_name"
                            placeholder="{{ __('admin.source_name_placeholder') }}" value="{{ $blog->source_name }}">
                    </div>

                    <div class="mt-3">
                        <label>{{ __('admin.youtube_url') }}</label>
                        <input type="text" class="input w-full border mt-2" name="video_url"
                            placeholder="{{ __('admin.youtube_url_placeholder') }}" value="{{ $blog->video_url }}">
                    </div>

                    <div class="mt-3">
                        <div class="grid grid-cols-12 gap-4 row-gap-3">
                            @if ($blog->status == 2)
                                <div class="col-span-12 sm:col-span-4">
                                    <label>{{ __('admin.schedule_date') }}</label>
                                    <input type="date" class="input w-full border mt-2 form-control"
                                        name="schedule_date" placeholder="{{ __('admin.schedule_date_placeholder') }}" value="{{  date('Y-m-d') }}">
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <label>{{ __('admin.schedule_time') }}</label>
                                    <input type="time" class="input w-full border mt-2" name="schedule_time"
                                        placeholder="{{ __('admin.schedule_time_placeholder') }}" value="{{ date('H:i') }}">
                                </div>
                            @else
                                <div class="col-span-12 sm:col-span-4">
                                    <label>{{ __('admin.schedule_date') }}</label>
                                      <input type="date" class="input w-full border mt-2 form-control"
                                        name="schedule_date" placeholder="{{ __('admin.schedule_date_placeholder') }}" value="{{  $blog->schedule_date }}">
                                    {{-- <p>{{ $blog->schedule_date }}</p> --}}
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <label>{{ __('admin.schedule_time') }}</label>
                                     <input type="time" class="input w-full border mt-2" name="schedule_time"
                                        placeholder="{{ __('admin.schedule_time_placeholder') }}" value="{{ $blog->schedule_time }}">
                                    {{-- <p>{{ $blog->schedule_time }}</p> --}}
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">{{ __('admin.text_speech') }}</h2>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12"  style="z-index: 9999;">
                <div class="intro-y box p-5">
                    <div class="mt-3">
                        <div class="grid grid-cols-12 gap-4 row-gap-3">
                            <div class="col-span-12 sm:col-span-6">
                                <label>{{ __('admin.accent') }} / {{ __('admin.voice') }}</label>
                                <div class="mt-2">
                                    @php
                                        $selectedAccent = $blog->blog_accent_code ?: \Helpers::resolveBlogSpeechAccentForVoice($blog->voice ?? null);
                                        $accentVoiceOptions = \Helpers::getBlogSpeechAccentVoiceOptions($selectedAccent);
                                    @endphp
                                    <select data-placeholder="{{ __('admin.accent_plceholder') }}" id="blog_accent_code" name="blog_accent_code"
                                        class="tail-select w-full">
                                        <option value="">{{ __('admin.accent_plceholder') }}</option>
                                        @foreach ($accentVoiceOptions as $accentCode => $label)
                                            <option @if ($accentCode == $selectedAccent) selected @endif value="{{ $accentCode }}">{{ $label }}</option>
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
            <h2 class="text-lg font-medium mr-auto">{{ __('admin.seo_details') }}</h2>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <div class="intro-y box p-5">
                    <div class="mt-3">
                        <label>{{ __('admin.slug') }} <span class="required">*</span></label>
                        <input type="text" class="input w-full border mt-2" id="slug" name="slug"
                            placeholder="{{ __('admin.slug_placeholder') }}" value="{{ $blog->slug }}">
                    </div>

                    <div class="mt-3">
                        <label>{{ __('admin.tags') }}<font class="font-size10 text-danger">
                                ({{ __('admin.comma_saperate') }})</font></label>
                        <input type="text" class="input w-full border mt-2" name="tags" data-role="tagsinput"
                            value="{{ $blog->tags }}" placeholder="{{ __('admin.tags_placeholder') }}"
                            style="display:none;">
                    </div>
                    <div class="mt-3">
                        <label>{{ __('admin.title') }} ({{ __('admin.meta_tag') }})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_title"
                            placeholder="{{ __('admin.title_placeholder') }}" value="{{ $blog->seo_title }}">
                    </div>
                    <div class="mt-3">
                        <label>{{ __('admin.keywords') }} ({{ __('admin.meta_tag') }})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_keyword"
                            placeholder="{{ __('admin.keywords_placeholder') }}" value="{{ $blog->seo_keyword }}">
                    </div>
                    <div class="mt-3">
                        <label>{{ __('admin.tags') }} ({{ __('admin.meta_tag') }})</label>
                        <input type="text" class="input w-full border mt-2" name="seo_tag" data-role="tagsinput"
                            placeholder="{{ __('admin.tags_placeholder') }}" value="{{ $blog->seo_tag }}"
                            style="display:none;">
                    </div>
                    <div class="mt-3">
                        <label>{{ __('admin.description') }} ({{ __('admin.meta_tag') }})</label>
                        <div class="mt-2">
                            <div class="preview">
                                <textarea name="seo_description" class="input w-full border mt-2">{{ $blog->seo_description }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div></div>
        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">{{ __('admin.visibility') }}</h2>
            @if(auth()->check() && auth()->user()->type === 'admin')
                <a href="{{ url('blog-visibility-options/side-menu/light') }}" class="button button--sm border text-gray-600 text-xs" style="font-size:11px;padding:4px 10px;" title="Manage visibility options">
                    ⚙ Manage
                </a>
            @endif
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <div class="intro-y box p-5">

                    @php
                        $visibilityOptions = \App\Models\BlogVisibilityOption::getActive();
                    @endphp

                    @if($visibilityOptions->isEmpty())
                        <p class="text-gray-400 text-sm">No visibility options configured. <a href="{{ url('blog-visibility-options/side-menu/light') }}" class="text-theme-1">Add options →</a></p>
                    @else
                        @foreach($visibilityOptions as $visOpt)
                            <div class="mt-3">
                                <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">
                                    <label class="cursor-pointer select-none width-25" for="vis_{{ $visOpt->field_key }}">
                                        {{ $visOpt->label }}
                                    </label>
                                    <input type="checkbox"
                                        class="input border mr-2 visible-checkbox"
                                        id="vis_{{ $visOpt->field_key }}"
                                        name="{{ $visOpt->field_key }}"
                                        @if($blog->{$visOpt->field_key} == 1) checked="checked" @endif>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>


        <style>
            /* Make feature checkboxes more visible */
            input[type="checkbox"].visible-checkbox {
                width: 18px !important;
                height: 18px !important;
                border: 2px solid #000 !important;
                accent-color: #000 !important;
                -webkit-appearance: checkbox !important;
                appearance: checkbox !important;
                vertical-align: middle;
            }
            input[type="checkbox"].visible-checkbox:focus {
                outline: 2px solid rgba(0,0,0,0.15);
            }
            /* Ensure tail-select/select2 dropdown appears above other UI */
            .tail-select .select-dropdown,
            .tail-select .select-list,
            .ts-wrapper .ts-list,
            .select2-container .select2-dropdown,
            .select2-container .select2-results,
            .select-dropdown,
            .dropdown-menu {
                z-index: 99999 !important;
            }
        </style>

        <div class="intro-y flex items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">Voting question</h2>
        </div>
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">
                <div class="intro-y box p-5">
                    <div class="col-span-12 sm:col-span-3">
                        <div class="mt-3">
                            <label>{{ __('admin.enable_voting') }}</label>
                            <div class="mt-2">
                                <input type="checkbox" name="is_voting_enable" id="is_voting_enable"
                                    class="input input--switch border" @if ($blog->is_voting_enable == 1) checked @endif>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 showTopicInput @if ($blog->is_voting_enable != 1) hide @endif">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">

                            <input type="text" class="input border mr-2" id="" name="VotingQuestion"
                                value="{{ $blog->VotingQuestion }}" placeholder="Enter the topic" style="width: 50%">
                        </div>
                    </div>
                    <div class="mt-3 ">
                        <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2" style="display: contents;">
                            <label class="c select-none width-25" for="">Option Type</label>
                            <br>
                            <div class="mt-3">
                                <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2"
                                    style="margin-right: 10px;">

                                    <input type="radio" class="input border mr-2" id="" name="optiontype"
                                        value="0" <?php if ($blog->optiontype === 0) {
                                            echo "checked='checked'";
                                        } ?>> Yes/No
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center text-gray-700 dark:text-gray-500 mt-2">

                                    <input type="radio" class="input border mr-2" id="" name="optiontype"
                                        value="1" <?php if ($blog->optiontype === 1) {
                                            echo "checked='checked'";
                                        } ?>> Agree/Disagree
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="text-right mt-5">
                        <a href="{{ url('blog/') }}/{{ $layout }}/{{ $theme }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                            class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{ __('admin.back') }}</a>
                        @can('blog-edit')
                            <button type="button" id="saveBtn" class="button w-24 bg-theme-1 text-white mr-1 js-blog-save-keep-status"
                                onclick="syncBlogTranslationFields(); addUpdateBlog(event,'addUpdateBlog','save_keep_status')">{{ __('admin.save') }}</button>
                            @if (auth()->check() && auth()->user()->type === 'admin')
                                <button type="button" id="createBtn" class="button w-48 bg-theme-1 text-white js-blog-update-full"
                                    onclick="syncBlogTranslationFields(); addUpdateBlog(event,'addUpdateBlog')">{{ __('Save and publish') }}</button>
                            @endif
                        @endcan
                    </div>

                </div>
            </div>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('Google_api') }}&libraries=places"></script>
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('.source_url_input');
    const button = document.querySelector('.source_url_button');

    if (input && button) {
        input.addEventListener('keyup', function () {
            let val = input.value.trim();
            // Auto prepend https:// if it looks like a domain
            if (val && !/^https?:\/\//i.test(val)) {
                val = 'https://' + val;
            }
            button.setAttribute('href', val || '#');
        });
    }
});
        function initAutocomplete() {
            const input = document.getElementById("location");
            const autocomplete = new google.maps.places.Autocomplete(input);

            autocomplete.addListener("place_changed", function() {
                const place = autocomplete.getPlace();

                if (!place.geometry) {
                    alert("No details available for input: '" + place.name + "'");
                    return;
                }

                document.getElementById("latitude").value = place.geometry.location.lat();
                document.getElementById("longitude").value = place.geometry.location.lng();
            });
        }

        google.maps.event.addDomListener(window, 'load', initAutocomplete);

        function reverseGeocode() {
            const geocoder = new google.maps.Geocoder();
            const latlng = {
                lat: parseFloat(document.getElementById("latitude").value),
                lng: parseFloat(document.getElementById("longitude").value)
            };

            geocoder.geocode({
                location: latlng
            }, function(results, status) {
                console.log(results);
                console.log(status);
                if (status === "OK") {
                    if (results[0]) {
                        document.getElementById("location").value = results[0].formatted_address;
                    } else {

                    }
                } else {

                }
            });
        }

        window.onload = function() {
            reverseGeocode();
        };
    </script>
    <script>
        var _buzzifyBlogDescEditor = CKEDITOR.replace('blogdescription', {
            height: '250px',
        });

        function getBlogEditLang() {
            return ($('#edit_lang').val() || 'en').toString();
        }

        function convertToSlugIfEnglish(Text) {
            if (getBlogEditLang() === 'en' && typeof convertToSlug === 'function') {
                convertToSlug(Text);
            }
        }

        function syncBlogTranslationFields() {
            var lang = getBlogEditLang();
            var titleVal = $('#blogTitle').val();
            var descVal = (CKEDITOR.instances && CKEDITOR.instances.blogdescription) ? CKEDITOR.instances.blogdescription.getData() :
                ($('#blogdescription').val() || '');

            if (lang === 'hi') {
                $('#title_hi').val(titleVal);
                $('#description_hi').val(descVal);
            } else {
                $('#title_en').val(titleVal);
                $('#description_en').val(descVal);
            }
        }

        function loadBlogTranslationFields(lang) {
            var titleVal = $('#title_' + lang).val() || '';
            var descVal = $('#description_' + lang).val() || '';
            $('#blogTitle').val(titleVal);
            if (CKEDITOR.instances && CKEDITOR.instances.blogdescription) {
                CKEDITOR.instances.blogdescription.setData(descVal);
            } else {
                $('#blogdescription').val(descVal);
            }
        }

        function switchBlogEditLang(lang) {
            syncBlogTranslationFields();
            $('#edit_lang').val(lang);
            loadBlogTranslationFields(lang);
        }

        $(function() {
            $('#edit_lang_select').on('change', function() {
                switchBlogEditLang($(this).val());
            });

            // Ensure editor shows current language content (default: English)
            CKEDITOR.on('instanceReady', function(evt) {
                if (evt && evt.editor && evt.editor.name === 'blogdescription') {
                    loadBlogTranslationFields(getBlogEditLang());
                }
            });
        });
    </script>
    <script>
        function showtitlerewrite() {
            // Use jQuery's .is() and .css() methods
            if ($('#accordionTitle').is(':hidden')) {
                $('#accordionTitle').css('display', 'block');
            } else {
                $('#accordionTitle').css('display', 'none');
            }
        }

        function showdisrewrite() {
            // Use jQuery's .is() and .css() methods
            if ($('#accordionDes').is(':hidden')) {
                $('#accordionDes').css('display', 'block');
            } else {
                $('#accordionDes').css('display', 'none');
            }
        }

        $('#rewrite_submit').click(function() {
            var creativity = $('#creativity_title').val();
            var tone = $('#tone_title').val();
            var sentimate = $('#sentimate_title').val();
            var words = $('#words_title').val();
            var translate = $('#translate_title').val();
            var blogTitle = $('#blogTitle').val();
            var targetLang = getBlogEditLang();
            generateText(blogTitle, 'title', creativity, tone, sentimate, words, translate, targetLang);
        });

        $('#rewrite_desSubmit').click(function() {
            var creativity = $('#creativity_des').val();
            var tone = $('#tone_des').val();
            var sentimate = $('#sentimate_des').val();
            var words = $('#words_des').val();
            var translate = $('#translate_des').val();

            if (CKEDITOR.instances && CKEDITOR.instances.blogdescription) {
                var blogDescription = CKEDITOR.instances.blogdescription.getData();
                var targetLang = getBlogEditLang();
                generateText(blogDescription, 'description', creativity, tone, sentimate, words, translate, targetLang);
            } else {
                console.error('CKEditor is not initialized or instance not found.');
            }
        });

        function generateText(text, fieldType, creativity, tone, sentimate, words, translate, targetLang) {
            $.ajax({
                type: 'POST',
                url: "{{ route('generateText') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    title: text,
                    creativity: creativity,
                    tone: tone,
                    sentimate: sentimate,
                    words: words,
                    fieldType: fieldType,
                    translate: translate,
                    targetLang: targetLang
                },
                beforeSend: function() {
                    $('#processing').show();
                },
                success: function(response) {
                    if (response.choices && response.choices.length > 0 && response.choices[0].message &&
                        response.choices[0].message.content) {
                        var generatedText = response.choices[0].message.content;
                        var translatedText = response.translatedText || "";
                        var currentLang = getBlogEditLang();

                        // Set the generated text into respective fields
                        if (fieldType === 'title') {
                            $('#blogTitle').val(generatedText);
                            // Update hidden translation fields
                            if (currentLang === 'en') {
                                $('#title_en').val(generatedText);
                                if (translatedText) $('#title_hi').val(translatedText);
                            } else if (currentLang === 'hi') {
                                $('#title_hi').val(generatedText);
                                if (translatedText) $('#title_en').val(translatedText); // If Hindi translated to English
                            }
                        } else if (fieldType === 'description') {
                            if (CKEDITOR.instances.blogdescription) {
                                CKEDITOR.instances.blogdescription.setData(generatedText);
                            }
                            // Update hidden translation fields
                            if (currentLang === 'en') {
                                $('#description_en').val(generatedText);
                                if (translatedText) $('#description_hi').val(translatedText);
                            } else if (currentLang === 'hi') {
                                $('#description_hi').val(generatedText);
                                if (translatedText) $('#description_en').val(translatedText);
                            }
                        }
                    } else {
                        console.error('Invalid response format:', response);
                    }
                },
                complete: function() {
                    $('#processing').hide();
                },
                error: function(error) {
                    $('#processing').hide();
                    console.error('Error:', error);
                }
            });
        }

        // Handle the click event for reimagining the image
        function reimageThis(number) {
            // console.log(number);
            // Add your reimage logic here
            var imageUrl = $('#getImage' + number).attr('src');
            var id = $('#productId').val();

            // Send POST request to PHP endpoint for processing
            $.ajax({
                type: 'POST',
                url: "{{ route('reimagine') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    imageUrl: imageUrl,
                    id: id,
                },
                success: function(result) {
                    // Assuming responseData contains blog_id and imageName
                    var blogId = result.blog_id;
                    var imageName = result.reimage;
                    if (result.type == "success") {
                        // After successfully adding the image, retrieve all images associated with the blog_id
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('getReimage') }}",
                            data: {
                                "_token": "{{ csrf_token() }}",
                                blog_id: blogId,
                            },
                            success: function(res) {
                                $('#resultImage').html(res.html);
                            }
                        });
                    } else {
                        myToastr(result.msg, result.type);
                    }

                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    myToastr(error, 'error');
                }
            });
        }
    </script> 

    <script>
        // Accent/Voice selection is a single dropdown now; server derives the voice from the selected accent.
    </script>
@endsection
