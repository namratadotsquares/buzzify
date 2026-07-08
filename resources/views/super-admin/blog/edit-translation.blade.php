@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.edit_blog_translation')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')
    <style>
        .accordion-content {
            display: none;
            border: 1px solid #edebeb;
            padding:10px;
            margin-top: 25px;
        }
        .image-container {
            display: inline-block;
            vertical-align: top;
            margin-right: 10px; /* Adjust as needed */
        }
        
        .image-checkbox-label {
            display: inline-block;
            margin-right: 5px; /* Adjust as needed */
        }
        
        .image-checkbox-label input[type="checkbox"] {
         margin: 0;
        }
        
        .delete-icon {
            display: inline-block;
            vertical-align: top;
            cursor: pointer;
        }
        a{
            cursor:pointer;
        }
     </style>
<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.edit_blog_translation')}}</h2>
    </div>
    <form id="addUpdateBlog">
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">

                <input type="hidden" name="blog_id" id="blog_id" value="{{$blog->id}}">
                <input type="hidden" name="layout" id="layout" value="{{$layout}}">
                <input type="hidden" name="theme" id="theme" value="{{$theme}}">
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
                    <div class="mt-3 float-right">
                        <button type="button" id="rewrite_title_button" onclick="showtitlerewrite();" class="button w-35 bg-theme-1 text-white">+ Rewrite Title</button>
                   </div>
                   <div class="mt-3 p-5">
                       <div class="accordion">
                           <div class="accordion-content mt-3" id="accordionTitle">
                               <div class="grid grid-cols-12 gap-4 row-gap-3">
                                   <div class="col-span-12 sm:col-span-3">
                                       <label>Creativity</label>
                                       <div class="mt-2">
                                           <select data-placeholder="Select creativity level"  class="tail-select w-full" name="creativity" id="creativity_title">
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
                                           <select id="tone_title" name="tone" data-placeholder="Select tone of voice" class="tail-select w-full">
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
                                           <select id="sentimate_title" name="sentimate" data-placeholder="Select tone of voice" class="tail-select w-full" >
                                               <option value="Positive" selected>Positive</option>
                                               <option value="Negative">Negative</option>
                                               <option value="Neutral"> Neutral </option>																															
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-span-12 sm:col-span-3">
                                       <label>Max Result Length</label>
                                               <input type="number" class="input w-full border mt-2 " id="words_title" name="words" placeholder="e.g. 25" max="1000" value="25">
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
                                   <div class="col-span-12 sm:col-span-6">
                                       <input type="button" class="button w-35 bg-theme-1 text-white" value="Submit" id="rewrite_submit">
                                   </div>  
                               </div>
                           </div>
                       </div>
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
                                <small id="wordCountMessage" style="color: red;"></small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 float-right">
                       <button type="button" id="rewrite_des_button" onclick="showdisrewrite();" class="button w-35 bg-theme-1 text-white">+ Rewrite Description</button>
                    </div>
                    <div class="mt-3 p-5">
                        <div class="accordion">
                            <div class="accordion-content mt-3" id="accordionDes">
                                <div class="grid grid-cols-12 gap-4 row-gap-3">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label>Creativity</label>
                                        <div class="mt-2">
                                            <select  data-placeholder="Select creativity level"  class="tail-select w-full" name="creativity" id="creativity_des">
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
                                            <select id="tone_des" name="tone" data-placeholder="Select tone of voice" class="tail-select w-full">
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
                                            <select id="sentimate_des" name="sentimate" data-placeholder="Select tone of voice" class="tail-select w-full" >
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
                                                <input type="number" class="input w-full border mt-2 " id="words_des" name="words" placeholder="e.g. 80" max="1000" value="100">
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <input type="button" class="button w-35 bg-theme-1 text-white" value="Submit" id="rewrite_desSubmit">
                                    </div>  
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.location')}}</label>
                        <input type="text" class="input w-full border mt-2" name="location" id="location" data-role="locationinput" value="" placeholder="{{__('admin.location_placeholder')}}" >
                        <input type="text" name="latitude"  id="latitude" style="display:none;" value="@if(isset($blog->latitude)) {{$blog->latitude}} @endif">
                        <input type="text" name="longitude"  id="longitude" style="display:none;"  value="@if(isset($blog->longitude)) {{$blog->longitude}} @endif">
                    </div>
                    <div class="mt-3">
                        <label class="cursor-pointer select-none width-25" for="check_location_radius">{{__('admin.check_location_radius')}}</label>
                        <input type="checkbox" class="input border mt-2" name="check_location_radius" id="check_location_radius" data-role="check_location_radiusinput"  <?php if($blog->is_location_radius===1){ echo "checked='checked'"; } ?> >
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
                       @can('blog-edit')
                             <button type="button" id="createBtn" class="button w-24 bg-theme-1 text-white" onclick="translateBlog(event,'addUpdateBlog')">{{__('admin.update')}}</button>
                         @endcan
                     </div>
                </div>
            </div>
        </div>
    </form>

    <script src="https://maps.googleapis.com/maps/api/js?key={{env('Google_api')}}&libraries=places"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function initAutocomplete() {
            const input = document.getElementById("location");
            const autocomplete = new google.maps.places.Autocomplete(input);
    
            autocomplete.addListener("place_changed", function () {
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
        const latlng = { lat: parseFloat(document.getElementById("latitude").value), lng: parseFloat(document.getElementById("longitude").value) };

        geocoder.geocode({ location: latlng }, function (results, status) {
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

    window.onload = function () {
        reverseGeocode();
    };
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('blogdescription');
        const message = document.getElementById('wordCountMessage');

        textarea.addEventListener('input', function () {
            const words = textarea.value.trim().split(/\s+/);
            const wordCount = words.filter(word => word.length > 0).length;

            if (wordCount > 80) {
                message.textContent = 'You can only enter up to 80 words.';
                // Trim to 80 words
                textarea.value = words.slice(0, 80).join(' ');
            } else {
                message.textContent = `Words: ${wordCount}/80`;
            }
        });
});
</script>
    <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
    <script>
        CKEDITOR.replace( 'blogdescription',{
            height: '460px',
        } );
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
               console.log('1-------');
                   var creativity = $('#creativity_title').val();
                   var tone = $('#tone_title').val();
                   var sentimate = $('#sentimate_title').val();
                   var words = $('#words_title').val();
                   var translate = $('#translate_title').val();
                   
               var blogTitle = $('#title').val();
               generateText(blogTitle, 'title',creativity,tone,sentimate,words,translate); // Call generateText function with 'title' as the field type
           });
       
           $('#rewrite_desSubmit').click(function() {
               console.log('2-------');
                   var creativity = $('#creativity_des').val();
                   var tone = $('#tone_des').val();
                    var sentimate = $('#sentimate_des').val();
                    var words = $('#words_des').val();
                    var translate = $('#translate_des').val();
        
                 // Check if CKEditor is initialized
                    if (CKEDITOR.instances && CKEDITOR.instances.blogdescription) {
                        var blogDescription = CKEDITOR.instances.blogdescription.getData(); // Get content from CKEditor
                        generateText(blogDescription, 'description',creativity,tone,sentimate,words,translate);
                   } else {
                       console.error('CKEditor is not initialized or instance not found.');
                   }
           });
       
           function generateText(text, fieldType,creativity,tone,sentimate,words,translate) {
        
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
                        fieldType:fieldType,
                        translate: translate
                    },
                   beforeSend: function() {
                       
                           $('#processing').show(); 
                       },
                   success: function(response) {
                       // Handle response here, display generated text
                       if (response.choices && response.choices.length > 0 && response.choices[0].message && response.choices[0].message.content) {
                           var generatedText = response.choices[0].message.content; // Extract generated text
                   
                           // Set the generated text into respective fields
                           if (fieldType === 'title') {
                               $('#title').val(generatedText); // Update the value of the title input field
                           } else if (fieldType === 'description') {
                               // Ensure CKEditor is initialized
                               if (CKEDITOR.instances.blogdescription) {
                                   CKEDITOR.instances.blogdescription.setData(generatedText); // Set content in CKEditor
                               } else {
                                   console.error('CKEditor instance not found.');
                               }
                           }
                       } else {
                           console.error('Invalid response format:', response);
                       }
                   },
                   error: function(error) {
                       console.error('Error:', error);
                   }
               });
           }
       
           // Handle the click event for reimagining the image
           function reimageThis(number) {
               // console.log(number);
               // Add your reimage logic here
               var imageUrl = $('#getImage'+number).attr('src');
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
                       if(result.type=="success"){
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
                       }else{
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
@endsection
