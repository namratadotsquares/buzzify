
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.languages_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')


    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.languages_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">

                <a href="{{ url('languages/language/create') }}?layout={{$layout}}&theme={{$theme}}" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_language')}}</a>

                <a href="{{ url('languages/translations') }}?layout={{$layout}}&theme={{$theme}}" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.manage_translations')}}</a>

            <div class="hidden md:block mx-auto text-gray-600"> </div>            
            
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">S.NO</th>
                        <th class="whitespace-no-wrap">{{__('admin.language')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.iso_code')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.status')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.action')}}</th>
                        <!-- <th class="whitespace-no-wrap">{{__('admin.edit')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.delete')}}</th> -->
                    </tr>
                </thead>
            
                <tbody>
                    @if(count($languages))
                    <?php
                    $page = (isset($_GET['page']))?$_GET['page']:1;
                    if($page>1){
                        $i = $languages->perPage() * ($languages->currentPage() - 1) + 1;
                    }
                    else{
                        $i=1;
                    }
                    ?>
                    @foreach ($languages as $language => $name)
                        <tr class="intro-x" >
                            <td>{{$i}}</td>
                            <td>{{ $name->name }}</td>
                            <td>
                                <!-- <a href="{{ route('languages.translations.index', $language) }}">
                                    {{ $language }}
                                </a> -->
                                <a href="javascript:;">
                                    {{ $name->language }}
                                </a>
                            </td>
                            <td>
                                @if($name->status==1)
                                    <a href="{{url('languages/language/status/')}}/{{$name->id}}/0">
                                        <div class="flex items-center text-theme-9">
                                            <i data-feather="check-square" class="w-4 h-4 mr-2"></i> {{__('admin.active')}}
                                        </div>
                                    </a>
                                @else                                    
                                    <a href="{{url('languages/language/status/')}}/{{$name->id}}/1">
                                        <div class="flex items-center text-theme-6">
                                            <i data-feather="check-square" class="w-4 h-4 mr-2"></i>{{__('admin.inactive')}}
                                        </div>
                                    </a>
                                @endif  
                            </td>
                            <td class="table-report__action w-40">
                                <div class="flex items-center">
                                    <a class="flex items-center mr-3" href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview_edit_{{$name->name}}"  title="{{__('admin.edit')}}">
                                        <i data-feather="check-square" class="w-4 h-4 mr-1"></i> 
                                    </a>
                                   
                                    <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$name->name}}"  title="{{__('admin.delete')}}">
                                        <i data-feather="trash-2" class="w-4 h-4 mr-1"></i>
                                    </a>
                                </div>
                                <div class="modal" id="delete-confirmation-modal-{{$name->name}}">
                                    <div class="modal__content">
                                        <div class="p-5 text-center">
                                            <i data-feather="x-circle" class="w-16 h-16 text-theme-6 mx-auto mt-3"></i>
                                            <div class="text-3xl mt-5">{{__('admin.sure_warning')}}</div>
                                            <div class="text-gray-600 mt-2">{{__('admin.delete_warning')}}</div>
                                        </div>
                                        <div class="px-5 pb-8 text-center">
                                            <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1">{{__('admin.cancel')}}</button>
                                            <a href="{{ route('languages.delete' ,$name->name) }}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal" id="header-footer-modal-preview_edit_{{$name->name}}">
                                    <div class="modal__content">
                                        <div class="flex items-center px-5 py-5 sm:py-3 border-b border-gray-200 dark:border-dark-5">
                                            <h2 class="font-medium text-base mr-auto">{{__('admin.edit')}}</h2>
                                        </div>
                                        <form method="post" action="{{route('languages.update', $name->name)}}">
                                            @csrf
                                            <input type="hidden" name="" value="{{$name->name}}">
                                            <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                <div class="col-span-12 sm:col-span-12">
                                                    <label>{{__('admin.language_name')}}</label>
                                                    <input type="text" class="input w-full border mt-2 flex-1" name="name" placeholder="{{__('admin.language_name')}}" value="{{$name->name}}">
                                                </div>
                                            </div>
                                            <div class="p-5 grid grid-cols-12 gap-4 row-gap-3">
                                                <div class="col-span-12 sm:col-span-12">
                                                    <label>{{__('admin.iso_code')}}</label>
                                                    <input type="text" class="input w-full border mt-2 flex-1" name="language" placeholder="{{__('admin.iso_code')}}" value="{{$name->language}}">
                                                </div>
                                            </div>
                                            <div class="px-5 py-3 text-right border-t border-gray-200 dark:border-dark-5">
                                                <button type="button" data-dismiss="modal" class="button w-20 border text-gray-700 dark:border-dark-5 dark:text-gray-300 mr-1">{{__('admin.cancel')}}</button>
                                                <input type="submit" class="button w-20 bg-theme-1 text-white" value="{{__('admin.save')}}">
                                            </div>
                                        </form>
                                    </div>
                                </div> 
                            </td>
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
        </div>
    </div>
@endsection