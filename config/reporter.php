<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 17:31
 * description:描述
 */

return [
    'asyn_mode' =>  env('reporter.asyn_mode',false),
//    'messager'=>[
//        'type'=> env('reporter.type','Ding'),
//        "groups" => ['default' => ['enabled'  => true, 'token'    => env('reporter.messager_default_token','cb3e7d7e0471f87aa853737135d850994d60013e5442460ac57e31635d9d431f'),
//                                   'timeout'  => 2.0, 'ssl_verify' => false,
//                          'keywords' => env('reporter.messager_default_keywords','勤鸟小伙伴,你好!'),],
//        ],
//    ],
    'Host'=> env('log.loghost','127.0.0.1'),
    'Port'=> env('log.logport','9601'),
];
