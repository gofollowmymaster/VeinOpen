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
             "tcpHost"=>env('logcenter.tcphost', '0.0.0.0'),'tcpPort'=>env('logcenter.tcpport', '9556'),
             "set"=>[
                 "worker_num"=> env('logcenter.swoole_worker_num',1),
                 "task_worker_num"=> env('logcenter.swoole_task_worker_num',1),
                 "max_request"=>100,"task_max_request"=>100,"max_connection"=>10000,
                 "daemonize"=>0, 'log_file'=>env('runtime_path').'log/'.env('evironment','test').'_'.env('logcenter.swoole_log','swoole_log'),
                 "package_eof"=>"|end|", "open_eof_check"=>true,'reload_async' => true,
             ],
             "masterProcessName"=>"LogCenter-master",
             "managerProcessName"=>"LogCenter-manager",
             "workerProcessName"=>"LogCenter-worker",
             "taskProcessName"=>"LogCenter-task",
             "masterPidFile"=>dirname(__DIR__).DIRECTORY_SEPARATOR.'master.pid',
             "managerPidFile"=>dirname(__DIR__).DIRECTORY_SEPARATOR.'manager.pid',
             "workerPidFile"=>dirname(__DIR__).DIRECTORY_SEPARATOR.'worker.pid',
             "taskPidFile"=>dirname(__DIR__).DIRECTORY_SEPARATOR.'task.pid',
             ],
        ],
    'topicConsumersMap'=>[
        'veinopen'=>['messager',],
//        'error'=>['messager',],
        ],
    'consumers'=>[
        'messager'=>[
            'type'=> env('logcenter.message_type','Ding'),
            'filter' =>['repeat'=>(int)env('logcenter.repeat',120),
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
