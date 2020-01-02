<?php

namespace app\command\logCenter\redis;

use app\common\exception\WarringException;

class RedisDB {
    private $redis;
    private $config;

    public function connect($config) {

        $redis = new \Redis;
        $redis->connect($config['host'], $config['port']);
        if (!$redis->connect($config['host'], $config['port'])) {
            throw new \RuntimeException('redis connect fail');
        }
        if (!$redis->auth($config['auth'])) {
            throw new \RuntimeException('redis auth fail'.json_encode($config));
        }
        $this->config=$config;
        $this->redis=$redis;
    }

    public function __call($name, $arguments) {
        // TODO: Implement __call() method.
        $result=  call_user_func_array([$this->redis, $name], $arguments);
        if (false === $result) {
            if (!$this->redis->Ping()) { //断线重连
                $this->redis = $this->connect($this->config);
                $result = call_user_func_array([$this->redis, $name], $arguments);
            }
        }
        return $result;
    }


}
