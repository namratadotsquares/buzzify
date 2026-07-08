@foreach($blogs as $blog)
    <h4><a href="{{url('/blog-details')}}/{{$blog->slug}}" >{{$blog->title}}</a></h4>
    <img
        style="width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 10px;"
        src="{{url('upload/blog/banner/original/')}}/{{$blog->BlogImages->image}}"
        class="attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image"
        alt="{{$blog->title}}"
        onerror="this.onerror=null;this.src=`{{url('site/img/780x450.png')}}`;"
    />
    
    <div class="single_post_entry_content">
        <span class="single-post-meta-wrapper">
            @if($blog->author){
                <span class="post-author">
                
                    <img
                        src="{{url('upload/blog/banner/original/')}}/{{$blog->author->image}}"
                        width="30"
                        height="30"
                        alt="{{$blog->author->name}}"
                        class="avatar avatar-50 wp-user-avatar wp-user-avatar-50 alignnone photo"
                        onerror="this.onerror=null;this.src=`{{url('site/img/780x450.png')}}`;"
                    />
                    <a href="javascript:;" title="{{$blog->author->name}}" rel="author">{{$blog->author->name}}</a>
                </span>
            @endif
            <span class="post-date updated"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($blog->schedule_date))}}</span>
            <span class="view_options"><i class="fa fa-eye"></i>{{$blog->viewcount}}</span>
            @if(Auth::user()) 
                @if($blog->isBookmarked==0)
                    <span class="view_options cursor_pointer" onclick="bookmark('{{$blog->id}}','list');">
                        <i class="fa fa-bookmark-o" id="notmarked_{{$blog->id}}"></i>
                        <i class="fa fa-bookmark hide" id="marked_{{$blog->id}}"></i>
                    </span>
                @else
                    <span class="view_options cursor_pointer" onclick="bookmark('{{$blog->id}}','list');">
                        <i class="fa fa-bookmark-o hide" id="notmarked_{{$blog->id}}"></i>
                        <i class="fa fa-bookmark" id="marked_{{$blog->id}}"></i>
                    </span>
                @endif 
            @endif
            <span id="'.$randomId.'_btn" class="view_options cursor_pointer" onclick="togglePlay('.$randomId.','{{$blog->id}}')">
                    <i class="fa fa-volume-up" id="volume_btn"></i>
                </span>
            </span>
        </span>
    </div>    
    @if($blog->content_type  == 'video')
        @if($vdoUrl = \Helpers::getYoutubeEmbedUrl($blog->video_url))
            <iframe width="420" height="345" src="{{$vdoUrl}}" style="margin-top:50px;"></iframe>
        @else if($blog->content_type  == 'audio'){
            @if($blog->audio_file != '' && is_file(public_path("/upload/blog/audio/".$blog->audio_file))){
                $audio_file = url("/upload/blog/audio/".$blog->audio_file);
                <audio controls  style="width: 100%; margin-top:50px;"><source src="{{$audio_file}}" type="audio/mp3"></audio>
            @endif
        @endif
    @endif
    <audio controls id='{{$blog->randomId}}' class="hide"></audio>
    <div class="post_content">
        <p>
            <?php echo $blog->description;?>
        </p>
    </div>    
    <div class="clearfix"></div>
    <div class="single_tag_share">
        <div class="tag-cat">
            <ul class="single_post_tag_layout">
                @if($blog->tags!=null)
                   @for($i=0;$i<count($blog->tags_changed);$i++)
                        <li><a rel="tag">{{$blog->tags_changed[$i]}}</a></li>
                    @endfor
                @endif
            </ul>
        </div>
    </div>
    @if($blog->url!='')
        <div class="pull-left margin-bottom-20" style="margin-bottom:20px;">
            Source:<br>
            <ul class="single_post_tag_layout">
                <a href="{{$blog->url}}" target="_blank">{{$blog->url}}</a>
            </ul>
        </div>
    @endif
@endforeach