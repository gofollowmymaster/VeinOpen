<?php

namespace app\command\logCenter\redis;

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
            throw new \RuntimeException('redis auth fail');
        }
        $this->config=$config;
        $this->redis=$redis;
    }

    public function __call($name, $arguments) {
        // TODO: Implement __call() method.
        $result=  call_user_func_array([$this->redis, $name], $arguments);
        if (false === $result) {
            serialize($this->redis);
//            if (!$this->redis->connected) { //断线重连
//                $this->redis = $this->connect($this->config);
////                Log::info('mysql reconnect', $res);
//                $result = call_user_func_array([$this->redis, $name], $arguments);
//            }

        }
        return $result;
    }


}
