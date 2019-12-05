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
        ['host' => '127.0.0.1', 'auth' => 'secret', 'port' => 6379,],//测试服内网ip
    ],
    'slave' => [
        [
            'host' => '127.0.0.1', 'auth' => 'secret', 'port' => 6379,
        ],
    ],
];
