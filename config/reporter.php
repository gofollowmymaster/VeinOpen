<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 17:31
 * description:描述
 */

return [
    'asyn_mode' => false,
    'swoole_conf'=>[
        "worker_num"=>1,"task_worker_num"=>1,
        "max_request"=>100,"task_max_request"=>100,
        "daemonize"=>1, 'log_file'=>'/mnt/test/log/swoole_ding.log',
    ],
    'messenger'=>[
        'name'=>'Ding',
        "groups" => [
            'default' => ['enabled'  => true, 'token'    => 'cb3e7d7e0471f87aa853737135d850994d60013e5442460ac57e31635d9d431f',
                          'timeout'  => 2.0, 'ssl_verify' => false,
                          'keywords' => '勤鸟开发小伙伴,你好!',],

        ],]
];
