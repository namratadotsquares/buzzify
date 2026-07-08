<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasApiTokens,HasRoles;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'gender', 'photo','phone','type','fb_token','role_id','otp','otp_expire_at','device_token','fcm_token'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function roleName()
    {
        return $this->hasOne('\Spatie\Permission\Models\Role','id','role_id');
    }

    /**
     * Get All category
     * @param Search data
     * @return array
    */
    public static function getAllUsers($search = ''){
//        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            if(isset($search['category'])){
                $array = array_values($search['category']);
               $contact = UserFeed::whereIn('category_id',$array)->join('users','users.id','=','user_feed.user_id');

            }

            if(isset($search['name']) && $search['name'] != ''){
              $keyword = $search['name'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('email'), 'like', '%'.strtolower($keyword). '%');
                });
            }
            $data = $contact->where('users.type','user')->orderBy('users.id','DESC')
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);

            return $data;
//        }catch (\Exception $e) {
//            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
//        }
    }
    /**
     * Get All category
     * @param Search data
     * @return array
    */
    public static function getAllSubadmin($search = ''){
        try {
            $contact = new self;
            $pagination_no = 10;
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }

            if(isset($search['name']) && $search['name'] != ''){
              $keyword = $search['name'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('email'), 'like', '%'.strtolower($keyword). '%');
                });
            }
            $data = $contact->whereNotIn('type',['admin','user'])->with('roleName')->orderBy('id','DESC')
                    ->paginate($pagination_no)->appends('per_page', $pagination_no);
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Get All category
     * @param Search data
     * @return array
    */
    public static function getAllUsersForDash($search = ''){
        try {
            $contact = new self;
            if(isset($search['name']) && $search['name'] != ''){
              $keyword = $search['name'];
              $contact = $contact->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('email'), 'like', '%'.strtolower($keyword). '%');
                });
            }
            $data = $contact->orderBy('id','DESC')
                    ->get();
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

     public static function addUser($data) {
        try {
            $blog = new self;
            $id=0;
            if($id = $blog->insertGetId($data)) {
                return ['status' => true, 'message' => "User added sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in adding blog" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }


        /**
     * Upadte Category
     * @param Array of post data
     * @return template_id
    */
    public static function updateUser($data) {
        try {
            $template = new self;
            $id=0;
            if($id = $template->where('id', $data['id'])->update($data)) {
                return ['status' => true, 'message' => "User updated sucessfully", 'id' =>$id];
            } else {
                return ['status' => false, 'message' => "Error in updating category" ];
            }
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Check if user has role Admin and Subadmin
     * @param $userId
     * @return bool
     */
    public static function isAdminSubadmin($userId,$role_id) {
        $roles = Role::where('id',$role_id)->first();
        if($roles){
            $roleName = $roles->name;
        }
        return self::checkUserRole($userId, $roleName);
    }

    /**
     * Check user role
     * @param $userId
     * @param $role
     * @return bool
     */
    private static function checkUserRole($userId, $role) {
        try {
            $userRole = self::findOrFail($userId)->hasRole($role);
            return $userRole;
        } catch (ModelNotFoundException $ex) {
            return false;
        }
    }
}
