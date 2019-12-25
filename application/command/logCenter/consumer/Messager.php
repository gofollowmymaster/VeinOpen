<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/21
 * Time: 9:49
 * description:描述
 */
namespace app\command\logCenter\consumer;

use app\command\logCenter\redis\RedisPool;
use app\common\exception\ConfigException;

class Messager implements Consumer{
    private $config;
    private $driver;
    private $redis;
    private $keyFilters=['error'];
    public function __construct(array $config){
        $this->config=$config;
        $driverName='app\command\logCenter\consumer\messager\\'.$config['type'];
        if(!class_exists($driverName)){
            throw new ConfigException( '配置异常,消息驱动' . $driverName . '不存在');
        }
        $this->keyFilters=array_merge( $this->keyFilters,$config['filter']['level']);
        $this->driver=new $driverName($config['groups']);
        $this->redis = RedisPool::getInstance()->get();
    }
    public function handle($message) {
        // TODO: Implement handle() method.
        return $this->messageFilter($message,function ($message){
            echo 'message send result:'.json_encode($this->driver->handle(json_encode($message),'default'))."\n";
        });

    }

    private function messageFilter(array $message,\Closure $func){
        $featureCode = md5((key_exists('uri', $message) ? $message['uri'] : 'uri') . (key_exists('param', $message) ?
                json_encode($message['param']) : 'param').(key_exists('response', $message) ? $message['response'] : 'response'));
        if ($this->redis->exists('logCenter:filter:'.$featureCode)) {
            output("重复消息,已被消费者过滤!" . json_encode($message));
            return;
        }
        $this->redis->set('logCenter:filter:'.$featureCode, json_encode($message), $this->config['filter']['repeat']);

        if(!array_intersect(array_keys($message),$this->keyFilters)){
            echo '该消息级别,被消费者过滤:'.json_encode($message)."\n";
            return ;
        }

        return $func($message);
    }
    public function __destruct() {
        // TODO: Implement __destruct() method.
        if ($this->redis)
            RedisPool::getInstance()->pushRedis($this->redis);
    }
}
