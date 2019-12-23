<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/17
 * Time: 18:17
 * description:描述
 */

namespace app\command\logCenter\callback;

use app\command\logCenter\redis\RedisPool;
use app\common\exception\ConfigException;
use think\facade\Config;

class OnTask {
    const MESSAGE_QUEUE = 'logCenter:queue:';
    private $consumerConfig;
    private $topicConsumersMap;
    private $consumers;
    private $redis;

    //todo  task中做路由分发工作 添加controller模块
    public function __construct() {
        $config = Config::get('logCenter.');
//        echo 'logCenter配置信息:'.json_encode($config);
        $this->topicConsumersMap = $config['topicConsumersMap'];
        $this->consumerConfig = $config['consumers'];
    }

    public function index($topic) {
        if (!isset($this->topicConsumersMap[$topic])) {
            throw new ConfigException('不存在的topic:' . $topic);
        }
        $this->initConsumers($this->topicConsumersMap[$topic]);
        $this->redis = RedisPool::getInstance()->get();
        $this->messageHandles($topic);
    }

    private function initConsumers(array $consumersMap) {
        foreach ($consumersMap as $consumer) {
            $consumerName = 'app\command\logCenter\consumer\\' . ucfirst($consumer);
            if (!class_exists($consumerName)) {
                throw new ConfigException('配置异常,消费者' . $consumerName . '不存在');
            }
            $this->consumers[] = new $consumerName($this->consumerConfig[$consumer]);
        }
    }

    private function messageHandles($topic) {
        $topic=self::MESSAGE_QUEUE.$topic;
        while (true) {
            $message = $this->redis->rPop($topic);
            if (!$message) {
                break;
            }
            $message = json_decode($message,true);
//            echo "\n".'message:'.$message."\n";
            $result = $this->messageFilter($message, function ($message) {
                $result = [];
                foreach ($this->consumers as $consumer) {
                    echo date('Y-m-d H:i:s', time()) . ' 消费信息:' . json_encode($message) . "\n";
                    $result[] = $res = $consumer->handle($message);
                    if ($res === false) {
                        echo date('Y-m-d H:i:s', time()) . " 消费信息结果:" . $res . "\n";
                    }
                }
                return $result;
            });

        }
    }

    protected function messageFilter(array $message, \Closure $func) {
//        echo array_pop($message)."\n";
        $featureCode = md5((key_exists('uri',$message) ? $message['uri'] :
            '/') . (key_exists('param',$message) ? json_encode($message['param']) : '?'));
        if ($this->redis->exists($featureCode)) {
            echo "重复消息,已被过滤!" . json_encode($message)."\n";
            return;
        }
        $this->redis->set($featureCode, json_encode($message), 300);
        return $func($message);
    }


    function __destruct() {
        // TODO: Implement __destruct() method.
        unset($this->consumers);
        if ($this->redis)
            RedisPool::getInstance()->pushRedis($this->redis);
    }
}
