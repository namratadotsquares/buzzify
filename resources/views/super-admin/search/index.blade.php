@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.search_log_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.search_log_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">
            <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <div class="w-56 relative text-gray-700 dark:text-gray-300">
                    <form method="GET">
                        <input type="text" class="input w-56 box pr-10 placeholder-theme-13" @if(isset($_GET['search_keyword'])) value="{{$_GET['search_keyword']}}" @endif name="search_keyword" placeholder="{{__('admin.search_by_keyword')}}">
                        <a href="javascript:;" onclick="searchClick();"><i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i></a> 
                        <input type="submit" id="search" class="hide" name="search">
                    </form>
                </div>
            </div>
            <button class="button ml-2 mt-2 mr-1 mb-2 border text-gray-700 dark:bg-dark-5 dark:text-gray-300 bg_white" onclick="resetFilter()">{{__('admin.reset')}}</button>
            <button type="button" id="delete-selected" class="button ml-2 mt-2 mr-1 mb-2 border text-white bg-red-600" onclick="deleteSelected()">{{__('admin.delete_selected') ?? 'Delete Selected'}}</button>
        </div>
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center">
        <div class="hidden md:block mx-auto text-gray-600">  </div>
            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <p><strong>Most Searched Keyword</strong> : {{$keyword->search_keyword}}</p>
            </div>
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap"><input type="checkbox" id="select_all"></th>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.search_keyword')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.search_count')}}</th>
                        <th class="whitespace-no-wrap">Last Searched</th>
                        <th class="whitespace-no-wrap">{{__('admin.actions') ?? 'Actions'}}</th>
                    </tr>
                </thead>
               
                <tbody>
                    @if(count($search))
                        <?php
                        $page = (isset($_GET['page']))?$_GET['page']:1;
                        if($page>1){
                            $i = $search->perPage() * ($search->currentPage() - 1) + 1;
                        }
                        else{
                            $i=1;
                        }
                        ?>
                        @foreach ($search as $row)
                            <tr class="intro-x">
                                <td class="w-10 text-center">
                                    <input type="checkbox" class="row_checkbox" value="{{ $row->id }}">
                                </td>
                                <td class="w-40">{{$i}}</td>
                                <td>{{ $row->search_keyword }}</td>
                                <td>{{ $row->search_count }}</td>
                                <td>@if($row->created_at!=null){{ date(setting('date_format'),strtotime($row->created_at)) }}@else -- @endif</td>
                                <td>
                                    <a href="{{ url('/delete-search-log/'.$row->id) }}" onclick="return confirm('Are you sure to delete this record?');" class="text-red-600">{{__('admin.delete') ?? 'Delete'}}</a>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr class="intro-x text-center text-danger">
                            <td class="w-40" colspan="6">
                                {{__('admin.no_record_found')}}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
                {!! $search->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center" style="color:black">
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

             <p class="text-right"><?php if ($search->firstItem() != null) { ?> {{__('admin.showing')}} {{ $search->firstItem() }} {{__('admin.to')}} {{ $search->lastItem() }} {{__('admin.of')}} {{ $search->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>
    </div>
    <script>
        document.getElementById('pagination').onchange = function() { 
        window.location = "{!! $search->url(1) !!}&per_page=" + this.value; 
       };  
    </script>
    <script>
        // select / deselect all
        document.getElementById('select_all').addEventListener('change', function(e){
            var checked = this.checked;
            document.querySelectorAll('.row_checkbox').forEach(function(cb){ cb.checked = checked; });
        });

        function deleteSelected(){
            var ids = [];
            document.querySelectorAll('.row_checkbox:checked').forEach(function(cb){ ids.push(cb.value); });
            if(ids.length == 0){
                alert('{{ __('admin.please_select_at_least_one') ?? 'Please select at least one item' }}');
                return;
            }
            if(!confirm('{{ __('admin.are_you_sure_to_delete_selected') ?? 'Are you sure to delete selected items?' }}')){
                return;
            }

            fetch('{{ url('/delete-multiple-search-log') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: ids })
            }).then(function(res){ return res.json(); }).then(function(data){
                if(data.status){
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __('message_alerts.there_is_an_error') }}');
                }
            }).catch(function(){ alert('{{ __('message_alerts.there_is_an_error') }}'); });
        }
    </script>
@endsection