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
Route::post('register', 'UserAPIController@register');
Route::post('forgot-password', 'UserAPIController@forgetPassword');
Route::post('reset-password', 'UserAPIController@resetPassword');
Route::post('socialMediaLogin', 'UserAPIController@socialMediaLogin');
Route::get('blog-category-list', 'CategoryAPIController@list');
Route::get('blog-list', 'BlogAPIController@list');
Route::get('blog-details/{id}', 'BlogAPIController@detail');
Route::get('setting-list', 'BlogAPIController@settingList');
Route::get('blog-all-list', 'BlogAPIController@allBloglist');
Route::post('get-single-blog-data', 'BlogAPIController@getSingleData');
Route::post('getProfile', 'UserAPIController@getProfile');
Route::post('updateProfile', 'UserAPIController@updateProfile');
Route::post('updateProfilePicture', 'UserAPIController@updateProfilePicture');
Route::post('searchBlog', 'BlogAPIController@searchBlog');
Route::post('updateToken', 'UserAPIController@updateToken');
Route::post('deleteAccount', 'UserAPIController@deleteAccount');
Route::post('bookmarkPost', 'BlogAPIController@bookmarkPost');
Route::post('deleteBookmarkPost', 'BlogAPIController@deleteBookmarkPost');
Route::post('AllBookmarkPost', 'BlogAPIController@AllBookmarkPost');
Route::post('increaseBlogViewCount', 'BlogAPIController@increaseBlogViewCount');
Route::post('addBlogVote', 'BlogAPIController@addBlogVote');
Route::post('blogSwipe', 'BlogAPIController@blogSwipe');
Route::post('getBlogVote', 'BlogAPIController@getBlogVote');
Route::post('nextPreviousBlog', 'BlogAPIController@nextPreviousBlog');
Route::get('e-news-list', 'API\EpaperAPIController@list');
Route::get('live-news-list', 'API\LiveNewsAPIController@list');
Route::get('language/lists', 'API\LanguagesAPIController@lists');
Route::get('keys/lists', 'API\LanguagesAPIController@keysLists');