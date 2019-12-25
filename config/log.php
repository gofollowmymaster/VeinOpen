<?php


return [
    'type'        =>  env('log.type','file'),
    'messageQueue'   =>  env('log.message_queue','redis'),
    'QueueServer' => [
                 'host' => env('redis.master_hostname', '127.0.0.1'),
                 'port' => env('redis.master_port', '6379'),
                 'auth' => env('redis.master_auth', 'secret'),],
    'LogHost'=> env('log.loghost','127.0.0.1'),
    'LogPort'=> env('log.logport','9601'),
    'project' => 'veinopen',
    'apart_level' => ['error', 'sql'],
];
