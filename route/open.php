<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/12
 * Time: 17:09
 * description:描述
 */
use think\facade\Route;


Route::group('open', function () {

    //场馆
    Route::resource('space','space')->except(['create']);

})->prefix("open/")->pattern(['id' => '\d+']);

//miss

Route::miss('manager/index/miss');
