@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.add_role')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
<style type="text/css">
    .table {
      --bs-table-bg: transparent;
      --bs-table-accent-bg: transparent;
      /* --bs-table-striped-color: #6e6b7b; */
      --bs-table-striped-bg: #fafafc;
      /* --bs-table-active-color: #6e6b7b; */
      --bs-table-active-bg:
      rgba(34, 41, 47, 0.1);
      /* --bs-table-hover-color: #6e6b7b; */
      --bs-table-hover-bg: #f6f6f9;
      width: 100%;
      /* margin-bottom: 1rem; */
      color: #6e6b7b;
      vertical-align: middle;
      border-color: #ebe9f1;
      }

    .table > :not(caption) > * > * {
      padding: 0.72rem 2rem;
      background-color: var(--bs-table-bg);
      border-bottom-width: 1px;
      box-shadow: inset 0 0 0 9999px var(--bs-table-accent-bg); }

    .table-bordered > :not(caption) > * {
      border-width: 1px 0; }

    .table-bordered > :not(caption) > * > * {
      border-width: 0 1px;
      }


    .table-striped > tbody > tr:nth-of-type(odd) {
      --bs-table-accent-bg: var(--bs-table-striped-bg);
      color: var(--bs-table-striped-color); }

    .table-hover > tbody > tr:hover {
      --bs-table-accent-bg: var(--bs-table-hover-bg);
      color: var(--bs-table-hover-color); }

    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      /* min-height: 500px; */
      }

    .table:not(.table-dark):not(.table-light) thead:not(.table-dark) th,
    .table:not(.table-dark):not(.table-light) tfoot:not(.table-dark) th {
      background-color: #f3f2f7; }

    .table-hover tbody tr {
      cursor: pointer; }

</style>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.add_role')}}</h2>
    </div>
    {!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="intro-y col-span-12 lg:col-span-12">            
                <div class="intro-y box p-5">
                    <div class="mt-3">
                        <label>{{__('admin.title')}} </label>
                        {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'input w-full border mt-2')) !!}
                    </div>
                    <div class="mt-3">
                        <label class="col-sm-2 control-" style="font-size: 17px;font-weight: bold; margin-bottom: 15px;" for="form-field-1">
                        {{__('admin.permissions')}}
                        </label>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Module Name</th>
                                        <th colspan="10">Permissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permission as $value)
                                    <tr>
                                        <td>
                                            <b style="text-transform: capitalize;">{{$value->group_name}}</b>
                                        </td>
                                        @foreach($value->permission as $row)
                                            <td>
                                                <label>
                                                    <input type="checkbox" class="name form-check-input cardcheckbox" name="permission[]" value="{{$row->id}}" {{$row->is_default ? 'checked' : ''}}> {{ $row->display_name }}
                                                </label>
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-right mt-8">
                        <a href="{{ route('roles.index') }}" class="button w-24 border dark:border-dark-5 text-gray-700 dark:text-gray-300 mr-1">{{__('admin.back')}}</a>
                        <button type="submit" id="createBtn" class="button w-24 bg-theme-1 text-white" >{{__('admin.save')}}</button>
                    </div>

                </div>            
            </div>
        </div> 
    {!! Form::close() !!}


@endsection