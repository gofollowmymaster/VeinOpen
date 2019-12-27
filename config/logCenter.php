<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 17:31
 * description:描述
 */

return [
    'server' =>[
         'redis'=> [
             'pool_size'=>8,
             'pool_get_timeout'=>2,
             'host' => env('redis.master_hostname', '127.0.0.1'),
             'port' => env('redis.master_port', '6379'),
             'auth' => env('redis.master_auth', 'secret'),
             ],
         'swoole'=>[
             "worker_num"=> env('logcenter.swoole_worker_num',1),
             "task_worker_num"=> env('logcenter.swoole_task_worker_num',1),
             "max_request"=>100,"task_max_request"=>100,
             "daemonize"=>0, 'log_file'=>'/mnt/'.env('evironment','test').'/log/'.env('logcenter.swoole_log','swoole_log'),
             "package_eof"=>"|end|",
             ],
        ],
    'topicConsumersMap'=>[
        'veinopen'=>['messager',],
//        'error'=>['messager',],
        ],
    'consumers'=>[
        'messager'=>[
            'type'=> env('logcenter.message_type','Ding'),
            'filter' =>['repeat'=>env('logcenter.repeat'),
                        'level'=>explode(',',env('logcenter.level','error,emergency,critical,alert,warning'))],
            "groups" => [
                'default' => ['enabled'  => true, 'token'    => env('logcenter.messager_default_token','cb3e7d7e0471f87aa853737135d850994d60013e5442460ac57e31635d9d431f'),
                              'timeout'  => 2.0, 'ssl_verify' => false,
                              'keywords' => env('reporter.messager_default_keywords','勤鸟小伙伴,你好!'),],
            ],
        ],
//        'elasticsearch'=>[],
    ],
    'reporter'=>[
        'type' =>  env('logcenter.reporter','http'),
        'keywords' => env('logcenter.messager_default_keywords','勤鸟小伙伴,你好!'),
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

    ],
];
