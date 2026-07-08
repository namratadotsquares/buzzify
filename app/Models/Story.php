<?php



namespace App\Models;



use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

use App\Models\CategoryTranslation;

class Story extends Model
{



    protected $table = "stories";

    protected $fillable = ['name', 'phone', 'email', 'story', 'file', 'user_id','reward_id'];

    protected $appends = ['files'];

    use SoftDeletes;



    protected $dates = ['deleted_at'];



    public function getFileAttribute($value)
    {
        $files = $this->decodeFileValue($value);
        return $files[0] ?? '';
    }

    public function getFilesAttribute()
    {
        return $this->decodeFileValue($this->attributes['file'] ?? null);
    }

    private function decodeFileValue($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter($value, function ($v) {
                return is_string($v) && $v !== '';
            }));
        }

        if (!is_string($value)) {
            return [];
        }

        $trimmed = ltrim($value);
        if ($trimmed !== '' && ($trimmed[0] === '[' || $trimmed[0] === '{')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    if ($this->isListArray($decoded)) {
                        return array_values(array_filter($decoded, function ($v) {
                            return is_string($v) && $v !== '';
                        }));
                    }
                    if (isset($decoded['files']) && is_array($decoded['files'])) {
                        return array_values(array_filter($decoded['files'], function ($v) {
                            return is_string($v) && $v !== '';
                        }));
                    }
                }
            }
        }

        return [$value];
    }

    private function isListArray(array $array): bool
    {
        $expectedKeys = range(0, count($array) - 1);
        return $expectedKeys === array_keys($array);
    }

    public static function getAllproduct($search = '')
    {

        try {

            $contact = new self;

            $pagination_no = 10;

            if (isset ($search['per_page']) && !empty ($search['per_page'])) {

                $pagination_no = $search['per_page'];

            }

            // if(isset($search['company_name']) && !empty($search['company_name'] && $search['company_name'] != '')){

            //   $contact = $contact->where(DB::raw('LOWER(company_name)'), 'like', '%'.strtolower($search['company_name']). '%');

            // }

            // Start Search by name- abha

            if (isset ($search['name']) && !empty ($search['name']) && $search['name'] != '') {

                $contact = $contact->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($search['name']) . '%')

                    ->orWhere(DB::raw('LOWER(email)'), 'like', '%' . strtolower($search['name']) . '%');

            }

            // End Search by name- abha

            $data = $contact->latest()->paginate($pagination_no)->appends('per_page', $pagination_no);

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }

    public static function getletestproduct($search = '')
    {

        try {

            $contact = new self;

            $pagination_no = 10;

            if (isset ($search['per_page']) && !empty ($search['per_page'])) {

                $pagination_no = $search['per_page'];

            }

            if (isset ($search['company_name']) && !empty ($search['company_name'] && $search['company_name'] != '')) {

                $contact = $contact->where(DB::raw('LOWER(company_name)'), 'like', '%' . strtolower($search['company_name']) . '%');

            }

            $data = $contact->where('status', 0)->paginate($pagination_no)->appends('per_page', $pagination_no);

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }



    public function user()
    {

        return $this->hasOne('App\Models\User', "id", "user_id");



    }

    public static function updateEpaper($data)
    {

        try {

            $template = new self;

            $id = 0;

            if ($id = $template->where('id', $data['id'])->update($data)) {

                return ['status' => true, 'message' => config('constant.messages.record_updated'), 'id' => $id];

            } else {

                return ['status' => false, 'message' => config('constant.messages.something_went_wrong')];

            }

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }

    public static function getuserstory($user_id = '')
    {

        try {

            $contact = new self;

            $pagination_no = 10;



            //$contact = $contact->where('status', 1);

            $contact = $contact->where('user_id', $user_id)->orderBy('id', 'DESC');

            $data = $contact->paginate($pagination_no)->appends('per_page', $pagination_no)->appends('user_id', $user_id);

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }





}

