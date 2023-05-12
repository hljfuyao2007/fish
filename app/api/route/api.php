<?php

use think\facade\Route;

// 首页控制器
Route::group('index', function () {
	Route::any('index', 'index'); // 总首页(各模块入口)
	Route::any('test', 'test'); // test
})->prefix('index/');

Route::any('api/<m>/<a>', 'api.<m>/<a>');
Route::any('api/<m>', 'api.<m>/index');
Route::any('api', 'api.index/index');


