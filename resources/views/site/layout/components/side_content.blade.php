<div class="col-md-4" id="sidebar">
    <div id="disto_recent_post_widget-7" class="widget post_list_widget">
        <div class="widget_jl_wrapper">
            <span class="jl_none_space"></span>
            <div class="widget-title">
                <h2>{{ __('frontend.recent_posts') }}</h2>
            </div>
            <div>
                <ul class="feature-post-list recent-post-widget">
                    @foreach($side_recent_blog as $side_post)
                    <li>
                        <a href="{{url('/blog-details')}}/{{$side_post->slug}}" class="jl_small_format feature-image-link image_post featured-thumbnail" title="{{$side_post->title}}">
                            <img src="{{url('upload/blog/banner/360/')}}/{{$side_post->blog_image}}" alt="{{$side_post->title}}" onerror="this.onerror=null;this.src='{{url('site/img/780x450.png')}}';" class="fishes" />

                            <div class="background_over_image">
                                @if($side_post->content_type  == 'audio')
                                <img src="{{url('upload/audio-image.png')}}" class="child_img_1"/>
                            @elseif($side_post->content_type  == 'video')
                                <img src="{{url('upload/video-image.png')}}" class="child_img_1"/>
                            @endif
                            </div>
                        </a>
                        <div class="item-details">
                            <span class="meta-category-small">
                                @if($side_post->blog_category_data)
                                    <a class="post-category-color-text" style="background:<?php echo $side_post->blog_category_data->category->color;?>" href="{{url('category-blog')}}?category={{$side_post->blog_category_data->category->slug}}">{{$side_post->blog_category_data->category->name}}</a>
                                @endif
                            </span>
                            <h3 class="feature-post-title"><a href="{{url('/blog-details')}}/{{$side_post->slug}}"> <?php echo substr(strip_tags($side_post->title),0,30);?>...</a></h3>
                            <span class="post-meta meta-main-img auto_image_with_date">
                                <span class="post-date"><i class="fa fa-clock-o"></i>{{date('M d, Y',strtotime($side_post->schedule_date))}}</span>
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            <span class="jl_none_space"></span>
        </div>
    </div>
    <span class="jl_none_space"></span>
</div>