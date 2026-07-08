<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
     /**
     * Show Category view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    function __construct(){
         $this->middleware('permission:author-list|author-create|author-edit|author-delete', ['only' => ['index','addUpdateAuthor']]);
         $this->middleware('permission:author-create', ['only' => ['addUpdateAuthor']]);
         $this->middleware('permission:author-edit', ['only' => ['addUpdateAuthor']]);
         $this->middleware('permission:author-delete', ['only' => ['deleteAuthor']]);
    }


    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
        $author = Author::getAllAuthors($request->all());
        return view('super-admin/author.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'author'=>$author,
        ]);
    }


     /**
     * upload image
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function uploadImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                $name = '';
                if($post['image']!=''){
                    $file=$request->file('image');
                    $name = time() . rand() .'.'.$file->getClientOriginalExtension();
                    $destination =  public_path('/upload/author/original/').$name;
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
     * Sadd update author
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addUpdateAuthor(Request $request)
    {
         try {
            if($request->ajax()){
                $post = $request->all();
                $data['prefield'] = $post;
                $validate = [
                    'name' => 'required',
                    'email' => 'required',
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
                        'name' => $post['name'],
                        'email' => \Helpers::checkEmpty($post['email']),
                        'designation' => $post['designation'],
                    );

                    if(isset($post['authorimage']) && $post['authorimage'] != ''){
                        $postData['image'] = \Helpers::checkEmpty($post['authorimage']);
                    }

                    if(isset($post['id']) && $post['id'] !='' & $post['id'] != 0){
                        $catExist = Author::where(DB::raw('LOWER(email)'),strtolower($post['email']))->where('id','!=',$post['id'])->get();
                        if (count($catExist)>0) {
                            return response(\Helpers::sendFailureAjaxResponse('This email already exist.',$data['prefield']));
                        }else{
                            Author::where('id', $post['id'])->update($postData);
                        }
                    }else{
                        $catExist = Author::where(DB::raw('LOWER(email)'),strtolower($post['email']))->where('id','!=',0)->get();
                        if (count($catExist)>0) {
                            return response(\Helpers::sendFailureAjaxResponse('This email already exist.',$data['prefield']));
                        }else{
                            $postData['created_at'] = date('Y-m-d H:i:s');
                            Author::insert($postData);
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

        /**
     * Method to delete category
     * @param array $request post data, id
    */
    public function deleteAuthor(Request $request,$id)
    {
        Author::where('id', $id)->delete();      
        return back()->with('success',__('message_alerts.author_deleted_success'));
    }
    /**
     * Method to change status of category
     * @param array $request post data ,id ,status
    */
    public function changeAuthorStatus(Request $request,$id,$status)
    {
        $post['status'] = $status;
        $post['id'] = $id;
        Author::updateAuthor($post);         
        return back()->with('success',__('message_alerts.status_changed_success'));  
    }

}
