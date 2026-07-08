<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class NewsAds extends Model
// {

//     protected $table = 'news_ads';
//     protected $fillable = [
//         'image',
//         'url',
//         'status',
//         'created_at',
//         'updated_at',
//     ];
// }

{



    protected $table = "news_ads";

    protected $fillable = ['image', 'url', 'frequency', 'status', 'created_at', 'updated_at'];

    protected $casts = [
        'frequency' => 'integer',
    ];



    public function section()

    {

        return $this->hasMany(Ads_images::class,'ad_id');

    }

    public function ads_views()

    {

        return $this->hasMany(Ads_action::class,'ad_id','id')->where('action','0')->with('users');

    }

    public function ads_clicks()

    {

        return $this->hasMany(Ads_action::class,'ad_id','id')->where('action','1')->with('users');

    }

    public function blog(){

        return $this->hasMany('App\Models\Blog',"category_id","id")->where('status',1)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->orderBy('id','DESC');

    }



    public function click(){

        return $this->hasMany(Ads_action::class,'ad_id','id')->where('action','1');

    }

    public function view(){

        return $this->hasOne(Ads_action::class,'ad_id','id')->where('action','0');

    }

    public function media()

    {

        return $this->hasMany(Ads_images::class,'ad_id')->orderBy('position','ASC');

    }



    /**

     * Get All Ads

     * @param Search data

     * @return array

    */

    public static function getAllAds($search = ''){

        try {

            $contact = new self;

            $pagination_no = 10;

            if(isset($search['per_page']) && !empty($search['per_page'])){

                $pagination_no = $search['per_page'];

            }



            if(isset($search['url']) && !empty($search['url'] && $search['url'] != '')){

              $contact = $contact->where(DB::raw('LOWER(url)'), 'like', '%'.strtolower($search['url']). '%');

            }



            if(isset($search['start_date']) && !empty($search['start_date'] && $search['start_date'] != '')){

                $contact = $contact->where('start_date',">=",$search['start_date']);

            }

           


            $data = $contact->orderBy('order','ASC')

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

    public static function getAllforDashCategory($search = ''){

        try {

            $contact = new self;

            if(isset($search['name']) && !empty($search['name'] && $search['name'] != '')){

              $contact = $contact->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($search['name']). '%');

            }

            $data = $contact->orderBy('id','DESC')->get();

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

    public static function getAllActiveCategory(){

        try {

            $category = new self;

            $data = $category->where('status',1)->orderBy('name','ASC')->get();

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

    public static function getCategoryBlog($id){

        try {

            $category = new self;

            $data = $category->where('status',1)->where('slug',$id)->with('blog')->first();

            if($data){

                $language = \Helpers::returnUserLangCode();

                $catTranslate = CategoryTranslation::where('category_id',$data->id)->where('language_code',$language)->first();

                if ($catTranslate) {

                    $data->name = $catTranslate->name;

                }

                foreach($data->blog as $row){

                    $row->post_show = false;

                    $blogTranslate = BlogTranslation::where('blog_id',$row->id)->where('language_code',$language)->first();

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

                        $catTranslate = CategoryTranslation::where('category_id',$row->category->id)->where('language_code',$language)->first();

                        if ($catTranslate) {

                            $row->category->name = $catTranslate->name;

                        }

                    }



                    $row->likes = Vote::where('blog_id',$row->id)->get();

                    $row->viewcount = BlogViewCount::where('blog_id',$row->id)->count();

                    $img = BlogImages::where('blog_id',$row->id)->orderBy('id','DESC')->first();

                    $user = User::where('id',$row->created_by)->orderBy('id','DESC')->first();

                    if($user){

                        if($user->type=='admin'){

                            $row->created_by_name = "SuperAdmin";

                        }else{

                            $row->created_by_name = "--";

                        }



                    }else{

                        $row->created_by_name = "--";

                    }

                    if($img){

                        $row->blog_image = $img->image;

                    }

                }

            }



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

    public static function getCategoryOnFilter($limit,$featured=''){

        try {

            $contact = new self;

            if(isset($featured) && $featured != ''){

                $contact = $contact->where('is_featured',1);

            }

            $data = $contact->where('status',1)->orderBy('id','DESC')->limit($limit)->get();

            foreach($data as $category){

                $language = \Helpers::returnUserLangCode();

                $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$language)->first();

                if ($catTranslate) {

                    $category->name = $catTranslate->name;

                }

                $category->blog_count = Blog::where('category_id',$category->id)->count();

            }

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

    public static function getNotFeaturedCategory(){

        try {

            $contact = new self;

            $data = $contact->where('is_featured',0)->where('status',1)->orderBy('id','DESC')->get();

            foreach($data as $category){

                $language = setting('preferred_site_language');

                if (auth()->user() && auth()->user()->type == 'user') {

                    if (auth()->user()->lang_code != '') {

                        $language = auth()->user()->lang_code;

                    }

                }else{

                    if(isset($_COOKIE['lang_code']) && $_COOKIE['lang_code'] != '') {

                        $language = $_COOKIE['lang_code'];

                    }

                }

                $catTranslate = CategoryTranslation::where('category_id',$category->id)->where('language_code',$language)->first();

                if ($catTranslate) {

                    $category->name = $catTranslate->name;

                }

            }

            return $data;

        }catch (\Exception $e) {

            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];

        }

    }







}

