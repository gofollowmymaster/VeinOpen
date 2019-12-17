<?php


return [
    'type'       => 'redis',
    'prefix'     => 'qnopen',
    'auto_start' => true,
    // redis主机
    'host'       => env('redis.master_hostname','127.0.0.1'),
    // redis端口
    'port'       => env('redis.master_hostport','6379'),
    // 密码
    'password'   =>  env('redis.master_auth',''),
];
