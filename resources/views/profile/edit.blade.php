@extends('../layout/' . $layout)

@section('subhead')
    <title>Admin Profile - {{setting('site_name')}}</title>
@endsection

@section('subcontent')
    @include('../layout/components/top-bar')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{__('admin.admin_profile')}}</h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">
                <form method="post" action="{{url('/editProfile')}}">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{$user->id}}">
                    <div>
                        <label>{{__('admin.name')}}</label>
                        <input type="text" class="input w-full border mt-2" name="name" value="{{$user->name}}" placeholder="{{__('admin.name_placeholder')}}">
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.email')}}</label>
                        <input type="email" class="input w-full border mt-2" name="email" value="{{$user->email}}" placeholder="{{__('admin.email_placeholder')}}">
                    </div>
                    <div class="mt-3">
                        <label>{{__('admin.password')}}</label>
                        <input type="password" class="input w-full border mt-2" name="password" placeholder="{{__('admin.password_placeholder')}}">
                    </div>
                    <?php 
                        if(file_exists(public_path()."/upload/user/".$user->photo) && $user->photo!='') { 
                            $url = url('upload/user').'/'.$user->photo;
                        }else{
                            $url = url('upload/no-image.png');
                        }
                    ?>
                    <input type="hidden" name="photo" id="photo" value="">
                    <div class="mt-3">
                        <label>{{__('admin.profile_image')}}</label>
                        <div class="col-span-12 sm:col-span-12">
                            <input type="button" class="button w-30 bg-theme-1 text-white" value="{{__('admin.profile_image')}}" onclick="triggerFileInput('profileuploadBtn')">
                            <input class="profileuploadBtn hide"  type="file" name="photo_img" onchange="uploadProfileImage(this,'profile_image','add',0);" accept="image/jpg, image/jpeg, image/png"/>
                        </div>
                        <div class="col-span-12 sm:col-span-12 mt-3">
                            <img onerror="this.onerror=null;this.src='<?php echo url("upload/no-image.png") ?>';"  id="profile_image" src="{{$url}}" class="width-20" >
                        </div>
                    </div>
                    <div class="text-right mt-5">
                        <button type="submit" class="button w-24 bg-theme-1 text-white" id="createBtn">{{__('admin.save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>    
@endsection