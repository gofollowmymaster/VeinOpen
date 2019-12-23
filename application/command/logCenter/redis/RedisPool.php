<?php

namespace app\command\logCenter\redis;

class RedisPool {
    private static $instance;
    private        $pool = [];
    private        $config;

    public static function getInstance($config = null) {
        if (empty(self::$instance)) {
            if (empty($config)) {
                throw new \RuntimeException("Redis config empty");

            }
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    public function __construct($config) {
        if (empty($this->pool)) {
            $this->config = $config;
            for ($i = 0; $i < $config['pool_size']; $i++) {
                $redis = new RedisDB();
                $redis->connect($config);
                array_push($this->pool,$redis);
            }
        }
    }

    public function get() {
        if (count($this->pool) > 0) {

            $redis=$this->popRedis();
            if (false === $redis) {
                throw new \RuntimeException("Pop redis timeout");
            }
            return $redis;
        } else {
            throw new \RuntimeException("Pool length <= 0");
        }
    }
    private function  popRedis(){
        $time=time();
        $redis=false;
        while(time()-$time<$this->config['pool_get_timeout']){
            if( $redis = array_pop($this->pool)){
                break;
            }
        }
        return $redis;
    }
    public function pushRedis(RedisDB $redis){
        array_push($this->pool,$redis);
    }
}
