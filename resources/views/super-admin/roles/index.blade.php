@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.roles_list')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <h2 class="intro-y text-lg font-medium mt-10">{{__('admin.roles_list')}}</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-no-wrap items-center mt-2">

            @can('role-create')
                <a href="{{ route('roles.create') }}" class="button text-white bg-theme-1 shadow-md mr-2">Add New Role</a>
            @endcan
           
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible table-manage">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-no-wrap">{{__('admin.id')}}</th>
                        <th class="whitespace-no-wrap">{{__('admin.name')}}</th>
                        <th class="text-center whitespace-no-wrap">{{__('admin.action')}}</th>
                    </tr>
                </thead>
                
                <tbody>
                    @if(count($roles))
                        <?php $i=1; ?>
                        @foreach ($roles as $key => $role)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $role->name }}</td>
                                <td class="table-report__action w-40">
                                    <div class="flex justify-center items-center">

                                        @can('role-edit')
                                            <a class="btn btn-primary" href="{{ route('roles.edit', $role->id) }}"><i data-feather="check-square" class="w-4 h-4 mr-1"></i> </a>
                                        @endcan

                                        @can('role-delete')
                                            <!-- <a class="btn btn-primary" href="{{ route('roles.destroy', $role->id) }}"><i data-feather="trash-2" class="w-4 h-4 mr-1"></i></a> -->
                                            <a class="flex items-center text-theme-6" href="javascript:;" data-toggle="modal" data-target="#delete-confirmation-modal-{{$role->id}}" title="{{__('admin.delete')}}">
                                                <i data-feather="trash-2" class="w-4 h-4 mr-1"></i>
                                            </a>
                                        @endcan
                                    </div>
                                    <div class="modal" id="delete-confirmation-modal-{{$role->id}}">
                                        <div class="modal__content">
                                            <div class="p-5 text-center">
                                                <i data-feather="x-circle" class="w-16 h-16 text-theme-6 mx-auto mt-3"></i>
                                                <div class="text-3xl mt-5">{{__('admin.sure_warning')}}</div>
                                                <div class="text-gray-600 mt-2">{{__('admin.delete_warning')}}</div>
                                            </div>
                                            <div class="px-5 pb-8 text-center">
                                                <button type="button" data-dismiss="modal" class="button w-24 border text-gray-700 mr-1">{{__('admin.cancel')}}</button>
                                                <a href="{{url('delete-role')}}/{{$role->id}}" class="button w-24 bg-theme-6 text-white">{{__('admin.delete')}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
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
         <div class="intro-y col-span-8 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            <ul class="pagination">
                {!! $roles->appends(request()->except('page'))->render() !!}
            </ul>
        </div>
        <div class="intro-y col-span-1 flex flex-wrap sm:flex-row sm:flex-no-wrap items-center">
            
        </div>
        <div class="intro-y col-span-3 sm:flex-row sm:flex-no-wrap items-right">

            <p class="text-right"><?php if ($roles->firstItem() != null) { ?> {{__('admin.showing')}} {{ $roles->firstItem() }} {{__('admin.to')}} {{ $roles->lastItem() }} {{__('admin.of')}} {{ $roles->total() }} {{__('admin.entries')}} <?php }?></p>

        </div>
    </div> 
@endsection