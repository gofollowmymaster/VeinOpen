<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/12
 * Time: 17:09
 * description:描述
 */
use think\facade\Route;


Route::group('firm', function () {
    //商家
    Route::resource('firm','firm');
    Route::get('firm/fathermenus', 'firm/fatherMenus');
    Route::get('firm/forbid/:id', 'firm/forbid');
    //场馆
    Route::resource('space','space')->except(['create']);
    Route::get('space/forbid/:id', 'space/forbid');
    Route::get('space/getAuthNode', 'space/getAuthNode');
    Route::get('space/saveAuthNode', 'space/saveAuthNode');

    //miss
    Route::miss('firm/miss');
})->prefix("firm/")->pattern(['id' => '\d+']);



