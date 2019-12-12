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
    Route::post('index/info', 'index/info');
    Route::get('index/menus', 'index/menus');
    //菜单
    Route::resource('menu','menu');
    Route::get('menu/fathermenus', 'menu/fatherMenus');
    Route::get('menu/forbid/:id', 'menu/forbid');
    //角色
    Route::resource('role','role')->except(['create']);
    Route::get('role/forbid/:id', 'role/forbid');
    Route::get('role/getAuthNode', 'role/getAuthNode');
    Route::get('role/saveAuthNode', 'role/saveAuthNode');
    //用户
    Route::resource('user','user');
    Route::get('user/forbid/:id', 'user/forbid');
    Route::post('user/pass/:id', 'user/pass');
    //节点
    Route::get('node/clear', 'node/clear');
    Route::get('node$', 'node/index');
    Route::get('node/autoAdd/[:group]', 'node/autoAdd');
    Route::get('node/clear/[:group]', 'node/clear');



    //miss
    Route::miss('index/miss');
})->prefix("manager/")->pattern(['id' => '\d+']);



