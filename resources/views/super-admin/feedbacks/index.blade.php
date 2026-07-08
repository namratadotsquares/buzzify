@extends('../layout/' . $layout)

@section('subhead')
    <title>Search Feedback List - {{setting('site_name')}}</title>
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
            <form id="bulkDeleteFormFeedbacks" method="POST" action="{{ url('/delete-multiple-feedback') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <button type="button" id="deleteSelectedFeedbacks" class="button w-48 bg-theme-6 text-white mr-2">{{ __('admin.delete_selected') ?? 'Delete Selected' }}</button>
            </form>
            <!--@can('epaper-create')-->
            <!--<a href="javascript:;" data-toggle="modal" data-target="#header-footer-modal-preview" class="button text-white bg-theme-1 shadow-md mr-2">{{__('admin.add_w_product')}}</a>-->
            <!--@endcan-->
            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['name'])) value="{{$_GET['name']}}" @endif name="name" placeholder="Search By name">
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
                        <th class="whitespace-no-wrap"><input type="checkbox" id="select_all_feedbacks" /></th>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.email')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.phone')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.feedback')}}</th>
                        <th class="whitespace-no-wrap">{{__('frontend.current_date')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($feedback))
                        <?php $page = (isset($_GET['page']))?$_GET['page']:1;
                        if($page>1){
                            $i = $feedback->perPage() * ($feedback->currentPage() - 1) + 1;
                        }
                        else{
                            $i=1;
                        }
                         ?>
                        @foreach ($feedback as $row)
                            @if($row->is_saved==0)
                                <tr class="intro-x row1" data-id="{{ $row->id }}">
                                    <td class="w-10">
                                        <input type="checkbox" class="row_checkbox_feedbacks" name="ids[]" form="bulkDeleteFormFeedbacks" value="{{ $row->id }}" />
                                    </td>
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
                            <td class="w-40" colspan="7">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        {{$feedback->links()}}
        
        <script>
            // select / deselect all for feedbacks
            var selectAll = document.getElementById('select_all_feedbacks');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    var rows = document.querySelectorAll('.row_checkbox_feedbacks');
                    for (var i = 0; i < rows.length; i++) {
                        rows[i].checked = this.checked;
                    }
                });
            }

            var deleteBtn = document.getElementById('deleteSelectedFeedbacks');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    var rows = document.querySelectorAll('.row_checkbox_feedbacks:checked');
                    if (rows.length === 0) {
                        alert('{{ __('admin.select_at_least_one_record') ?? 'Please select at least one record.' }}');
                        return;
                    }
                    if (confirm('{{ __('admin.sure_warning') ?? 'Are you sure?' }}')) {
                        document.getElementById('bulkDeleteFormFeedbacks').submit();
                    }
                });
            }
        </script>
    </div>
   
@endsection