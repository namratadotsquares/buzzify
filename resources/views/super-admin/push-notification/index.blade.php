@extends('../layout/' . $layout)

@section('subhead')
    <title>{{__('admin.send_notification')}} - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')   
<link href="{{ asset('dist/css/tagsinput.css') }}" rel="stylesheet" type="text/css">
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.send_notification')}}</h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <form id="addUpdateBlog" method="POST" action="{{url('/send-notification')}}">
                <div class="intro-y box p-5">
                    @csrf
                        <div class="mt-3">
                            <div class="grid grid-cols-12 gap-4 row-gap-3">
                                <div class="col-span-12 sm:col-span-6">
                                    <label>{{__('admin.send_to')}}</label>
                                    <div class="mt-2">
                                        <select data-placeholder="{{__('admin.all_users')}}" name="send_to" id="send_to" class="tail-select w-full" onchange="triggerEmailsField(this.value)">
                                            <option value="all" >{{__('admin.all_users')}}</option>
                                            <option value="specific" >{{__('admin.specific_users')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-6">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 emails hide sm:col-span-8">
                            <label>{{__('admin.email')}} <font class="text-danger font-size10">({{__('admin.comma_saperate')}})</font></label>
                            <select data-placeholder="Select Email" name="email[]" class="tail-select w-full select2" multiple="">
                                @foreach(DB::table('users')->where('active',1)->where('type','user')->get() as $item)
                                    <option value="{{$item->email}}">{{$item->email}}</option>
                                @endforeach
                            </select>
                            <!-- <input type="text" class="input w-full border mt-2" name="email"  data-role="tagsinput" placeholder="{{__('admin.email_placeholder')}}" style="display:none;"> -->
                        </div>

                        <div class="mt-3 emails">
                            <label>{{__('admin.title')}} </label>
                            <input type="text" class="input w-full border mt-2 typeahead" id="postal"  name="title" placeholder="{{__('admin.title_placeholder')}}">
                        </div>
                        <div class="mt-3">
                            <label>{{__('admin.description')}}</label>
                            <div class="mt-2">
                                <div class="preview">
                                    <textarea class="input w-full border mt-2" row="50" name="description"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="text-right mt-5">
                            <button type="submit" id="createBtn" class="button w-24 bg-theme-1 text-white">{{__('admin.send')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>  
@endsection