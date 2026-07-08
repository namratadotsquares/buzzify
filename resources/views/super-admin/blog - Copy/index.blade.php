@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.blog_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
<style type="text/css">
    .w-50{
      width: 11rem;
    }
</style>
    @include('../layout/components/top-bar')
    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.blog_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        

        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">

            <!-- @can('blog-create')
                <a href="{{url('/add-blog')}}/{{$layout}}/{{$theme}}" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_blog')}}</a>
            @endcan -->

            <div class="hidden md:block mx-auto text-gray-600">  </div>

            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        @if(isset($_GET['post']))
                        <input type="hidden"  value="{{$_GET['post']}}" name="post">
                        @endif
                        <select data-placeholder="" name="status" class="input w-50 mt-5 box pr-10 placeholder-theme-13">
                            <option value="">Select Status</option>
                            <option @if(isset($_GET['status']) && $_GET['status']!='') @if($_GET['status'] == 1) selected @endif @endif value="1">Active</option>
                            <option @if(isset($_GET['status']) && $_GET['status']!='') @if($_GET['status'] == 0) selected @endif @endif value="0">In Active</option>
                        </select>

                         <select data-placeholder="{{__('admin.select_category')}}" name="category_id" class="input w-50 mt-5 box pr-10 placeholder-theme-13">
                            <option value="" >{{__('admin.select_category')}}</option>
                            @foreach($category as $cat)
                                <option value="{{$cat->id}}" @if(isset($_GET['category_id']) && $_GET['category_id']!='') @if($cat->id == $_GET['category_id']) selected  @endif @endif >{{$cat->name}}</option>
                            @endforeach
                        </select>
                        <input type="text" class="input w-40 box pr-10 placeholder-theme-13" @if(isset($_GET['name'])) value="{{$_GET['name']}}" @endif name="name" placeholder="{{__('admin.blog_search')}}">

                        <input type="date" class="input w-40  box  placeholder-theme-13" name="from_date" placeholder="{{__('admin.from_date')}}"  @if(isset($_GET['from_date'])) value="{{$_GET['from_date']}}" @endif>
                        <input type="date" class="input w-40 box  placeholder-theme-13" name="to_date" placeholder="{{__('admin.to_date')}}"  @if(isset($_GET['to_date'])) value="{{$_GET['to_date']}}" @endif>
                        <!-- <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a>  -->
                        <input type="submit" id="search" value="Search" class="button text-white bg-theme-1 shadow-md mr-2 ml-2" name="search">
                    </form>
                </div>
            </div>
            <?php ?>
            <button class="button ml-2  mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()" style="margin-top: 26px;">{{__('admin.reset')}}</button>

        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.title')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.category')}}</th>
                        <!-- <th class="whitespace-no-wrap">{{__('admin.posted_by')}}</th> -->
                        <th class="whitespace-no-wrap width-20">{{__('admin.visibility')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.views')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.created_at')}}</th>
                        @can('blog-status')
                        <th class="text-center whitespace-no-wrap">{{__('admin.status')}}</th>
                        @endcan
                        @if(Gate::check('blog-edit') || Gate::check('blog-delete') || Gate::check('blog-translate') || Gate::check('blog-send-notification') || Gate::check('blog-analytics'))
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    <?php $query = '';
                        if(isset($_GET['post'])){
                            $query = '?post='.$_GET['post'];
                        }
                    ?>
                    @if(count($blog))
                        <?php
                        $page = (isset($_GET['page']))?$_GET['page']:1;
                        if($page>1){
                            $i = $blog->perPage() * ($blog->currentPage() - 1) + 1;
                        }
                        else{
                            $i=1;
                        }
                        ?>
                        @foreach ($blog as $row)
                            <tr class="intro-x" >
                                <td >
                                    {{$i}}
                                </td>
                                <td>
                                    <a href="{{$row->blog_image}}" class="image-popup" title="{{$row->name}}">
                                        <img src="{{$row->blog_image}}" class="thumb-img-list" alt="{{$row->name}}" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    </a>
                                </td>
                                <td>{{ substr($row->title, 0, 10) }}... ({{$row->blog_accent_code}})</td>
                              <td>@if($row->blog_category_name) {{ $row->blog_category_name }} @else -- @endif</td>
                                <!--<td>@if($row->category) {{ $row->category->name }} @endif</td>-->
                                <!-- <td>{{ $row->created_by_name }}</td> -->
                                <td class="text-center">
                                    @if($row->is_featured==1 || $row->is_slider==1 || $row->is_editor_picks==1|| $row->is_weekly_top_picks==1)
                                        @if($row->is_featured==1)
                                            <button class="button button--sm mr-1 bg-theme-1 text-white featured-btn">{{__('admin.featured')}}</button>
                                        @endif
                                        @if($row->is_slider==1)
                                            <button class="button button--sm mr-1 bg-theme-9 text-white featured-btn">{{__('admin.slider')}}</button>
                                        @endif
                                        @if($row->is_editor_picks==1)
                                            <button class="button button--sm mr-1 bg-theme-12 text-white featured-btn" >{{__('admin.editor_picks')}}</button>
                                        @endif
                                        @if($row->is_weekly_top_picks==1)
                                            <button class="button button--sm mr-1 bg-theme-6 text-white featured-btn">{{__('admin.weekly_top_picks')}}</button>
                                        @endif
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>{{ $row->viewcount }}</td>
                                <td>{{ date(setting('date_format'),strtotime($row->created_at)) }}</td>
                                @can('blog-status')
                                <td>
                                    @if($row->status==1)
                                        <a href="{{url('change-status-blog/')}}/{{$row->id}}/0">
                                            <div class="flex items-center justify-center text-theme-9">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                            </div>
                                        </a>
                                    @else
                                        <a href="{{url('change-status-blog/')}}/{{$row->id}}/1">
                                            <div class="flex items-center justify-center text-theme-6">
                                                <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                            </div>
                                        </a>
                                    @endif
                                </td>
                                @endcan
                                @if(Gate::check('blog-edit') || Gate::check('blog-delete') || Gate::check('blog-translate') || Gate::check('blog-send-notification') || Gate::check('blog-analytics'))
                                <td class="table-report__action w-40">
                                    <div class="flex justify-center items-center">
                                        @can('blog-translate')
                                        <a class="flex items-center mr-3 text-theme-3 font-size23" href="{{url('edit-blog-translation')}}/{{$layout}}/{{$theme}}/{{$row->id}}{{$query}}"  title="{{__('admin.translate')}}">
                                           <i data-feather="edit-3" class="w-4 h-4 mr-1"></i>
                                        </a>
                                        @endcan
                                        @can('blog-send-notification')
                                        <a class="flex items-center mr-3 text-theme-9 font-size23" href="{{url('send-blog-notification')}}/{{$row->id}}{{$query}}"  title="{{__('admin.send_notification')}}">
                                           <i data-feather="bell" class="w-4 h-4 mr-1"></i>
                                        </a>
                                        @endcan
                                        @can('blog-analytics')
                                         <a class="flex items-center mr-3 text-theme-9 font-size23" href="{{route('analytics',$row->id)}}"  title="{{__('admin.analytics ')}}">
                                             <i data-feather="activity" class="w-4 h-4 mr-1"></i>
                                          <!-- <img src="{{url('icon/analytics.svg')}}" class="w-4 h-4 mr-1"> -->
                                        </a>
                                        @endcan

                                        @if (setting('instagram_share') == 1)
                                        <a class="flex items-center mr-3" title="{{__('admin.instagram_image')}}" href="{{asset('upload/social-media-post/instagram/')}}/{{$row->scial_media_image}}" download>
                                           <i data-feather="download" class="w-4 h-4 mr-1"></i>
                                        </a>
                                        @endif


                                            @if(Auth::user()->type == 'subadmin')
{{--                                                @if(Auth::user()->id == $row->created_by)--}}
                                                <a class="flex items-center mr-3" href="{{url('edit-blog/')}}/{{$layout}}/{{$theme}}/{{$row->id}}"  title="{{__('admin.edit')}}">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-1"></i>
                                                </a>
{{--                                                @endif--}}
                                            @else
                                            <a class="flex items-center mr-3" href="{{url('edit-blog/')}}/{{$layout}}/{{$theme}}/{{$row->id}}" title="{{__('admin.edit')}}">
                                                <i data-feather="check-square" class="w-4 h-4 mr-1"></i>
                                                </a>
                                            @endif


                                        @can('blog-delete')
                                            <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}" title="{{__('admin.delete')}}">
                                                <i data-feather="trash-2" class="w-4 h-4 mr-1"></i>
                                            </a>
                                        @endcan
                                    </div>
                                    <div class="modal" id="delete-confirmation-modal-{{$row->id}}">
                                        <div class="modal__content">
                                            <div class="p-5 text-center">
                                                <i data-feather="x-circle" class="w-16 h-16 text-theme-6 mx-auto mt-3"></i>
                                                <div class="text-3xl mt-5">{{__('admin.sure_warning')}}</div>
                                                <div class="text-gray-600 mt-2">{{__('admin.delete_warning')}}</div>
                                            </div>
                                            <div class="px-5 pb-8 text-center">
                                                <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1">{{__('admin.cancel')}}</button>
                                                <a href="{{url('delete-blog')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="10">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
                {!! $blog->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center ml-5">
            <?php $entry_count = (isset($_GET['per_page']))?$_GET['per_page']:config('constant.paginate.num_per_page'); ?>
            <form>
                <select id="pagination" class="form-select">
                   <option value="5" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 5){ echo "selected"; }else{ if($entry_count==5){ echo "selected"; } } ?> >5</option>
                   <option value="10" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 10){ echo "selected"; }else{ if($entry_count==10){ echo "selected"; } } ?>>10</option>
                   <option value="25" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 25){ echo "selected"; }else{ if($entry_count==25){ echo "selected"; } } ?>>25</option>
                   <option value="50" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 50){ echo "selected"; }else{ if($entry_count==50){ echo "selected"; } } ?>>50</option>
                   <option value="100" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 100){ echo "selected"; }else{ if($entry_count==100){ echo "selected"; } } ?>>100</option>
                </select> 
            </form>
        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">
            <p class="text-right"><?php if ($blog->firstItem() != null) { ?> {{__('admin.showing')}} {{ $blog->firstItem() }} {{__('admin.to')}} {{ $blog->lastItem() }} {{__('admin.of')}} {{ $blog->total() }} {{__('admin.entries')}} <?php }?></p>
        </div>
    </div>
    <script>
        document.getElementById('pagination').onchange = function() { 
        window.location = "{!! $blog->url(1) !!}&per_page=" + this.value; 
       };  
    </script>
@endsection
