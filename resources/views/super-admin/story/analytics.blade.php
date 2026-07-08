@extends('../layout/' . $layout) @section('subhead')
<title>{{__('admin.story')}} - {{setting('site_name')}}</title>
@endsection @section('subcontent') @include('../layout/components/top-bar')
<style type="text/css">
    .tabcontent {
        display: none;
        padding: 6px 12px;
        /*border: 1px solid #ccc;*/
        border-top: none;
    }
    .tab button.active {
        background-color: #ccc;
    }
    
    .pr-pl{
        padding-left: 4.75rem;
        padding-right: 4.75rem;
    }
    .p-18{
       /* padding: 1.8rem;*/
       padding-top: 0.5rem;
       padding-bottom: 0.5rem;
        padding-right: 1.8rem;
        padding-left: 1.8rem;
    }

    /* Make video thumbnail clickable (select radio) */
    #story-media-list video {
        pointer-events: none;
    }

    
</style>
<h2 class="intro-y text-lg font-medium mt-10">@if(strlen($analytics['Detail']->name)>60) {{substr($analytics['Detail']->name,0,60)}}... @else {{$analytics['Detail']->name}} @endif  - {{__('admin.story')}} {{__('admin.analytics')}}</h2><div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-12 xxl:col-span-12 flex lg:block flex-col-reverse">
        <div class="intro-y box mt-6">
            <div class="p-18"> 
        
            </div>
    
            <div class=" tabcontent" id="poll" style="display: block;">
                <div class="p-5"> 
                    <div class="flex items-center border-b border-gray-200 dark:border-dark-5">
                        <h2 class="font-medium text-base mr-auto">
                        </h2>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        @php
                                            $mediaFiles = [];
                                            if (isset($analytics['Detail']->files) && is_array($analytics['Detail']->files) && count($analytics['Detail']->files)) {
                                                $mediaFiles = $analytics['Detail']->files;
                                            } elseif (isset($analytics['Detail']->file) && $analytics['Detail']->file) {
                                                $mediaFiles = [$analytics['Detail']->file];
                                            }
                                            $defaultMedia = $mediaFiles[0] ?? '';
                                        @endphp

                                        <div class="w-full">
                                            <div id="story-media-preview" class="w-full">
                                                @php
                                                    $file = $defaultMedia;
                                                    $fileType = $file ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : '';
                                                @endphp
                                                @if(!$fileType)
                                                    There is not any media images, videos, and document uploaded!
                                                @elseif(in_array($fileType, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'wmv', '3gp', 'flv']))
                                                    <video width="320" height="240" controls>
                                                        <source src="{{ $file }}" type="video/{{ $fileType }}">
                                                    </video>
                                                @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <img src="{{ $file }}" width="320" height="240" alt="Image">
                                                @else
                                                    <a href="{{ $file }}" target="_blank">View Document</a>
                                                @endif
                                            </div>

                                            @if(count($mediaFiles) > 1)
                                                <div class="mt-4">
                                                    <div class="text-xs mb-2"><b>Select one media</b> (this will be used as the primary file)</div>
                                                    <div class="grid grid-cols-3 gap-2" id="story-media-list">
                                                        @foreach($mediaFiles as $idx => $m)
                                                            @php
                                                                $ext = $m ? strtolower(pathinfo($m, PATHINFO_EXTENSION)) : '';
                                                                $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'wmv', '3gp', 'flv']);
                                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                            @endphp
                                                            <label class="border rounded p-1 cursor-pointer" style="display:block;" onclick="selectStoryMedia('{{ $m }}', this)">
                                                                <input type="radio" name="story_media" value="{{ $m }}" @if($idx===0) checked @endif style="margin-right:6px;" onchange="updateStoryPreview(this.value)">
                                                                <div style="margin-top:6px;">
                                                                    @if($isVideo)
                                                                        <video width="90" height="60" muted>
                                                                            <source src="{{ $m }}" type="video/{{ $ext }}">
                                                                        </video>
                                                                    @elseif($isImage)
                                                                        <img src="{{ $m }}" width="90" height="60" alt="Image" style="object-fit:cover;">
                                                                    @else
                                                                        <span class="text-xs">Document</span>
                                                                    @endif
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                           {!! $analytics['Detail']->story !!}
                        </div>

                       
                                    @if($analytics['Detail']->status==0)
                                        <a href="javascript:void(0);" class="btn btn-primary" onclick="confirmApproval({{ $analytics['Detail']->id }}, 1, {{ $analytics['Detail']->user_id }})">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.accept')}}
                                            </div>
                                        </a>
                                        <a href="{{url('change-status-story/')}}/{{$analytics['Detail']->id}}/2/{{$analytics['Detail']->user_id}}" >
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.reject')}}
                                            </div>
                                        </a>
                                    @elseif($analytics['Detail']->status==2)
                                        <a href="javascript:void(0);" class="btn btn-primary" onclick="confirmApproval({{ $analytics['Detail']->id }}, 1, {{ $analytics['Detail']->user_id }})">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.accept')}}
                                            </div>
                                        </a>
                                    @else
                                        <!--<a href="{{url('change-status-story/')}}/{{$analytics['Detail']->id}}/2/{{$analytics['Detail']->user_id}}">-->
                                        <!--    <div class="flex items-center justify-center text-theme-6">-->
                                        <!--        <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.reject')}}-->
                                        <!--    </div>-->
                                        <!--</a>-->
                                    @endif

                                    <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
                                        @if($isRewardEligible==true && ($analytics['Detail']->status==0 || $analytics['Detail']->status==2))
                                        <p><b>Note:</b> This is {{$wallet_stories_count}} Storie of this Users If Approved He will get <b>{{$amount}} Points</b> after approve.</p>
                                        @endif
                                    </div>
                    </div>
                </div>
            </div>
       
        </div>
    </div>
</div>
<!-- Reward Prompt Modal -->
<div id="rewardModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; padding:20px; margin:10% auto; width:300px; text-align:center;">
        <p>Do you want to give extra reward points?</p>
        <input type="number" id="extraPoints" class="form-control mt-2 mb-3" placeholder="Enter extra points">
        <button onclick="submitApproval()" class="btn btn-success">Submit</button>
        <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function selectStoryMedia(url, containerEl) {
        if (!containerEl) return;
        var radio = containerEl.querySelector('input[type=\"radio\"][name=\"story_media\"]');
        if (radio) {
            radio.checked = true;
        }
        updateStoryPreview(url);
    }

    function updateStoryPreview(url) {
        if (!url) return;
        var ext = '';
        try {
            ext = url.split('.').pop().split('?')[0].toLowerCase();
        } catch (e) {
            ext = '';
        }

        var preview = document.getElementById('story-media-preview');
        if (!preview) return;

        var videoExts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'wmv', '3gp', 'flv'];
        var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (videoExts.indexOf(ext) !== -1) {
            preview.innerHTML = '<video width=\"320\" height=\"240\" controls><source src=\"' + url + '\" type=\"video/' + ext + '\"></video>';
        } else if (imageExts.indexOf(ext) !== -1) {
            preview.innerHTML = '<img src=\"' + url + '\" width=\"320\" height=\"240\" alt=\"Image\">';
        } else {
            preview.innerHTML = '<a href=\"' + url + '\" target=\"_blank\">View Document</a>';
        }
    }

    function confirmApproval(storyId, status, userId) {
        Swal.fire({
            title: 'Give Extra Reward Points?',
            text: "You can leave it empty if you don't want to give extra points.",
            input: 'number',
            inputPlaceholder: 'Enter extra points (optional)',
            showCancelButton: true,
            confirmButtonText: 'Approve',
            cancelButtonText: 'Cancel',
            inputAttributes: {
                min: 0
            },
            preConfirm: (rewardPoints) => {
                var selectedMediaEl = document.querySelector('input[name=\"story_media\"]:checked');
                var selectedMedia = selectedMediaEl ? selectedMediaEl.value : '';
                var selectedExt = '';
                try {
                    selectedExt = selectedMedia.split('.').pop().split('?')[0].toLowerCase();
                } catch (e) {
                    selectedExt = '';
                }
                var videoExts = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'wmv', '3gp', 'flv'];
                if (selectedMedia && videoExts.indexOf(selectedExt) !== -1) {
                    Swal.showValidationMessage('You can not select video. Please select an image.');
                    return false;
                }

                return new Promise((resolve) => {
                    var selectedMediaEl = document.querySelector('input[name=\"story_media\"]:checked');
                    var selectedMedia = selectedMediaEl ? selectedMediaEl.value : '';
                    $.ajax({
                        url: `/change-status-story/${storyId}/${status}/${userId}`,
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            reward_points: rewardPoints,
                            selected_media: selectedMedia,
                            theme:'{{ $theme }}',
                            layout:'{{ $layout }}'
                        },
                        success: function (response) {
                            Swal.fire('Approved!', response.message, 'success').then(() => {
                                window.location.href = response.redirect_url;
                            });
                            resolve(response);
                        },
                        error: function () {
                            Swal.fire('Error', 'Something went wrong!', 'error');
                            resolve(false);
                        }
                    });
                });
            }
        });
    }
    </script>
    
@endsection
