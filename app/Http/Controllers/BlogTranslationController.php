<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogTranslation;
use App\Models\BlogActionLog;
use App\Models\Languages;
use App\Models\SiteContent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class BlogTranslationController extends Controller
{

    function __construct(){
         $this->middleware('permission:blog-translate', ['only' => ['index']]);
         //$this->middleware('permission:blog-translate-show', ['only' => ['show']]);
         //$this->middleware('permission:blog-translate-store', ['only' => ['store']]);
    }


	    /**
     * Show Edit Blog view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index($layout = 'side-menu', $theme = 'light',$id=0)
    {
         $blog = Blog::find($id);
        $languages =  Languages::get();
        $data = BlogTranslation::where('blog_id',$id)->where('language_code',setting('preferred_site_language'))->first();
        return view('super-admin/blog.edit-translation', [
            'theme' => $theme,
            'page_name' => 'create',
            'side_menu' => array(),
            'layout' => $layout,
            'languages'=>$languages,
            'blog'=>$blog,
            'data'=>$data,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/blog/side-menu/light').'" class="breadcrumb">'.trans("admin.blog_post").'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/edit-blog-translation'. '/' . $layout . '/' . $theme).'/'.$id.'" class="breadcrumb--active">'.trans("admin.edit_blog_translation").'</a>'


        ]);
    }

     	/**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try {
            if($request->ajax()){
                $post = $request->all();
                $validate = [
                    'blog_id' => 'required',
                    'language_code' => 'required',
                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing')));
                }else{
                   	$data = BlogTranslation::where('blog_id',$post['blog_id'])->where('language_code',$post['language_code'])->first();
                    return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_found'),$data));
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }



   	/**
     * store data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if($request->ajax()){
                $post = $request->all();
                $validate = [
                    'blog_id' => 'required',
                    'language_code' => 'required',
                    'title' => 'required',
                   'description' => [
                        'required',
                        function ($attribute, $value, $fail) {
                              $max_words=SiteContent::where('key','news_max_words')->first()->value??60;
                            if (str_word_count(strip_tags($value)) > $max_words) {
                                $fail('The ' . $attribute . ' may not be more than '.$max_words.' words.');
                            }
                        },
                    ],

                ];
                $validator = Validator::make($post, $validate);
                if ($validator->fails()) {
                     $data['error'] = $validator->errors();
                    return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.required_field_missing'),$data));
                }else{
                   	$exist = BlogTranslation::where('blog_id',$post['blog_id'])->where('language_code',$post['language_code'])->first();

                   	$inject = [
                   		'blog_id'=>$post['blog_id'],
                   		'language_code'=>$post['language_code'],
                   		'description'=>$post['description'],
                   		'title' => $post['title'],
                   		'seo_description' => $post['seo_description'],
                   		'seo_keyword' => $post['seo_keyword'],
                   		'seo_tag' => $post['seo_tag'],
                   		'seo_title' => $post['seo_title'],
                   		'tags' => $post['tags'],
                   	];
                       Blog::where('id',$post['blog_id'])->update([
                        'latitude'=>$post['latitude'],
                        'longitude'=>$post['longitude'],
                        'is_location_radius'=>(isset($post['check_location_radius']) && $post['check_location_radius']=='on')?1:0,
                       ]);
                   	if ($exist) {
                   		BlogTranslation::where('id',$exist->id)->update($inject);
                   		$id = $exist->id;
                   	}else{
                   		$inject['created_at'] = date('Y-m-d h:i s');
                   		$id = BlogTranslation::insertGetId($inject);
                   	}
                    if ($id) {
                        BlogActionLog::record('blog_translated', (int) $post['blog_id'], Auth::check() ? (int) Auth::id() : null, [
                            'source' => 'blog_translation_form',
                            'languages' => [$post['language_code']],
                        ]);

                        $layout = $post['layout'] ?? 'side-menu';
                        $theme = $post['theme'] ?? 'light';
                        $redirectUrl = url('/edit-blog' . '/' . $layout . '/' . $theme . '/' . $post['blog_id']);
                        return response(\Helpers::sendSuccessAjaxResponse(__('message_alerts.record_updated'), [
                            'id' => $id,
                            'redirect_url' => $redirectUrl,
                        ]));
                    } else {
                     	return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
                   	}
                }
            }else{
                return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(__('message_alerts.there_is_an_error')));
        }
    }
}
