<?php



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;



/*

|--------------------------------------------------------------------------

| API Routes

|--------------------------------------------------------------------------

|

| Here is where you can register API routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| is assigned the "api" middleware group. Enjoy building your API!

|

*/



Route::middleware('auth:api')->get('/user', function (Request $request) {

	return $request->user();

});



Route::get('test', 'UserAPIController@test');

Route::post('login', 'UserAPIController@login');
Route::post('add_app_version', 'UserAPIController@add_app_version');
Route::get('get_app_version', 'UserAPIController@get_app_version');

Route::post('register', 'UserAPIController@register');

Route::post('feedback', 'UserAPIController@feedback');

Route::post('send-story', 'UserAPIController@save_story');
Route::post('increaseStoryViewCount', 'UserAPIController@increaseStoryViewCount');
Route::get('notificationEnabled', 'UserAPIController@notificationEnabled');
Route::get('notificationEnabledUser', 'UserAPIController@notificationEnabledUser');
Route::get('notificationCount', 'UserAPIController@notificationCount');
Route::get('getCustomNotification', 'UserAPIController@getCustomNotification');
Route::get('getUser', 'UserAPIController@getUser');
Route::post('logout', 'UserAPIController@logout');



Route::get('product-list', 'API\EpaperAPIController@productlist');

Route::get('list-story', 'UserAPIController@list_story');

Route::get('list-request', 'UserAPIController@list_request');

Route::get('list-wallet-history', 'UserAPIController@wallet_history');

Route::get('user-wallet', 'UserAPIController@user_wallet');

Route::post('product-request', 'UserAPIController@pro_req');



Route::post('addDeviceToken', 'UserAPIController@addDeviceToken');
Route::post('removeDeviceToken', 'UserAPIController@removeDeviceToken');

Route::post('forgot-password', 'UserAPIController@forgetPassword');
Route::get('sendTestMail', 'UserAPIController@sendTestMail');

Route::post('reset-password', 'UserAPIController@resetPassword');

Route::post('verify-otp', 'UserAPIController@verifyOtp');

Route::post('resend-otp', 'UserAPIController@resendOtp');

Route::post('socialMediaLogin', 'UserAPIController@socialMediaLogin');

Route::get('blog-category-list', 'CategoryAPIController@list');
Route::get('category-list-with-blog', 'CategoryAPIController@list_with_blog');
Route::get('blog-categorys-list', 'CategoryAPIController@listData');
Route::get('list-news/{id}', 'CategoryAPIController@listNews');
Route::get('blog-category', 'CategoryAPIController@prefernceData');
Route::get('blog-category/{id}', 'CategoryAPIController@categorylist');



Route::get('blog-list', 'BlogAPIController@list');

Route::get('blog-details/{id}', 'BlogAPIController@detail');

Route::get('setting-list', 'BlogAPIController@settingList');

Route::get('blog-all-list', 'BlogAPIController@allBloglist');
Route::get('blog-all-lists', 'BlogAPIController@allBloglists');

Route::post('blog/action', 'BlogAPIController@action');

Route::post('get-single-blog-data', 'BlogAPIController@getSingleData');

Route::post('getProfile', 'UserAPIController@getProfile');

Route::post('updateProfile', 'UserAPIController@updateProfile');

Route::post('updateProfilePicture', 'UserAPIController@updateProfilePicture');

Route::post('changePassword', 'UserAPIController@changePassword');

Route::post('searchBlog', 'BlogAPIController@searchBlog');

Route::post('updateToken', 'UserAPIController@updateToken');

Route::post('deleteAccount', 'UserAPIController@deleteAccount');

Route::post('bookmarkPost', 'BlogAPIController@bookmarkPost');

Route::post('deleteBookmarkPost', 'BlogAPIController@deleteBookmarkPost');

Route::post('AllBookmarkPost', 'BlogAPIController@AllBookmarkPost');

Route::post('increaseBlogViewCount', 'BlogAPIController@increaseBlogViewCount');
Route::post('blogView', 'BlogAPIController@blogView');
Route::get('blogResetview', 'BlogAPIController@blogResetview');
Route::post('addBlogVote', 'BlogAPIController@addBlogVote');

Route::post('blogSwipe', 'BlogAPIController@blogSwipe');

Route::post('getBlogVote', 'BlogAPIController@getBlogVote');

Route::post('nextPreviousBlog', 'BlogAPIController@nextPreviousBlog');

Route::get('e-news-list', 'API\EpaperAPIController@list');

Route::get('live-news-list', 'API\LiveNewsAPIController@list');

Route::get('language/lists', 'API\LanguagesAPIController@lists');

Route::get('keys/lists', 'API\LanguagesAPIController@keysLists');

Route::get('cms_page/{language_code?}', 'API\CmsPagesController@index');



// myfeed

Route::post('add_feed', 'API\UserFeedController@store');

Route::get('getFeed/{userID}', 'API\UserFeedController@index');
Route::get('getWithoutFeed/{userID}', 'API\UserFeedController@index2');

Route::get('getAllFeed', 'API\UserFeedController@getAllFeed');
Route::get('getAllFeeds', 'API\UserFeedController@getAllFeeds');
Route::get('getAlllocalfeed', 'API\UserFeedController@get_local_news');



//ads

Route::post('uploads/ads_images', 'AddController@upload')->name('medias.create');

Route::post('reArrange/ads_images', 'AddController@reArrange')->name('medias.reArrange');

Route::post('uploads/ads_images', 'AddController@uploadImages');


Route::get('Ads/fullscreenads', 'API\AdsController@fullscreenads');
Route::post('Ads/action', 'API\AdsController@action');
Route::get('Ads/newsads', 'API\AdsController@newsads');


