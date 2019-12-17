<?php

return [
    // 数据库调试模式
    'debug'    => env('database.debug',false),
    // 数据库类型
    'type'     => env('database.type','mysql'),
    // 服务器地址
    'hostname' => env('database.hostname','127.0.0.1'),
    // 数据库名
    'database' =>  env('database.database','devicemanager'),
    // 用户名
    'username' =>  env('database.username','root1'),
    // 密码
    'password' => env('database.password','root'),
    // 端口
    'hostport' =>env('database.hostport','3306'),
];
