@extends('../site/layout/main')
@section('content')
<div class="options_layout_wrapper jl_radius jl_none_box_styles jl_border_radiuss no_transform">
    <div class="options_layout_container full_layout_enable_front no_transform">
        <!-- Start header -->
        @include('../site/layout/components/header_1')
        <!-- end header -->
        @include('../site/layout/components/overlay-menu')

        <!-- begin content -->
        <div class="jl_single_style8">
            <div class="single_captions_aboves_image_full_width_wrapper">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="jl_single_full_box">
                                <span class="image_grid_header_absolute" style="background-image: url('{{url('upload/blog/banner/original/')}}/{{$blog_detail['blog_image']}}');background-size: unset;"></span>
                                <span class="link_grid_header_absolute"></span>

                                <div class="single_post_entry_content single_post_caption_full_width_format">
                                    <span class="meta-category-small single_meta_category">
                                       <!-- @if($blog_detail->blog_category_data)
                                        <a class="post-category-color-text" style="background: {{$blog_detail->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$blog_detail->blog_category_data->category->slug}}">
                                            {{$blog_detail->blog_category_data->category->name}}
                                        </a>
                                        @endif-->
                                      @if(count($blog_detail->blog_category))
                                      	@foreach($blog_detail->blog_category as $blog_category_data)
                                          <a class="post-category-color-text" style="background: {{$blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$blog_category_data->category->slug}}">
                                              {{$blog_category_data->category->name}}
                                          </a>
                                     	 @endforeach
                                        @endif
                                    </span>
                                    <h1 class="single_post_title_main">
                                        {{$blog_detail['title']}}
                                    </h1>
                                    <!--<?php echo substr(strip_tags($blog_detail['description']),0,350); ?>...-->
                                    <span class="single-post-meta-wrapper">
                                        <span class="post-author">
                                            @if($blog_detail['author'])
                                            <img
                                                src="{{url('upload/author/original')}}/{{$blog_detail['author']->image}}"
                                                width="30"
                                                height="30"
                                                alt="{{$blog_detail['author']->name}}"
                                                class="avatar avatar-50 wp-user-avatar wp-user-avatar-50 alignnone photo"
                                                onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"
                                            />
                                            <a href="javascript:;" title="{{$blog_detail-['author']->name}}" rel="author">{{$blog_detail['author']->name}}</a>
                                            @endif
                                        </span>
                                        <span class="post-date updated"> <i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($blog_detail['schedule_date']))}} </span>
                                        <span class="view_options"> <i class="fa fa-eye"></i>{{$blog_detail['viewcount']}} </span>

                                        @if(Auth::user()) 
                                            @if($blog_detail['isBookmarked']==0)
                                                <span class="view_options" onclick="bookmark('{{$blog_detail['id']}}','detail');">
                                                    <i class="fa fa-bookmark-o" id="notmarked"></i>
                                                    <i class="fa fa-bookmark hide" id="marked"></i>
                                                </span>
                                            @else
                                                <span class="view_options cursor_pointer" onclick="bookmark('{{$blog_detail['id']}}','detail');">
                                                    <i class="fa fa-bookmark-o hide" id="notmarked"></i>
                                                    <i class="fa fa-bookmark" id="marked"></i>
                                                </span>
                                            @endif 
                                        @endif
                                        <span id="audio-123456_btn" class="view_options cursor_pointer" onclick="togglePlay('audio-123456',{{$blog_detail['id']}})">
                                            <i class="fa fa-volume-up" id="volume_btn"></i>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section id="content_main" class="clearfix jl_spost no_transform">
            <div class="container no_transform">
                <div class="row main_content no_transform">
                    <div class="col-md-8 loop-large-post" id="content">
                        <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide"></div>

                        @if($blog_detail['content_type'] == 'video') @if( $vdoUrl = \Helpers::getYoutubeEmbedUrl($blog_detail['video_url']))
                        <iframe width="420" height="345" src="{{$vdoUrl}}"></iframe>
                        @endif @elseif($blog_detail['content_type'] == 'audio')
                        <?php $audio_file = public_path("/upload/blog/audio/".$blog_detail['audio_file']); ?>
                        @if($blog_detail['audio_file'] != '' && is_file($audio_file))
                        <?php $audio_file = url("/upload/blog/audio/".$blog_detail['audio_file']); ?>
                        <audio controls class="width-100" style="width:100%;"><source src="{{$audio_file}}" type="audio/mp3" /></audio>
                        @endif @endif

                        <audio controls id="audio-123456" class="hide"></audio>

                        <div class="widget_container content_page" id="div_height">
                            <!-- start post -->
                            <div class="post-2963 post type-post status-publish format-standard has-post-thumbnail hentry category-science tag-gaming tag-inspiration" id="post-2963">
                                <div class="single_section_content box blog_large_post_style">
                                    <div class="post_content">
                                        <p>
                                            <?php echo $blog_detail['description']; ?>
                                        </p>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="single_tag_share">
                                        <div class="tag-cat">
                                            <ul class="single_post_tag_layout">
                                                @if($blog_detail['tags']!=null)
                                                <?php for($i=0;$i<count($blog_detail['tags']);$i++){ ?>
                                                <li><a rel="tag">{{$blog_detail['tags'][$i]}}</a></li>
                                                <?php } ?>
                                                @endif
                                            </ul>
                                        </div>
                                        @if($blog_detail->url!='')
                                        <div class="pull-left source-div">
                                            Source:<br>
                                            <ul class="single_post_tag_layout source-url">
                                                <a href="{{$blog_detail->url}}" target="_blank">{{$blog_detail->url}}</a>
                                            </ul>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- scoller load -->
									<input type="hidden" id="total_blogs" value="" />
                                    <input type="hidden" id="loaded_ids" value="{{$blog_detail['id']}}" />

                                    <div class="related-posts" id="load-data">
                                        <hr />

                                        <input type="hidden" id="active_category" value="" />
                                      	<input type="hidden" id="active_category_string" value="{{$blog_detail->blog_category_id_string}}" />
                                    </div>

                                    <div id="load_when_come_here"></div>

                                    <div class="related-posts">
                                        <h4>
                                            {{ __('frontend.related_article') }}
                                        </h4>

                                        <div class="single_related_post">
                                            @foreach($related_blogs as $related_post)
                                            <div class="jl_related_feature_items">
                                                <div class="jl_related_feature_items_in img_absolute_1">
                                                    <div class="image-post-thumb">
                                                        <a href="{{url('/blog-details')}}/{{$related_post->slug}}" class="link_image featured-thumbnail" title="{{$related_post->title}}">
                                                            <img
                                                                width="780"
                                                                height="450"
                                                                src="{{url('upload/blog/banner/360/')}}/{{ $related_post->blog_image }}"
                                                                class="blog_image_resize attachment-disto_large_feature_image size-disto_large_feature_image wp-post-image main_img_1"
                                                                alt="{{$related_post->title}}"
                                                                onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';"
                                                            />

                                                            <div class="background_over_image">
                                                                @if($related_post->content_type == 'audio')
                                                                <img src="{{url('upload/audio-image.png')}}" class="child_img_1" />
                                                                @elseif($related_post->content_type == 'video')
                                                                <img src="{{url('upload/video-image.png')}}" class="child_img_1" />
                                                                @endif
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <span class="meta-category-small">
                                                        @if($related_post->blog_category_data)
                                                        <a class="post-category-color-text" style="background: {{$related_post->blog_category_data->category->color}};" href="{{url('category-blog')}}?category={{$related_post->blog_category_data->category->slug}}">
                                                            {{$related_post->blog_category_data->category->name}}
                                                        </a>
                                                        @endif
                                                    </span>
                                                    <div class="post-entry-content">
                                                        <h3 class="jl-post-title"><a href="{{url('/blog-details')}}/{{$related_post->slug}}"> {{$related_post->title}}</a></h3>
                                                        <span class="jl_post_meta">
                                                            <span class="jl_author_img_w">
                                                                @if($related_post->author)
                                                                <img
                                                                    src="{{url('upload/author/original')}}/{{$related_post->author->image}}"
                                                                    width="30"
                                                                    height="30"
                                                                    alt="{{$related_post->author->name}}"
                                                                    class="avatar avatar-30 wp-user-avatar wp-user-avatar-30 alignnone photo"
                                                                    onerror="this.onerror=null;this.src='{{url('site/img/120x120.png')}}';"
                                                                />
                                                                <a href="javascript:;" title="{{$related_post->author->name}}" rel="author">{{$related_post->author->name}}</a>
                                                                @endif
                                                            </span>
                                                            <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($related_post->schedule_date))}}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                            <div class="clear_3col_related"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end post -->
                            <div class="brack_space"></div>
                        </div>
                    </div>

                    <!-- start sidebar -->
                    @include('../site/layout/components/side_content')
                    <!-- end sidebar -->
                </div>
            </div>
        </section>
        <!-- end content -->
        <!-- Start footer -->
        @include('../site/layout/components/footer')
        <!-- End footer -->
    </div>
</div>
<div id="go-top">
    <a href="#go-top"><i class="fa fa-angle-up"></i></a>
</div>

@endsection