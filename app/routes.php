<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

Route::get('/', function() {
//    $view = Option::get('theme');
//    if (in_array($view, helper::themes())) {
//        return View::make("$view.index", array(
//                    'articles' => Article::status(Article::S_PUBLISHED)
//                            ->withCate()
//                            ->orderby('published', 'desc')
//                            ->paginate(20)));
//    }
//    return "主题文件{$view}丢失";
     return View::make('hello');
});
Route::get('/page/{page}', function($page) {
    return Redirect::to("?page=$page");
})->where('page', '[0-9]+');

//文章列表
Route::get('/{id}', 'PostController@byId')->where('id', '[0-9]+');
Route::get('tag/{tag}', 'PostController@byTag');
Route::get('author/{uid}', 'PostController@byAuthor')->where('uid', '[0-9]+');
Route::get('category/{slug}', 'PostController@byCategory')->where('slug', '[a-z]+');

Route::get('search/{keyword}', 'PostController@byKeyword');
Route::post('search', function() {
    $keyword = Input::get('keyword');
    return Redirect::to('search/' . $keyword);
});

Route::get('archives', 'PostController@byArchives');

Route::get('login', function() {
    return Redirect::to('manage/login.html');
})->before('guest');

Route::get('edit/{id}', function($id) {
    return Redirect::to('manage/index.html#article/edit/' . $id);
})->before('manage_article');

//前台api
Route::resource('api/comment', 'ManageComment');


//后台路由
Route::get('m', function() {
    return Redirect::to('manage');
});



//后台入口
Route::get('ipa/checkAuth', 'ManageEntry@checkAuth');
Route::post('ipa/login', 'ManageEntry@login');
Route::get('ipa/logout', 'ManageEntry@logout');

//后台功能
Route::get('ipa/initrole', 'ManageController@initRole');
Route::get('ipa/navs', 'ManageController@navs');
Route::get('ipa/theme', 'ManageController@themes');
Route::get('ipa/template', 'ManageController@templates');
Route::get('ipa/cache/flush', 'ManageController@cacheFlush');

//用户管理
Route::resource('ipa/user', 'ManageUser');
Route::resource('ipa/role', 'ManageRole');
Route::resource('ipa/article', 'ManageArticle');
Route::resource('ipa/category', 'ManageCategory');
Route::resource('ipa/comment', 'ManageComment');
Route::resource('ipa/application', 'ManageApplication');

Route::resource('ipa/attachment', 'ManageAttachment');
Route::resource('ipa/option', 'ManageOption');


Route::controller('ipa/stat', 'ManageStatistic');

//插件列表,包含长微博,Ucenter设置
Route::controller('ipa/plugin', 'ManagePlugin');

//插件
Route::resource('ipa/ad', 'ManageAd');
Route::resource('ipa/appkey', 'ManageAppkey');
Route::controller('ipa/sync', 'ManageSync');

//长微博
Route::get('changweibo/{id}', function($id) {
    $c = new changweibo($id);

    if ($filename = $c->init()) {
        return Response::make(readfile($filename))->header('Content-type', 'image/png');
    } else {
        return $c->fail();
    }
});
//二维码
Route::get('qrimage', function() {
    if ($url = Input::get('url')) {
        $size = Input::get('size', 79);
        return Response::make(QRcode::png(rawurldecode($url), FALSE, QR_ECLEVEL_M, $size / 27, 1))
                        ->header('Content-type', 'image/png');
    }
});

//rss
Route::get('feed', 'FeedController@show');

//解包apk
Route::controller('ipa/unpack', 'ManageUnpack');

Route::get('/login', 'HomeController@login');
Route::get('/loginwb', 'HomeController@loginWithWeibo');

Route::get('/{slug}', 'PostController@bySlug')->where('slug', '[a-z]+');
