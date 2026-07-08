<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Models\Category;

use App\Models\Ads;

use App\Models\Languages;

use App\Models\CategoryTranslation;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Gate;

use App\Models\RssFeed;

use App\Models\Blog;





class CategoryController extends Controller
{



    function __construct()
    {

        $this->middleware('permission:category-list|category-delete|category-status|category-featured', ['only' => ['index', 'deleteCategory', 'changeCategoryStatus', 'changeCategoryFeatured']]);

        $this->middleware('permission:category-delete', ['only' => ['deleteCategory']]);

        $this->middleware('permission:category-status', ['only' => ['changeCategoryStatus']]);

        $this->middleware('permission:category-featured', ['only' => ['changeCategoryFeatured']]);

    }







    /**

     * Show Category view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function index(Request $request, $layout = 'side-menu', $theme = 'light')
    {
        $category = Category::getAllCategory($request->all());
       

        $languages = Languages::get();
        foreach ($category as $row) {
            $val = CategoryTranslation::where('category_id', $row->id)->where('language_code', setting('preferred_site_language'))->first();
            if ($val) {
                $row->language_code = $val->language_code;
                $row->category_name_trans = $val->name;
            } else {
                $row->language_code = setting('preferred_site_language');
                $row->category_name_trans = $row->name;
            }
        }
        $categories = Category::where('status', 1)->get();
        

        return view('super-admin/category.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'languages' => $languages,
            'category' => $category,
            'categories' => $categories,
            'breadcrumb' => '<a href="' . url('/') . '" class="breadcrumb">' . trans('admin.dashboard') . '</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="' . url('/category/side-menu/light') . '" class="breadcrumb--active">' . trans('admin.category_list') . '</a>'
        ]);

    }



    /**

     * Show Category view.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    // public function addUpdateCategory(Request $request)
    // {
    //     $post = $request->all();
    //     if (!empty ($post)) {
    //         if (isset ($post['thumb_image']) && $post['thumb_image'] != '') {
    //             $post['image'] = $post['thumb_image'];
    //             unset($post['thumb_image']);
    //         }
    //         if (!isset ($post['id'])) {
    //             $order = isset($post['order']) ? $post['order'] : 0;
    //             if (Gate::check('category-create')) {
    //                 $slug = \Helpers::createSlug($post['name'], 'category', 0, false);
    //                 $post['slug'] = $slug;
    //                 $post['created_at'] = date('Y-m-d h:i:s');
    //                 $post['order'] = $order; 
    //                 $id = Category::addCategory($post);
    //                 $Languages = Languages::get();
    //                 foreach ($Languages as $lang) {
    //                     $injectTransLation = [
    //                         'category_id' => $id['id'],
    //                         'language_code' => $lang->language,
    //                         'name' => $post['name'],
    //                         'created_at' => date("Y-m-d H:i:s"),
    //                     ];
    //                     CategoryTranslation::insertGetId($injectTransLation);
    //                 }
    //                 $msg = __('message_alerts.record_inserted');
    //             } else {
    //                 return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
    //             }
    //         } else {
    //             $order = isset($post['order']) ? $post['order'] : 0;
    //             if (Gate::check('category-edit')) {
    //                 $slug = \Helpers::createSlug($post['name'], 'category', $post['id'], false);
    //                 $post['slug'] = $slug;
    //                 $post['updated_at'] = date('Y-m-d h:i:s');
    //                 $post['order'] = $order; 
    //                 $id = Category::updateCategory($post);
    //                 if ($post['id']) {
    //                     $injectTransLation = [
    //                         'name' => $post['name'],
    //                         'updated_at' => date("Y-m-d H:i:s"),
    //                     ];
    //                     CategoryTranslation::where('category_id', $post['id'])->where('language_code', 'en')->update($injectTransLation);
    //                 }
    //                 $msg = __('message_alerts.record_updated');
    //             } else {
    //                 return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
    //             }
    //         }
    //         return array('success' => true, 'data' => $id, 'message' => $msg);
    //     } else {
    //         return array('success' => false, 'data' => null, 'message' => __('message_alerts.something_went_wrong'));
    //     }
    // }
    
    public function addUpdateCategory(Request $request)
{
    $post = $request->all();

    // Get existing order numbers
    $existingOrders = Category::pluck('order');

    // Update logic
    if (isset($post['order']) && isset($post['id'])) {
    $newOrder = $post['order'];
    $existingOrder = Category::where('id', $post['id'])->value('order');

    // Check if the category is existing
    $isExistingCategory = Category::where('id', $post['id'])->exists();

    if ($isExistingCategory) {
        // Set the new order for the updating category
        Category::where('id', $post['id'])
            ->update(['order' => $newOrder]);

        // Update the order of the category which was at the new position
        // Category::where('order', $newOrder)
        //     ->where('id', '!=', $post['id'])
        //     ->update(['order' => 1]);
    }
}

    // Rest of your code
    if (!empty($post)) {
        if (isset($post['thumb_image']) && $post['thumb_image'] != '') {
            $post['image'] = $post['thumb_image'];
            unset($post['thumb_image']);
        }
        if (!isset($post['id'])) {
            $order = isset($post['order']) ? $post['order'] : 0;
            if (Gate::check('category-create')) {
                $slug = \Helpers::createSlug($post['name'], 'category', 0, false);
                $post['slug'] = $slug;
                $post['created_at'] = date('Y-m-d h:i:s');
                $post['order'] = $order;
                $id = Category::addCategory($post);
                $Languages = Languages::get();
                foreach ($Languages as $lang) {
                    $injectTransLation = [
                        'category_id' => $id['id'],
                        'language_code' => $lang->language,
                        'name' => $post['name'],
                        'created_at' => date("Y-m-d H:i:s"),
                    ];
                    CategoryTranslation::insertGetId($injectTransLation);
                }
                $msg = __('message_alerts.record_inserted');
            } else {
                return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
            }
        } else {
            $order = isset($post['order']) ? $post['order'] : 0;
            if (Gate::check('category-edit')) {
                $slug = \Helpers::createSlug($post['name'], 'category', $post['id'], false);
                $post['slug'] = $slug;
                $post['updated_at'] = date('Y-m-d h:i:s');
                $post['order'] = $order;
                $id = Category::updateCategory($post);
                if ($post['id']) {
                    $injectTransLation = [
                        'name' => $post['name'],
                        'updated_at' => date("Y-m-d H:i:s"),
                    ];
                    CategoryTranslation::where('category_id', $post['id'])->where('language_code', 'en')->update($injectTransLation);
                }
                $msg = __('message_alerts.record_updated');
            } else {
                return response(\Helpers::sendFailureAjaxResponse('User does not have a right permission.'));
            }
        }
        return array('success' => true, 'data' => $id, 'message' => $msg);
    } else {
        return array('success' => false, 'data' => null, 'message' => __('message_alerts.something_went_wrong'));
    }
}








    /**

     * upload category thumb image

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */





    public function uploadCategoryThumbImage(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $name = '';

                if ($post['image'] != '') {

                    $file = $request->file('image');

                    $name = time() . rand() . '.' . $file->getClientOriginalExtension();

                    $destination = public_path('/upload/category/original/') . $name;

                    $c = \Helpers::compress_image($file, $destination, 30);

                }



                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), $name));

            } else {

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

    public function deleteCategory(Request $request)
    {

        try {

            if ($request->ajax()) {

                $post = $request->all();

                $blogs = Blog::where('category_id', $post['id'])->get();

                if (isset ($post['category_id']) && $post['category_id'] != '') {

                    foreach ($blogs as $blog) {

                        Blog::where('id', $blog->id)->update(['category_id' => $post['category_id']]);

                    }

                } else {

                    foreach ($blogs as $blog) {

                        Blog::where('id', $blog->id)->update(['status' => 0]);

                    }

                }

                Category::where('id', $post['id'])->delete();

                CategoryTranslation::where('category_id', $post['id'])->delete();

                RssFeed::where('category_id', $post['id'])->update(['status' => 0]);

                return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.category_deleted_success'), []));

            } else {

                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));

            }

        } catch (\Exception $ex) {

            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));

        }

        //Category::where('id', $id)->delete();      

        //CategoryTranslation::where('category_id',$id)->delete();

        //RssFeed::where('category_id',$id)->update(['status' => 0]); 

        //return back()->with('success',__('message_alerts.category_deleted_success'));

    }

    /**

     * Method to change status of category

     * @param array $request post data ,id ,status

    */

    public function changeCategoryStatus(Request $request, $id, $status)
    {

        $post['status'] = $status;

        $post['id'] = $id;

        Category::updateCategory($post);

        return back()->with('success', __('message_alerts.status_changed_success'));

    }





    /**

 * update category

 *

 * @param  \Illuminate\Http\Request  $request

 * @return \Illuminate\Http\Response

 */



    public function update(Request $request)
    {

        $posts = Category::all();

        foreach ($posts as $post) {

            foreach ($request->order as $order) {

                if ($order['id'] == $post->id) {

                    $c = Category::where('id', $post->id)->update(['order' => $order['position']]);

                }

            }

        }

        return response(__('message_alerts.record_updated'), 200);

    }







    /**

     * Method to change fetured of category

     * @param array $request post data ,id ,status

    */

    public function changeCategoryFeatured(Request $request, $id, $status)
    {

        $post['is_featured'] = $status;

        $c = Category::where('id', $id)->update($post);

        return back()->with('success', __('message_alerts.status_changed_success'));

    }

}

