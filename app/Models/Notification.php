<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Notification extends Model
{

    protected $table = 'notification';

    protected $fillable = ['user_id', 'title', 'decs', 'notificationId', 'image', 'created_at', 'updated_at'];

    public function users()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public static function getNotification($user_id = '')
    {

        try {

            $contact = new self;

            $pagination_no = 10;



            //$contact = $contact->where('status', 1);

            $contact = $contact->where('user_id', $user_id);

            $data = $contact->orderBy('id', 'DESC')->paginate($pagination_no)->appends('per_page', $pagination_no)->appends('user_id', $user_id);

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }

}

