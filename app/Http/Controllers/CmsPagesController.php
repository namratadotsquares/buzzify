<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CmsPages;
use App\Models\Languages;
use App\Models\CmsPagesTranslation;
use Illuminate\Support\Facades\Validator;

class CmsPagesController extends Controller
{
    function __construct(){
         $this->middleware('permission:cms-pages-list|cms-pages-create|cms-pages-edit|cms-pages-delete', ['only' => ['index','editPage','addUpdateCMSPage','deletePage']]);
         $this->middleware('permission:cms-pages-create', ['only' => ['addPage','store']]);
         $this->middleware('permission:cms-pages-edit', ['only' => ['editPage','addUpdateCMSPage']]);
         $this->middleware('permission:cms-pages-delete', ['only' => ['deletePage']]);
    }

    /**
     * list of cms pages
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $cms = CmsPages::get();
        return view('super-admin/cms.cms-pages-list', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'cms'=>$cms,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/cms-pages/side-menu/light').'" class="breadcrumb--active">'.trans('admin.cms_pages').'</a>'

        ]);
    }

        /**
     * Add Page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addPage($layout = 'side-menu', $theme = 'light')
    {
        
        return view('super-admin/cms.add', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,
            
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/cms-pages/side-menu/light').'" class="breadcrumb">'.trans('admin.cms_pages').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/add-cms-page').'" class="breadcrumb--active">Add CMS</a>'

        ]);
    }
    public function store(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $post = $request->all();
       
         unset($request->_token);
                    $postData = array(
                        'title' => $post['title'],
                        'page_title' => $post['title'],
                        'page_name' => $post['slug'],
                    );
                    if(isset($post['banner_image']) && $post['banner_image'] != ''){
                        $postData['image'] = \Helpers::checkEmpty($post['banner_image']);
                    }
                    if(isset($post['blogdescription']) && $post['blogdescription'] != ''){
                        $postData['description'] = $post['blogdescription'];
                    }
                    if(isset($post['id']) && $post['id'] !='' & $post['id'] != 0){
                        CmsPages::where('id', $post['id'])->update($postData);
                    }else{
                         // dd($postData);
                        $id = CmsPages::insertGetId($postData);
                        $Languages = Languages::get();
                        // foreach ($Languages as $lang) {
                        //     $injectTransLation = [
                        //         'cms_id'=>$id,
                        //         'language_code'=>$lang->language,
                        //         'description'=>$post['description'],
                        //         'title' => $post['title'],
                        //         'page_title' => $post['title'],
                        //         'meta_char' => $post['title'],
                        //         'meta_desc' => $post['title'],
                        //         'created_at' =>date("Y-m-d H:i:s"),
                        //     ];
                        //     CmsPagesTranslation::insertGetId($injectTransLation);
                        // }
                    }

        return redirect('/cms-pages/side-menu/light');
    }

     /**
     * Show Edit Page view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function editPage($layout = 'side-menu', $theme = 'light',$id=0)
    {
        $data = CmsPages::find($id);
        return view('super-admin/cms.edit', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,
            'data'=>$data,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/cms-pages/side-menu/light').'" class="breadcrumb">'.trans('admin.cms_pages').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-cms-page').'/'.$id.'" class="breadcrumb--active">'.$data->page_title.'</a>'

        ]);
    }


     /**
     * upload cms banner image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function uploadCMSBannerImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['image']!=''){
                    $file=$request->file('image');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/cms/original/').$name;
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
     * add update CMS page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateCMSPage(Request $request)
    {
         try {
            if($request->ajax()){
                $post = $request->all();
                $data['prefield'] = $post;
                $validate = [
                    'title' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                    $data['error'] = $validator->errors();
                    $error = '';
                    $errors = (array)$data['error'];
                    foreach ($errors as $row) {
                        foreach ($validate as $key => $value) {
                            if(isset($row[$key])){
                                $error = $row[$key];
                            }
                        }
                    }
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error'),$data['prefield']));
                }else{
                    unset($post['_token']);
                    $postData = array(
                        'title' => $post['title'],
                    );
                    if(isset($post['banner_image']) && $post['banner_image'] != ''){
                        $postData['image'] = \Helpers::checkEmpty($post['banner_image']);
                    }
                    if(isset($post['description']) && $post['description'] != ''){
                        $postData['description'] = $post['description'];
                    }
                    if(isset($post['id']) && $post['id'] !='' & $post['id'] != 0){
                        CmsPages::where('id', $post['id'])->update($postData);
                    }else{
                        $id = CmsPages::insertGetId($postData);
                        $Languages = Languages::get();
                        foreach ($Languages as $lang) {
                            $injectTransLation = [
                                'cms_id'=>$id,
                                'language_code'=>$lang->language,
                                'description'=>$post['description'],
                                'title' => $post['title'],
                                'page_title' => $post['title'],
                                'meta_char' => $post['title'],
                                'meta_desc' => $post['title'],
                                'created_at' =>date("Y-m-d H:i:s"),
                            ];
                            CmsPagesTranslation::insertGetId($injectTransLation);
                        }
                    }
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated')));
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
    
    public function deletePage($id){
        CmsPagesTranslation::where('cms_id',$id)->delete();
        CmsPages::destroy($id);
        return back()->with('success',__('page deleted successfull'));
    }
}
