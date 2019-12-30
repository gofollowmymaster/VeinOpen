<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/7
 * Time: 15:38
 */
namespace app\common\repository;

 use app\common\exception\WarringException;
 use app\common\tool\RedisClient;
 use think\exception\DbException;

 class RedisDriver {
    protected $redis;
    public function __construct($config=null) {
        $redis= RedisClient::getInstance($config);
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
     protected function buildRedisKey() {
         $params = func_get_args();
         $subject = $params[0];
         unset($params[0]);
         $params = array_values($params);
         $pattern = array_fill(0, count($params), '/<<[\w]+>>/');
         $result = preg_replace($pattern, $params, $subject, 1);
         if (!$result || strpos('>>', $result)) {
             throw new WarringException('redis键正则替换异常!' . $subject);
         }
         return $result;
     }

}
