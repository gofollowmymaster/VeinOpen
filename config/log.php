<?php


return [
    'type'        => 'asyn',
    'messageQueue'   => 'redis',
    'server' => ['host' => env('redis.master_hostname', '127.0.0.1'),
                 'port' => env('redis.master_port', '6379'),
                 'auth' => env('redis.master_auth', 'secret'),],
    'project' => 'veinopen',
    'apart_level' => ['error', 'sql'],
];
