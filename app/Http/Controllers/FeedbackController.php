<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CmsPages;
use App\Models\Languages;
use App\Models\CmsPagesTranslation;
use App\Models\UserFeed;
use App\Models\UserFeedback;
use Illuminate\Support\Facades\Validator;


class FeedbackController extends Controller
{


    /**
     * list of cms pages
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$layout = 'side-menu', $theme = 'light')
    {
    $search = $request->input('name');
    
    if (!empty($search)) {
        $feedback = UserFeedback::where('name', 'like', "%{$search}%")
                    ->orderBy('id', 'desc')
                    ->paginate(20);
    } else {
        $feedback = UserFeedback::orderBy('id', 'desc')->paginate(20);
    }
    
        return view('super-admin/feedbacks.index', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'feedback'=>$feedback,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">'.trans('admin.dashboard').'</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/user-feedback').'" class="breadcrumb--active">'.trans('admin.feedback').'</a>'
        ]);
    }

    /**
     * Delete multiple feedback entries
     */
    public function deleteMultipleFeedback(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', __('message_alerts.select_record'));
        }

        UserFeedback::whereIn('id', $ids)->delete();

        return back()->with('success', __('message_alerts.record_deleted'));
    }
}

?>
