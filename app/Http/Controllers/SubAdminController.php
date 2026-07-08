<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
Use DB;
use Illuminate\Support\Facades\Gate;

class SubAdminController extends Controller
{
    function __construct(){
         $this->middleware('permission:sub-admin-list|sub-admin-status|sub-admin-delete', ['only' => ['index','changeSubAdminStatus','deleteSubAdmin']]);
         $this->middleware('permission:sub-admin-status', ['only' => ['changeSubAdminStatus']]);
         $this->middleware('permission:sub-admin-delete', ['only' => ['deleteSubAdmin']]);
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $category = User::getAllSubadmin($request->all());
        $roles = Role::whereNotIn('name',['admin','user'])->orderBy('id','DESC')->get();
        return view('sub_admin.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'category'=>$category,
            'roles'=>$roles,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans("admin.dashboard").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/sub-admin/side-menu/light').'" class="breadcrumb--active">Sub Admin List</a>'
        ]);
    }

    /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateSubAdmin(Request $request)
    {
        $post = $request->all();
        if(!empty($post)){
            if (isset($post['image']) && $post['image'] != '') {
               $post['photo'] = $post['image'];
               unset($post['image']);
            }else{
                unset($post['image']);
            }

            if(!isset($post['id'])){
                if (Gate::check('sub-admin-create')) {
                  	if(!isset($post['password']) && $post['password']==''){
                      return response(\Helpers::sendFailureAjaxResponse('Enter password.'));
                    }
                    $emailExist = User::where('email',$post['email'])->first();
                    if ($emailExist) {
                        return array('success'=>false,'data'=>null,'message'=>'Email Id already exist !');
                    }
                    $roleDetail = Role::where('id',$post['role_id'])->first();
                    
                    $password = $post['password'];
                    $post['active'] = 1;
                    $post['type'] = 'subadmin';
                    $post['created_at'] = date('Y-m-d h:i:s');
                    $post['password'] = bcrypt($post['password']);
                    $post['role_id'] = $post['role_id'];
                    $ids= User::addUser($post);

                     //echo json_encode($ids);exit;
                    $user= User::find($ids['id']);
                    $role = Role::where('id',$roleDetail->id)->first();
                    $permissions = DB::table('role_has_permissions')->where('role_id',$role->id)->pluck('permission_id')->all();
                    $role->syncPermissions($permissions);
                    $user->assignRole([$role->id]);
                    
                    $emailData = array(             
                        'app_name'=>"Usmile App",
                        'email' => $post['email'],
                        'username'=> $post['name'], 
                        'first_name'=> $post['name'],
                        'reply_text'=> "Your account has been created by admin, Please use: "  .$password.  " password to get login with using your email id : ".$post['email'], 
                    );
                    $toEmail = $emailData['email'];
                    $emailName = $emailData['first_name'];
                    $emailSubject = 'You account has been created as Sub-Admin';
                    \Helpers::sendEmail('emails.create-subadmin',$emailData,$toEmail,'', setting('site_name'), setting('site_name'). ' App',setting('from_email'),'');
                    $msg = __('message_alerts.record_inserted');
                }else{
                    return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                }    

            }else{
                if (Gate::check('sub-admin-edit')) {
                    $emailExist = User::where('id','!=',$post['id'])->where('email',$post['email'])->first();
                    if ($emailExist) {
                        return array('success'=>false,'data'=>null,'message'=>'Email Id already exist !');
                    }
                    $post['updated_at'] = date('Y-m-d h:i:s');
                    if(isset($post['password'])){
                        $post['password'] = bcrypt($post['password']);
                    }
                    $post['role_id'] = $post['role_id'];
                    $id = User::updateUser($post);
                    
                    $user= User::find($post['id']);
                    $role = Role::where('id',$post['role_id'])->first();
                    $permissions = DB::table('role_has_permissions')->where('role_id',$role->id)->pluck('permission_id')->all();
                    $role->syncPermissions($permissions);
                    $user->assignRole([$role->id]);

                    $msg = __('message_alerts.record_updated');
                }else{
                    return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
                }      
            }            
            return array('success'=>true,'data'=>[],'message'=>$msg);
        }else{
            return array('success'=>false,'data'=>null,'message'=>__('message_alerts.something_went_wrong'));
        }
    }

    /**
     * upload category thumb image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadSubAdminThumbImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['image']!=''){
                    $file=$request->file('image');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/user/').$name;
                    $c = \Helpers::compress_image($file,$destination,30);
                }
                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'),$name));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }

    /**
     * Method to delete category
     * @param array $request post data, id
    */
    public function deleteSubAdmin(Request $request,$id)
    {
        User::where('id', $id)->delete();      
        return back()->with('success',__('message_alerts.sub_admin_deleted_success'));
    }

    /**
     * Method to change status of category
     * @param array $request post data ,id ,status
    */
    public function changeSubAdminStatus(Request $request,$id,$status)
    {
        $data = User::where('id',$id)->first();
        if($data->role_id !=0){
            $post['active'] = $status;
            $post['id'] = $id;
            $user = User::updateUser($post);
            return back()->with('success',__('message_alerts.status_changed_success'));
        }else{
            return back()->with('failure', 'Please assgin role');  
        }  
    }  

    public function setpermissions(Request $request)
    {
        $array = array(
            [
                'name'=>'dashboard'
            ],
            [
                'name'=>'feed-item'
            ]
        );
        $user = Permission::create([
                'name'=>'change-status-sub-admin'
            ]);
        return back()->with('success',__('message_alerts.status_changed_success'));  
    } 
}
