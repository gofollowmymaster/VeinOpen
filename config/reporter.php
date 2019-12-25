<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 17:31
 * description:描述
 */

return [
    'type' =>  env('reporter.type','http'),
    'keywords' => env('reporter.messager_default_keywords','勤鸟小伙伴,你好!'),
    'http'=>[
        'messagerType'=> env('reporter.messager_type','Ding'),
        "groups" => ['default' => ['enabled'  => true, 'token'    => env('reporter.messager_default_token','cb3e7d7e0471f87aa853737135d850994d60013e5442460ac57e31635d9d431f'),
                                   'timeout'  => 2.0, 'ssl_verify' => false,],
        ],
    ],
    'rpc'=>[
        'Host'=> env('log.loghost','127.0.0.1'),
        'Port'=> env('log.logport','9601'),
    ],
    'queue'=>[
        'host' =>  env('redis.master_hostname','127.0.0.1'),
        'auth' =>  env('redis.master_auth','secret'),
        'port' =>  env('redis.master_hostport',6379),
        ],//测试服内网ip

];
