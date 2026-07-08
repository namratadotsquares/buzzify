<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache is cleared";
});

Route::get('givePermissionToAdmin', 'UserController@givePermissionToAdmin');

/************************
cronjobs
***********************/
Route::get('blog/notification/cron-job', 'BlogController@executeCron');
Route::get('refreshFbToken', function() {
    Artisan::call('refresh:fbtoken');
    return "Schedular Called!";
});


Route::get('quote-notification', function() {
    Artisan::call('quote:cron');
    return "Schedular Called!";
});


Route::get('/', 'SiteController@index');
Route::get('/category-blog', 'SiteController@category_blogs');
Route::get('publish/{id}', 'BlogController@publish');
// Route::get('/blog-detail/{blog_id}', 'SiteController@blog_detail');

/*
    Admin Routing
*/
Route::middleware('loggedin')->group(function() {
    Route::get('admin-login', 'AuthController@loginView')->name('login-view');
    Route::post('admin-login', 'AuthController@login')->name('login');
});

// Route::group(['prefix'=>  config('constant.admin_prefix'), 'middleware' => 'auth'], function() { 

Route::middleware('auth')->group(function() { 
    Route::resource('roles','RoleController');
    Route::get('/admin', 'DashboardController@index');
    Route::get('logout', 'AuthController@logout')->name('logout');
    Route::get('page/{layout}/{theme}/{pageName}', 'PageController@loadPage')->name('page');
    Route::get('/dashboard/{layout}/{theme}', 'DashboardController@index');
    Route::get('/category/{layout}/{theme}', 'CategoryController@index');
    Route::post('/category/{layout}/{theme}', 'CategoryController@index');
    Route::post('/add-update-category', 'CategoryController@addUpdateCategory');
    Route::get('/delete-category/{id}', 'CategoryController@deleteCategory');
    Route::get('/change-status-category/{id}/{status}', 'CategoryController@changeCategoryStatus');
    Route::get('/mark-category-featured/{id}/{status}', 'CategoryController@changeCategoryFeatured');
    Route::post('/uploadCategoryThumbImage', 'CategoryController@uploadCategoryThumbImage');
    Route::post('/category-sortable','CategoryController@update');
    Route::get('/rss-feed-src/{layout}/{theme}', 'RssFeedController@index');
    Route::post('/rss-feed-src/{layout}/{theme}', 'RssFeedController@index');
    Route::post('/add-update-rss-feed-src', 'RssFeedController@addUpdateRssFeedSrc');
    Route::get('/delete-rss-feed-src/{id}', 'RssFeedController@deleteRssFeed');
    Route::get('/change-status-rss-feed-src/{id}/{status}', 'RssFeedController@changeRssFeedStatus');
    Route::get('/feed-item/{layout}/{theme}', 'RssFeedController@feedItem');
    Route::post('/feed-item/{layout}/{theme}', 'RssFeedController@feedItem');
    Route::get('/save-post/{post_id}/{category_id}', 'RssFeedController@saveFeed');
    Route::get('/author/{layout}/{theme}', 'AuthorController@index');
    Route::post('/author/{layout}/{theme}', 'AuthorController@index');
    Route::post('/uploadImage', 'AuthorController@uploadImage');
    Route::post('/addUpdateAuthor', 'AuthorController@addUpdateAuthor');
    Route::get('/change-status-author/{id}/{status}', 'AuthorController@changeAuthorStatus');
    Route::get('/deleteAuthor/{id}', 'AuthorController@deleteAuthor');
    Route::get('/blog/{layout}/{theme}', 'BlogController@index');
    Route::post('/blog/{layout}/{theme}', 'BlogController@index');
    Route::get('/add-blog', 'BlogController@addBlog');
    Route::get('/edit-blog/{id}', 'BlogController@editBlog');
    Route::get('/delete-blog/{id}', 'BlogController@deleteBlog');
    Route::get('/deleteBlogImage/{id}', 'BlogController@deleteBlogImage');
    Route::post('/uploadMultipleBannerImage', 'BlogController@uploadMultipleBannerImage');
    Route::post('/uploadAudioFIle', 'BlogController@uploadAudioFIle');
	Route::get('/change-status-blog/{id}/{status}', 'BlogController@changeBlogStatus');
    Route::post('/uploadBlogThumbImage', 'BlogController@uploadBlogThumbImage');
    Route::post('/uploadBannerImage', 'BlogController@uploadBannerImage');    
    Route::post('/addUpdateblog', 'BlogController@addUpdateblog');
    Route::post('/blog-sortable','BlogController@update');
    Route::get('/blog-sortable','BlogController@update');
    Route::get('/delete-slider-post/{id}', 'BlogController@deleteSliderPost');
    Route::get('/slider/{layout}/{theme}', 'BlogController@slider');
    Route::post('/slider/{layout}/{theme}', 'BlogController@slider');
    Route::get('/users/{layout}/{theme}', 'UserController@index');
    Route::post('/users/{layout}/{theme}', 'UserController@index');
    Route::get('/delete-user/{id}', 'UserController@deleteUser');
    Route::get('/change-status-user/{id}/{status}', 'UserController@changeUserStatus');
    Route::get('/profile/{layout}/{theme}', 'UserController@profile');
    Route::post('/editProfile', 'UserController@editProfile');
    Route::post('/uploadProfileImage', 'UserController@uploadProfileImage');
    Route::get('/setting/{page}', 'SiteContentController@index');
    Route::get('/sendMail', 'SiteContentController@sendMail');
    Route::post('/uploadLogoImage', 'SiteContentController@uploadLogoImage');
    Route::post('/uploadBGImage', 'SiteContentController@uploadBGImage');
    Route::post('/updateSetting', 'SiteContentController@updateSetting');
    Route::post('/add-update-social', 'SiteContentController@addUpdateSocial');
    Route::get('/delete-social/{id}', 'SiteContentController@deleteSocial');
    Route::get('/change-status-social/{id}/{status}', 'SiteContentController@changeSocialStatus');
    Route::post('/settingPermission', 'SiteContentController@settingPermission');
    Route::get('/search-log/{layout}/{theme}', 'SearchController@index');
    Route::post('/search-log/{layout}/{theme}', 'SearchController@index');
    Route::get('/cms-pages/{layout}/{theme}', 'CmsPagesController@index');
    Route::post('/cms-pages/{layout}/{theme}', 'CmsPagesController@index');
    Route::post('/edit-cms-page/{id}', 'CmsPagesController@editPage');
    Route::get('/edit-cms-page/{id}', 'CmsPagesController@editPage');
    Route::post('/uploadCMSBannerImage', 'CmsPagesController@uploadCMSBannerImage');
    Route::post('/addUpdateCMSPage', 'CmsPagesController@addUpdateCMSPage');    
    Route::get('/sub-admin/{layout}/{theme}', 'SubAdminController@index');
    Route::post('/sub-admin/{layout}/{theme}', 'SubAdminController@index');
    Route::post('/add-update-sub-admin', 'SubAdminController@addUpdateSubAdmin');
    Route::get('/delete-sub-admin/{id}', 'SubAdminController@deleteSubAdmin');
    Route::get('/change-status-sub-admin/{id}/{status}', 'SubAdminController@changeSubAdminStatus');
    Route::post('/uploadSubAdminThumbImage', 'SubAdminController@uploadSubAdminThumbImage');
    Route::get('/setpermissions', 'SubAdminController@setpermissions');
    Route::get('/live-news/{layout}/{theme}', 'LiveNewsController@index');
    Route::post('/live-news/{layout}/{theme}', 'LiveNewsController@index');
    Route::post('/add-update-live-news', 'LiveNewsController@addUpdateNews');
    Route::post('/upload-logo', 'LiveNewsController@uploadLogo');
    Route::get('/delete-live-news/{id}', 'LiveNewsController@deleteNews');
    Route::get('/change-status-live-news/{id}/{status}', 'LiveNewsController@changeNewsStatus');
    Route::get('/e-paper/{layout}/{theme}', 'EpaperController@index');
    Route::post('/e-paper/{layout}/{theme}', 'EpaperController@index');
    Route::post('/add-update-e-paper', 'EpaperController@addUpdateEpaper');
    Route::post('/upload-logo-e-paper', 'EpaperController@uploadLogo');
    Route::get('/delete-e-paper/{id}', 'EpaperController@deleteEpaper');
    Route::get('/change-status-e-paper/{id}/{status}', 'EpaperController@changeEpaperStatus');
    Route::post('/uploadPdf', 'EpaperController@uploadPdf');
    Route::get('/news-api-post/{layout}/{theme}', 'NewsApiController@index');
    Route::post('/news-api-post/{layout}/{theme}', 'NewsApiControllers@index');
    Route::get('/save-news-api-post', 'NewsApiController@saveNewsApiPost');

    // translation routes
    Route::post('/get-category-translation/', 'CategoryTranslationController@show');
    Route::post('/translate-category', 'CategoryTranslationController@store');
    Route::post('/get-live-news-translation/', 'LiveNewsTranslationController@show');
    Route::post('/translate-live-news', 'LiveNewsTranslationController@store');
    Route::post('/get-e-paper-translation/', 'EpaperTranslationController@show');
    Route::post('/translate-e-paper', 'EpaperTranslationController@store');
    Route::get('/edit-cms-page-translation/{id}', 'CmsPagesTranslationController@index');
    Route::post('/get-cms-page-translation/', 'CmsPagesTranslationController@show');
    Route::post('/translate-cms-page', 'CmsPagesTranslationController@store');
    Route::get('/edit-blog-translation/{id}', 'BlogTranslationController@index');
    Route::post('/get-blog-translation/', 'BlogTranslationController@show');
    Route::post('/translate-blog/', 'BlogTranslationController@store');
    Route::get('/send-notification/{layout}/{theme}', 'SendPushNotifictionController@index');
    Route::post('/send-notification', 'SendPushNotifictionController@sendNotification');
    Route::get('/send-blog-notification/{blog_id}/', 'BlogController@sendBlogNotification');
    Route::get('/quotes/{layout}/{theme}', 'QuoteController@index');
    Route::post('/quotes/{layout}/{theme}', 'QuoteController@index');
    Route::post('/add-update-quote', 'QuoteController@store');
    Route::get('/change-status-quote/{id}/{status}', 'QuoteController@changequoteStatus');
    Route::get('/delete-quote/{id}', 'QuoteController@deletequote');

});


/***************** Site routes *****************/
Route::get('user-login', 'SiteAuthController@loginView')->name('login-view');
Route::post('user-login', 'SiteAuthController@login')->name('login');
Route::get('user-signup', 'SiteAuthController@signupView')->name('signup-view');
Route::post('user-signup', 'SiteAuthController@signup')->name('signup');
Route::get('forget-password', 'SiteAuthController@forgetView')->name('forget-password-view');
Route::post('forget-password', 'SiteAuthController@forget_password')->name('forget-password');
Route::post('reset-password', 'SiteAuthController@reset_password')->name('reset-password');
Route::get('/edit-profile', 'EditProfileController@index');
Route::post('edit-profile', 'EditProfileController@edit_profile')->name('edit-profile');
Route::get('/saved-stories', 'EditProfileController@saved_stories');
Route::get('/delete-story/{id}', 'EditProfileController@deleteStory');
Route::post('bookmark', 'EditProfileController@bookmark')->name('bookmark');
Route::post('upload-profile-image', 'EditProfileController@uploadProfileImage');
Route::get('delete-account', 'EditProfileController@deleteAccount');
Route::get('logout', 'SiteAuthController@logout')->name('logout');
/*************************Site routes end***************** */

/*
CMS Pages
*/
Route::get('/{page_name}', 'SiteController@cms'); // All CMS Pages dynamic
Route::post('/set-language/', 'SiteController@setLangugae');


Route::get('/{category}/{blog_id}', 'SiteController@blog_detail');
 Route::post('load-blog','SiteController@loadDataAjax' );