<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2019/12/23
 * Time: 10:37
 * description:描述
 */

namespace app\command\logCenter\controller;

use app\command\logCenter\redis\RedisPool;
use app\common\exception\ConfigException;
use app\common\exception\WarringException;
use think\facade\Config;

class LogController {
    const MESSAGE_QUEUE = 'logCenter:queue:';
    private $consumerConfig;
    private $topicConsumersMap;
    private $consumers;
    private $redis;


    //todo  task中做路由分发工作 添加controller模块
    public function __construct() {
        $config = Config::get('logCenter.');
        $this->topicConsumersMap = $config['topicConsumersMap'];
        $this->consumerConfig = $config['consumers'];
    }

    public function consumeFromRedis(array $params) {

        if (!key_exists('topic', $params)) {
            throw new ConfigException('请求参数不存在topic');
        }
        $topic = $params['topic'];
        if (!key_exists($topic, $this->topicConsumersMap)) {
            throw new ConfigException('不存在的topic:' . $topic);
        }
        $this->initConsumers($this->topicConsumersMap[$topic]);
        $this->redis = RedisPool::getInstance()->get();

        $topic = self::MESSAGE_QUEUE . $topic;
        $num=0;
        while (true) {
            $message = $this->redis->rPop($topic);
            if (!$message||$num>100) {
                break;
            }
            $num++;
            $this->messageHandles($message);
        }
        return ;
    }

    /**
     * 初始化消费者
     * @param array $consumersMap
     * @throws ConfigException
     */
    private function initConsumers(array $consumersMap) {
        foreach ($consumersMap as $consumer) {
            $consumerName = 'app\command\logCenter\consumer\\' . ucfirst($consumer);
            if (!class_exists($consumerName)) {
                throw new ConfigException('配置异常,消费者' . $consumerName . '不存在');
            }
            $this->consumers[] = new $consumerName($this->consumerConfig[$consumer]);
        }
    }

    public function consumeFromRequest($params) {
        $topic = $params['topic'];
        $message=$params['message'];
        if (!key_exists($topic, $this->topicConsumersMap)) {
            throw new ConfigException('不存在的topic:' . $topic);
        }
        $this->initConsumers($this->topicConsumersMap[$topic]);
        $this->messageHandles($message);
        return ;
    }

    /**
     *
     * @param $message
     * @return mixed|void
     */
    private function messageHandles($message) {

        $message = json_decode($message, true);
        //            echo "\n".'message:'.$message."\n";
        return $this->messageFilter($message, function ($message) {
            $result = [];
            foreach ($this->consumers as $consumer) {
                output(' 消费信息:' . json_encode($message));
                $result[] = $res = $consumer->handle($message);
                if ($res === false) {
                    throw new WarringException(" 消费信息失败,结果:" . $res);
                }
            }
            return $result;
        });

    }

    protected function messageFilter(array $message, \Closure $func) {

        return $func($message);
    }


    function __destruct() {
        // TODO: Implement __destruct() method.
        unset($this->consumers);
        if ($this->redis)
            RedisPool::getInstance()->pushRedis($this->redis);
    }

}
