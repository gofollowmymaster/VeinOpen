<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 15:30
 * description:描述
 */

return [
    'master'   => [
        ['host' =>  env('redis.master_hostname','127.0.0.1'), 'auth' =>  env('redis.master_auth','secret'), 'port' =>  env('redis.master_hostport',6379),],//测试服内网ip
    ],
    'slave' => [
        [
            env('redis.slave_hostname','127.0.0.1'), 'auth' =>  env('redis.slave_auth','secret'), 'port' =>  env('redis.slave_hostport',6379),],//测试服内网ip
    ],
];
