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
    <div class="grid grid-cols-12 gap-6 mt-5">
        
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            <!--@can('epaper-create')-->
            <!--<a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_w_product')}}</a>-->
            <!--@endcan-->
            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['name'])) value="{{$_GET['name']}}" @endif name="name" placeholder="Search Here...">
                        <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a>
                        <input type="submit" id="search" class="hide" name="search">
                    </form>
                </div>
            </div>

            <button class="button ml-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>
        </div>
       
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.email')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.phone')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.feedback')}}</th>
                        <th class="text-center whitespace-no-wrap">{{__('frontend.current_date')}}</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @if(count($feed))
                        <?php $i=1; ?>
                        @foreach ($feed as $row)
                            @if($row->is_saved==0)
                            <tr class="intro-x row1" data-id="{{ $row->id }}">
                                <td class="">
                                    {{$i}}
                                </td>
                                <td class="w-40">
                                    {{ $row->name }}
                                </td> 
                                <td class="w-40">
                                    {{ $row->email }}
                                </td> 
                                <td class="w-40">
                                    {{ $row->phone }}
                                </td> 
                                <td>
                                    {{ $row->feed_back }}
                                </td>
                                <td>
                                    {{ $row->created_at }}
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
   
@endsection