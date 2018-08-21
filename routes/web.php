<?php

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

//Route::get('/', function () {
//    return view('welcome');
//});

//静态页面路由
Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');

//用户注册路由
Route::get('/signup', 'UsersController@create')->name('signup');

//用户资源路由
Route::resource('users', 'UsersController');

//用户登录路由
Route::get('login', 'SessionsController@create')->name('login');
Route::post('login', 'SessionsController@store')->name('login');
Route::delete('logout', 'SessionsController@destroy')->name('logout');

//用户状态激活路由，通过email进行激活
Route::get('signup/confirm/{token}', 'UsersController@confirmEmail')->name('confirm_email');

/**
 * 用户密码重置路由，通过email确认进行重置
 */
//显示重置密码的邮箱发送页面
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
//邮箱发送重设链接
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
//密码更新页面
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
//执行密码更新操作
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

//微博发布和删除的资源路由,only指定之生成story和destroy两个
Route::resource('statuses', 'StatusesController', ['only' => ['store', 'destroy']]);

//显示关注列表
Route::get('/users/{user}/followings', 'UsersController@followings')->name('users.followings');
//粉丝列表
Route::get('/users/{user}/followers', 'UsersController@followers')->name('users.followers');

//关注用户
Route::post('users/followers/{user}', 'FollowersController@store')->name('followers.store');
//取消关注
Route::delete('users/followers/{user}', 'FollowersController@destroy')->name('followers.destroy');