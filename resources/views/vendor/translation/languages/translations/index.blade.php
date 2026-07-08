<?php
   $layout = (isset($layout))?$layout:'side-menu';
   $theme  = (isset($theme))?$theme:'light';
?>
@extends('../layout/' . $layout)
@section('subhead')
<title>{{__('admin.localization')}} - {{setting('site_name')}}</title>
@endsection
@section('subcontent')
@include('../layout/components/top-bar')
<h2 class="intro-y text-lg font-medium mt-10">{{__('admin.content_manager')}} 
    @can('localization-manage-languages')
    <a href="javascript:;" data-toggle="modal" data-target="#add-keys" class="button text-white bg-theme-1 shadow-md mr-2 pull-right font-size15" value="Add Keys"> {{__('admin.add_keys')}} </a>
    <a href="{{url('languages')}}?layout={{$layout}}&theme={{$theme}}" class="button text-white bg-theme-1 shadow-md mr-2 pull-right font-size15" value="Reset"> {{__('admin.manage_languages')}} </a>
    @endcan
</h2>
<div class="grid grid-cols-12 gap-8 mt-5">
   <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
      <form method="GET">
         <input type="hidden" name="layout" value="{{$layout}}">
         <input type="hidden" name="theme" value="{{$theme}}">

         <input type="text" class="input mt-5 w-56 box pr-10 placeholder-theme-13" name="key"  @if(isset($_GET['key'])) value="{{$_GET['key']}}" @endif placeholder="{{__('admin.search_keyword')}}" >
         <select class="input w-56 mt-5 box pr-10 placeholder-theme-13 ml-2" name="language_id">
            <option value="">{{__('admin.select_language')}}</option>
            @foreach($languages as $row)
            <option value="{{$row->id}}" <?php if(isset($_GET['language_id'])){ if($_GET['language_id']==$row->id){ echo "selected"; } }?>>{{$row->name}}</option>
            @endforeach
         </select>
         <select class="input w-56 mt-5 box pr-10 placeholder-theme-13 ml-2" name="group">
            <option value="">{{__('admin.select_group')}}</option>
            @foreach($groups as $row)
                <option value="{{$row->group}}" <?php if(isset($_GET['group'])){ if($_GET['group']==$row->group){ echo "selected"; } }?>>
                  @if($row->group == 'admin')
                    Admin
                  @endif
                  @if($row->group == 'api')
                    App
                  @endif
                  @if($row->group == 'frontend')
                   Website
                  @endif
                  @if($row->group == 'message_alerts')
                   Message Alerts
                  @endif
                </option>
            @endforeach
         </select>
         <input  name="search" type="submit" class="button mt-5 text-white bg-theme-1 shadow-md mr-2 ml-3" value="{{__('admin.search')}}">
         <input type="button" class="button mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white"  onclick="resetFilter()" value="{{__('admin.reset')}}">
         <!-- <button class="button mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button> -->

      </form>
   </div>
<div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
   <table class="table table-report -mt-2">
      <thead>
         <tr>
            <th class="whitespace-no-wrap">S.NO</th>
            <th class="uppercase font-thin">{{__('admin.keywords')}}</th>
            <th class="uppercase font-thin">{{__('admin.value')}}</th>
            <th class="uppercase font-thin">{{__('admin.group')}}</th>
            @can('localization-edit')
            <th class="uppercase font-thin">{{__('admin.edit')}}</th>
            @endcan
          {{--  <th class="uppercase font-thin">{{__('admin.delete')}}</th> --}}
         </tr>
      </thead>
      <tbody>
         @if(count($translations))
         <?php
            $page = (isset($_GET['page']))?$_GET['page']:1;
            if($page>1){
                $i = $translations->perPage() * ($translations->currentPage() - 1) + 1;
            }
            else{
                $i=1;
            }
         ?>
         @foreach($translations as $row)
         <tr>
            <td>{{$i}}</td>
            <td>{{ $row->keyword }} </td>
            <td>{{ $row->value }}</td>
            <td>
              @if($row->group == 'admin')
                Admin
              @endif
              @if($row->group == 'api')
                App
              @endif
              @if($row->group == 'frontend')
               Website
              @endif
              @if($row->group == 'message_alerts')
               Message Alerts
              @endif
              <!-- {{ $row->group }} -->
            </td>
            @can('localization-edit')
            <td>
               <a class="flex items-center mr-3" title="{{__('admin.edit')}}" href="javascript:;" onclick="getTranslationValues('{{$row->id}}')" data-toggle="modal" data-target="#header-footer-modal-preview">
               <i data-feather="check-square" class="w-4 h-4 mr-1"></i> 
               </a>
            </td>
            @endcan
            {{-- <td>
               <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$row->id}}"  title="{{__('admin.delete')}}">
               <i data-feather="trash-2" class="w-4 h-4 mr-1"></i>
               </a>
               <div class="modal" id="delete-confirmation-modal-{{$row->id}}">
                  <div class="modal__content">
                     <div class="p-5 text-center">
                        <i data-feather="x-circle" class="w-16 h-16 text-theme-6 mx-auto mt-3"></i>
                        <div class="text-3xl mt-5">{{__('admin.sure_warning')}}</div>
                        <div class="text-gray-600 mt-2">{{__('admin.delete_warning')}}</div>
                     </div>
                     <div class="px-5 pb-8 text-center">
                        <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1">{{__('admin.cancel')}}</button>
                        <a href="{{ route('languages.translations.delete' ,$row->id) }}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                     </div>
                  </div>
               </div>
            </td> --}}
         </tr>
         <?php $i++; ?>
         @endforeach
         @else
         <tr class="intro-x text-center text-danger">
            <td class="w-40" colspan="7">
               {{__('admin.no_record_found')}}
            </td>
         </tr>
         @endif
      </tbody>
   </table>
   <div class="modal" id="header-footer-modal-preview">
      <div class="modal__content">
         <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" id="content-key">{{__('admin.edit_content')}}</h2>
         </div>
         <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide">
         </div>
         <form id="addcategoryform" action="{{url('languages/translations/update')}}" method="post">
            @csrf
            <div class="px-5 py-3 border-t border-gray-200 dark:border-dark-5">
            <div id="append" class="pb-4">
            </div>
            </div>
            <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
               <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
               <input type="submit" class="button w-20 bg-theme-1 text-white" value="{{__('admin.save')}}">
            </div>
         </form>
      </div>
   </div>
</div>
<div class="intro-y col-span-12 md:col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
   <ul class="pagination">
      {!! $translations->appends(request()->except('page'))->onEachSide(-1)->render() !!}
   </ul>
</div>
<div class="intro-y col-span-12 md:col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center ml-5" style="color:black;">
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
<div class="intro-y col-span-12 md:col-span-3 sm:flex-row sm:flex-no-wrap items-right">

    <p class="text-right"><?php if ($translations->firstItem() != null) { ?> {{__('admin.showing')}} {{ $translations->firstItem() }} {{__('admin.to')}} {{ $translations->lastItem() }} {{__('admin.of')}} {{ $translations->total() }} {{__('admin.entries')}} <?php }?></p>

</div>
</div>
<div class="modal" id="add-keys">
      <div class="modal__content">
         <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" id="content-key">{{__('admin.edit_content')}}</h2>
         </div>
         <div class="flex items-center px-5 py-5 sm:py-3 ajax-msg hide">
         </div>
         <form id="addkeyform" action="{{ route('languages.translations.store') }}" method="post">
            @csrf
            <div class="px-5 py-3 border-t border-gray-200 dark:border-dark-5">
            <div class="pb-4">
               <div class="grid grid-cols-12 gap-4 row-gap-3">
                  <div class="col-span-12 sm:col-span-12">
                     <label>{{__('admin.select_group')}}</label>
                     <select class="input w-full border mt-2 flex-1 focus" name="group">
                        <option value="">Select Group</option>
                        <option value="admin">Admin</option>
                        <option value="api">API</option>
                        <option value="frontend">Frontend</option>
                        <option value="message_alerts">Message Alerts</option>
                     </select>
                  </div>
               </div>
               <div class="grid grid-cols-12 mt-5 gap-4 row-gap-3">
                  <div class="col-span-12 sm:col-span-12">
                     <label>{{__('admin.keyword')}}</label>
                     <input type="text" class="input w-full border mt-2 flex-1 focus" name="keyword" placeholder="{{__('admin.keyword_placeholder')}}" >
                  </div>
               </div>
               <div class="grid grid-cols-12 mt-5 gap-4 row-gap-3">
                  <div class="col-span-12 sm:col-span-12">
                     <label>{{__('admin.value')}}</label>
                     <input type="text" class="input w-full border mt-2 flex-1 focus" name="value" placeholder="{{__('admin.value_placeholder')}}" >
                  </div>
               </div>
            </div>
            </div>
            <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
               <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
               <input type="submit" class="button w-20 bg-theme-1 text-white" value="{{__('admin.save')}}">
            </div>
         </form>
         
      </div>
   </div>
<script>
        document.getElementById('pagination').onchange = function() { 
        window.location = "{!! $translations->url(1) !!}&per_page=" + this.value; 
       };  
    </script>
@endsection