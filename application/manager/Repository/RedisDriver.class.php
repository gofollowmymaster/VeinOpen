<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/11/7
 * Time: 15:38
 */
namespace Common\Repository;

 use Common\Exception\DbException;
 use Common\Exception\WarringException;
 use Common\Tools\RedisClient;

 class RedisDriver {
    protected $redis;
    public function __construct($config=null) {
        $redis=New RedisClient($config);
        $this->redis=$redis->getRedis();
    }

    public function __call($name, $arguments) {
        if(method_exists($this->redis, $name)){
            return call_user_func_array(array($this->redis,$name), $arguments);
        }
        throw new WarringException('不存在的方法'.$name);
    }

    protected function pipeLine(\Closure $func){
        $pipe = $this->redis->multi(\Redis::PIPELINE);
        $func($pipe);
        $result= $pipe->exec();
        if(in_array(false,$result,true)){
            throw new DbException('更新租柜redis状态失败:'.json_encode($result));
        }
        return $result;
    }

}
