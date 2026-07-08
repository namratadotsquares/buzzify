<style>
.single_post_title_main a {
  color: #fff !important;
}
.single_post_title_main{
    margin-top:15px !important;
}
</style>
@foreach($blogs as $blog)
<div class="jl_single_full_box">
    <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/original/')}}/{{$blog->BlogImages->image}}');background-size: unset;"></span>
    <span class="link_grid_header_absolute"></span>

    <div class="single_post_entry_content single_post_caption_full_width_format">
        <span class="meta-category-small single_meta_category">
            
            @if(count($blog->blog_category))
            @foreach($blog->blog_category as $blog_category_data)
                <a class="post-category-color-text" style="background: {{$blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$blog_category_data->category->slug}}">
                    {{$blog_category_data->category->name}}
                </a>
                @endforeach
            @endif
        </span>
        <h1 class="single_post_title_main">
            <a href="{{url('/blog-details')}}/{{$blog->slug}}" >{{$blog->title}}</a>
        </h1>
        <span class="single-post-meta-wrapper">
            <span class="post-date updated"> <i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($blog->schedule_date))}} </span>
            <span class="view_options"> <i class="fa fa-eye"></i>{{$blog->viewcount}} </span>

            @if(Auth::user()) 
                @if($blog->isBookmarked==0)
                    <span class="view_options" onclick="bookmark('{{$blog->id}}','detail');">
                        <i class="fa fa-bookmark-o" id="notmarked"></i>
                        <i class="fa fa-bookmark hide" id="marked"></i>
                    </span>
                @else
                    <span class="view_options cursor_pointer" onclick="bookmark('{{$blog->id}}','detail');">
                        <i class="fa fa-bookmark-o hide" id="notmarked"></i>
                        <i class="fa fa-bookmark" id="marked"></i>
                    </span>
                @endif 
            @endif
            <span id="audio-123456_btn" class="view_options cursor_pointer" onclick="togglePlay('audio-123456','{{$blog->id}}')">
                <i class="fa fa-volume-up" id="volume_btn"></i>
            </span>
        </span>
    </div>
</div>
<div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>

@if($blog->content_type == 'video') @if( $vdoUrl = \Helpers::getYoutubeEmbedUrl($blog->video_url))
    <iframe width="420" height="345" src="{{$vdoUrl}}"></iframe>
@endif @elseif($blog->content_type == 'audio')
    <?php $audio_file = public_path("/upload/blog/audio/".$blog->audio_file); ?>
@if($blog->audio_file != '' && is_file($audio_file))
    <?php $audio_file = url("/upload/blog/audio/".$blog->audio_file); ?>
    <audio controls class="width-100" style="width:100%;"><source src="{{$audio_file}}" type="audio/mp3" /></audio>
@endif @endif

<audio controls id="audio-123456" class="hide"></audio>

<div class="widget_container content_page" id="div_height">
    <!-- start post -->
    <div class="post-2963 post type-post status-publish format-standard has-post-thumbnail hentry category-science tag-gaming tag-inspiration" id="post-2963">
        <div class="single_section_content box blog_large_post_style">
            <div class="post_content" style="padding: 0;">
                <p>
                    <?php echo $blog->description; ?>
                </p>
            </div>
            <div class="clearfix"></div>
            <div class="single_tag_share">
                <div class="tag-cat">
                    <ul class="single_post_tag_layout">
                        @if($blog->tags!=null)
                        <?php for($i=0;$i<count($blog->tags);$i++){ ?>
                        <li><a rel="tag">{{$blog->tags[$i]}}</a></li>
                        <?php } ?>
                        @endif
                    </ul>
                </div>
                @if($blog->url!='')
                <div class="pull-left source-div">
                    <p class="source-name">Source:</p>
                    <ul class="single_post_tag_layout source-url">
                        <a href="{{$blog->url}}" target="_blank">{{$blog->url_host}}</a>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- end post -->
    <div class="brack_space"></div>
</div>
<hr style="width:100%;float:left;"/>
@endforeach
