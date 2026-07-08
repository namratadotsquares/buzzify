@extends('../layout/' . $layout)

@section('subhead')
    <title>Search Feed Items List - {{setting('site_name')}}</title>
@endsection

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" /> -->
<style type="text/css">
    .table-report img {
        box-shadow: 0px 0px 0px 2px #fff, 1px 1px 5px rgb(0 0 0 / 32%);
        width: 50px;
    }
</style>
@section('subcontent')
    @include('../layout/components/top-bar')
    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.search_feed_items')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            <form method="GET">
                @if(isset($_GET['source']))
                <select class="input w-56 box pr-10 placeholder-theme-13" name="category_id" onchange="getSources(this.value,'{{$_GET['source']}}')">
                @else
                <select class="input w-56 box pr-10 placeholder-theme-13" name="category_id" onchange="getSources(this.value,0);">
                @endif
                    <option value="">{{__('admin.all_categories')}}</option>
                    @foreach($category as $cat)
                        <option value="{{$cat->id}}"<?php if(isset($_GET['category_id'])){ if($_GET['category_id']==$cat->id){ echo "selected"; } }?>>{{$cat->name}}</option>
                    @endforeach
                </select>
                <select class="input w-56 box pr-10 placeholder-theme-13 ml-2" name="source" id="source">
                        <option value="">{{__('admin.all_source')}}</option>
                        @foreach($sources as $src)
                           
                            <option value="{{$src->id}}" <?php if(isset($_GET['source'])){ if($_GET['source']==$src->id){ echo "selected"; } }?>>{{$src->rss_name}}</option>
                            
                        @endforeach
                    </select>
                <!-- <select class="input w-56 box pr-10 placeholder-theme-13" name="source" id="source">
                        <option value="">{{__('admin.all_source')}}</option>
                        @foreach($sources as $src)
                            @if($src->categoryData)
                            <option value="{{$src->id}}" <?php if(isset($_GET['source'])){ if($_GET['source']==$src->id){ echo "selected"; } }?>>{{$src->rss_name}}</option>
                            @endif
                        @endforeach
                    </select> -->
                <!-- @if(isset($_GET['source'])){
                    
                @else -->
                    
                <!-- @endif -->
                <button type="submit" class="button text-white bg-theme-1 shadow-md mr-2 ml-3">{{__('admin.search_feeds')}}</button>

                <a class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" href="{{url('feed-item/side-menu/light')}}">{{__('admin.reset')}}</a>
            </form>
            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
            </div>
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.image')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.title_desc')}}</th>
                        @can('feed-item-save-post')
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @if(count($feed))
                        <?php $i=1; ?>
                        @foreach ($feed as $row)
                            @if($row->is_saved==0)
                            <tr class="intro-x row1" data-id="{{ $row->id }}">
                                <td class="w-40">
                                    @if(isset($row->url) && $row->url!=null && $row->url!='')
                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{$row->url}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    @else
                                        <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  src="{{url('upload/author/default.png')}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';">
                                    @endif
                                </td> 
                                <td>
                                    <a target="_blank" href="{{$row->link}}" class="font-medium whitespace-no-wrap"><?php echo substr($row->title, 0,90);?></a> 
                                    <div class="text-gray-600 text-xs">
                                        {{-- <?php echo substr($row->description, 0,150)."........";?> --}}
                                         <?php echo substr(strip_tags($row->description),0,150)."........"; ?>   
                                    </div>
                                </td>
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                       @can('feed-item-save-post')
                                        @if(isset($row->post_id))
                                            <a href="{{url('save-post')}}/{{$row->post_id}}/{{$row->category_id}}?source={{$row->source}}" class="font-medium whitespace-no-wrap button text-white bg-theme-1">{{__('admin.save_as_post')}}</a> 
                                        @else
                                            <a href="{{url('save-post')}}/0/{{$row->category_id}}?source={{$row->source}}&link={{$row->link}}" class="font-medium whitespace-no-wrap button text-white bg-theme-1">{{__('admin.save_as_post')}}</a>
                                        @endif
                                       @endcan
                                        <div class="text-gray-600 text-xs whitespace-no-wrap"></div>
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
                                                <a href="{{url('delete-rss-feed-src')}}/{{$row->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="4">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            
        </div>
        <div class="intro-y col-span-3 flex flex-wrap sm:flex-row sm:flex-no-wrap items-right">
        </div>
    </div>
    <div class="modal" id="header-footer-modal-preview">
        <div class="modal__content">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                <h2 class="font-medium text-base mr-auto">{{__('admin.add_rss_feed')}}</h2>
            </div>

            <form id="addrssfeedform">
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.select_category')}}</label>
                        <select class="input w-full border mt-2 flex-1" name="category_id">
                            <option value="">{{__('admin.select_category')}}</option>
                            @foreach($category as $detail)
                                <option value="{{$detail->id}}">{{$detail->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.name')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1 height-40" name="rss_name" placeholder="{{__('admin.name_placeholder')}}">
                    </div>
                </div>
                <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                    <div class="col-span-12 sm:col-span-12">
                        <label>{{__('admin.url')}}</label>
                        <input type="text" class="input w-full border mt-2 flex-1 height-40" name="rss_url" placeholder="{{__('admin.url_placeholder')}}">
                    </div>
                </div>               

                <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                    <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                    <input type="button" class="button w-20 bg-theme-1 text-white" value="{{__('admin.create')}}" onclick="add_rss_feed_src(event,'addrssfeedform')">
                </div>
            </form>            
        </div>
    </div>
    @if(isset($_GET['source']) && isset($_GET['category_id'])) 
        <!-- It is required-inline JS to put here because following js are making dynamic from the admin setting -->
        <script>
            setTimeout(function(){
                (function($){
                    "use strict";    
                    getSources("<?php echo $_GET['category_id']; ?>", "<?php echo $_GET['source']; ?>"); 
                })(jQuery);
            }, 3000);
        </script>
    @endif
@endsection