<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 17:09
 * description:描述
 */
use think\facade\Route;


Route::group('manager', function () {
    Route::post('login', 'Login/index');
    Route::post('out', 'Login/out');
    Route::post('index/pass', 'index/pass');
    Route::get('index/info/:id', 'index/info');
})->prefix('manager/')->ext('html')->pattern(['id' => '\d+']);

