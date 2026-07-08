<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

use App\Models\CategoryTranslation;

class Category extends Model
{



    protected $table = "category";

    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'image', 'color', 'order', 'status', 'is_featured'];

    protected $dates = ['deleted_at'];

    public function blog()
    {

        return $this->hasMany('App\Models\Blog', "category_id", "id")->where('status', 1)->where('schedule_date', "<=", date("Y-m-d H:i:s"))->orderBy('id', 'DESC');

    }



    /**

     * Add Contact

     * @param Array of post data

     * @return category_id

    */

    public static function addCategory($data)
    {

        try {

            $category = new self;

            $id = 0;

            if ($id = $category->insertGetId($data)) {

                return ['status' => true, 'message' => config('constant.messages.record_inserted'), 'id' => $id];

            } else {

                return ['status' => false, 'message' => config('constant.messages.something_went_wrong')];

            }

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }



    /**

     * Upadte Category

     * @param Array of post data

     * @return template_id

    */

    public static function updateCategory($data)
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



    /**

     * Get All category

     * @param Search data

     * @return array

    */

    public static function getAllCategory($search = '')
    {

        try {

            $contact = new self;

            $pagination_no = 10;

            if (isset ($search['per_page']) && !empty ($search['per_page'])) {

                $pagination_no = $search['per_page'];

            }



            if (isset ($search['name']) && !empty ($search['name'] && $search['name'] != '')) {

                $contact = $contact->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($search['name']) . '%');

            }

            // $contact = $contact->where('status', 1);

               // $data = $contact->orderByRaw("CAST(`order` AS UNSIGNED) ASC")->paginate($pagination_no)->appends('per_page', $pagination_no);
               $data = $contact->orderByRaw("CAST(`order` AS UNSIGNED) ASC")
                ->orderBy('updated_at', 'desc')
                ->paginate($pagination_no)
                ->appends('per_page', $pagination_no);


            if (count($data)) {

                foreach ($data as $row) {

                    $row->total_blog = Blog::where('category_id', $row->id)->count();

                }

            }
            // echo "<pre>";
            // foreach($data as $datas){
            // print_r($datas);
            // }
            // exit();
            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }

    /**

     * Get All category

     * @param Search data

     * @return array

    */

    public static function getAllforDashCategory($search = '')
    {

        try {

            $contact = new self;

            if (isset ($search['name']) && !empty ($search['name'] && $search['name'] != '')) {

                $contact = $contact->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($search['name']) . '%');

            }

            $data = $contact->orderBy('id', 'DESC')->get();

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }



    /**

     * Get All category

     * @param Search data

     * @return array

    */

    public static function getAllActiveCategory()
    {

        try {

            $category = new self;

            $data = $category->where('status', 1)->orderBy('name', 'ASC')->get();

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }

    /**

     * Get All category

     * @param Search data

     * @return array

    */

    public static function getCategoryBlog($id)
    {

        try {

            $category = new self;

            $pagination_no = 10;

            if (isset ($search['per_page']) && !empty ($search['per_page'])) {

                $pagination_no = $search['per_page'];

            }

            $data = $category->where('status', 1)->where('slug', $id)->with('blog')->first();

            if ($data) {

                $language = \Helpers::returnUserLangCode();

                $catTranslate = CategoryTranslation::where('category_id', $data->id)->where('language_code', $language)->first();

                if ($catTranslate) {

                    $data->name = $catTranslate->name;

                }

                foreach ($data->blog as $row) {

                    $row->post_show = false;

                    $blogTranslate = BlogTranslation::where('blog_id', $row->id)->where('language_code', $language)->first();

                    if ($blogTranslate) {

                        $row->post_show = true;

                        $row->title = $blogTranslate->title;

                        $row->tags = $blogTranslate->tags;

                        $row->description = $blogTranslate->description;

                        $row->seo_title = $blogTranslate->seo_title;

                        $row->seo_keyword = $blogTranslate->seo_keyword;

                        $row->seo_tag = $blogTranslate->seo_tag;

                        $row->seo_description = $blogTranslate->seo_description;

                    }



                    if ($row->category) {

                        $catTranslate = CategoryTranslation::where('category_id', $row->category->id)->where('language_code', $language)->first();

                        if ($catTranslate) {

                            $row->category->name = $catTranslate->name;

                        }

                    }



                    $row->likes = Vote::where('blog_id', $row->id)->get();

                    $row->viewcount = BlogViewCount::where('blog_id', $row->id)->count();

                    $img = BlogImages::where('blog_id', $row->id)->orderBy('id', 'DESC')->first();

                    $user = User::where('id', $row->created_by)->orderBy('id', 'DESC')->first();

                    if ($user) {

                        if ($user->type == 'admin') {

                            $row->created_by_name = "SuperAdmin";

                        } else {

                            $row->created_by_name = "--";

                        }



                    } else {

                        $row->created_by_name = "--";

                    }

                    if ($img) {

                        $row->blog_image = $img->image;

                    }

                }

            }



            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }



    // /**

    //  * Get All category

    //  * @param Search data

    //  * @return array

    // */

    // public static function getCategoryBlog($id){

    //     try {

    //         $category = new self;

    //         $data = $category->where('status',1)->where('slug',$id)->with('blog')->first();

    //         if($data){

    //             $language = \Helpers::returnUserLangCode();

    //             $catTranslate = CategoryTranslation::where('category_id',$data->id)->where('language_code',$language)->first();

    //             if ($catTranslate) {

    //                 $data->name = $catTranslate->name;

    //             }

    //             foreach($data->blog as $row){

    //                 $row->post_show = false;

    //                 $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$language)->first();

    //                 if ($blogTranslate) {

    //                     $row->post_show = true;

    //                     $row->title = $blogTranslate->title;

    //                     $row->tags = $blogTranslate->tags;

    //                     $row->description = $blogTranslate->description;

    //                     $row->seo_title = $blogTranslate->seo_title;

    //                     $row->seo_keyword = $blogTranslate->seo_keyword;

    //                     $row->seo_tag = $blogTranslate->seo_tag;

    //                     $row->seo_description = $blogTranslate->seo_description;

    //                 }



    //                 if ($row->category) {

    //                     $catTranslate = CategoryTranslation::where('category_id',$row->category->id)->where('language_code',$language)->first();

    //                     if ($catTranslate) {

    //                         $row->category->name = $catTranslate->name;

    //                     }

    //                 }



    //                 $row->likes = Vote::where('blog_id',$row->id)->get();

    //                 $row->viewcount = BlogViewCount::where('blog_id',$row->id)->count();

    //                 $img = BlogImages::where('blog_id',$row->id)->orderBy('id','DESC')->first();

    //                 $user = User::where('id',$row->created_by)->orderBy('id','DESC')->first();

    //                 if($user){

    //                     if($user->type=='admin'){

    //                         $row->created_by_name = "SuperAdmin";

    //                     }else{

    //                         $row->created_by_name = "--";

    //                     }



    //                 }else{

    //                     $row->created_by_name = "--";

    //                 }

    //                 if($img){

    //                     $row->blog_image = $img->image;

    //                 }

    //             }

    //         }



    //         return $data;

    //     }catch (\Exception $e) {

    //         return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];

    //     }

    // }



    /**

     * Get All category

     * @param Search data

     * @return array

    */

    public static function getCategoryOnFilter($limit, $featured = '')
    {

        try {

            $contact = new self;

            if (isset ($featured) && $featured != '') {

                $contact = $contact->where('is_featured', 1);

            }

            $data = $contact->where('status', 1)->orderBy('id', 'DESC')->limit($limit)->get();

            foreach ($data as $category) {

                $language = \Helpers::returnUserLangCode();

                $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $language)->first();

                if ($catTranslate) {

                    $category->name = $catTranslate->name;

                }

                $category->blog_count = Blog::where('category_id', $category->id)->count();

            }

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }







    /**

 * Get All category

 * @param Search data

 * @return array

*/

    public static function getNotFeaturedCategory()
    {

        try {

            $contact = new self;

            $data = $contact->where('is_featured', 0)->where('status', 1)->orderBy('id', 'DESC')->get();

            foreach ($data as $category) {

                $language = setting('preferred_site_language');

                if (auth()->user() && auth()->user()->type == 'user') {

                    if (auth()->user()->lang_code != '') {

                        $language = auth()->user()->lang_code;

                    }

                } else {

                    if (isset ($_COOKIE['lang_code']) && $_COOKIE['lang_code'] != '') {

                        $language = $_COOKIE['lang_code'];

                    }

                }

                $catTranslate = CategoryTranslation::where('category_id', $category->id)->where('language_code', $language)->first();

                if ($catTranslate) {

                    $category->name = $catTranslate->name;

                }

            }

            return $data;

        } catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile()];

        }

    }







}

