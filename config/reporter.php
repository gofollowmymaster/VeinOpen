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
    'swoole_conf'=>[
        "worker_num"=> env('reporter.swoole_worker_num',1),
        "task_worker_num"=> env('reporter.swoole_task_worker_num',1),
        "max_request"=>100,"task_max_request"=>100,
        "daemonize"=>1, 'log_file'=>env('reporter.swoole_log','mnt/'),
    ],
    'messenger'=>[
        'type'=> env('reporter.type','Ding'),
        "groups" => [
            'default' => ['enabled'  => true, 'token'    => env('reporter.messenger_default_token','cb3e7d7e0471f87aa853737135d850994d60013e5442460ac57e31635d9d431f'),
                          'timeout'  => 2.0, 'ssl_verify' => false,
                          'keywords' => env('reporter.messenger_default_keywords','勤鸟小伙伴,你好!'),],

        ],]
];
