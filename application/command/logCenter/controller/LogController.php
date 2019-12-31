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
use app\command\logCenter\reporter\Reporter;
use think\facade\Config;

class LogController {
    const MESSAGE_QUEUE       = 'logCenter:queue:';
    const MESSAGE_DELAY_QUEUE = 'logCenter:zset:';
    private   $protocolKeys = ['logId', 'ip', 'uri', 'time', 'project', 'serverIp', 'param'];
    private   $consumerConfig;
    protected $reporterConfig;
    private   $topicConsumersMap;
    private   $consumers;
    private   $redis;


    //todo  task中做路由分发工作 添加controller模块
    public function __construct() {
        $config = Config::get('logCenter.');
        $this->topicConsumersMap = $config['topicConsumersMap'];
        $this->consumerConfig = $config['consumers'];
        $this->reporterConfig = $config['reporter'];

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
        $num = 0;
        while (true) {
            $message = $this->redis->rPop($topic);
            if (!$message || $num > 100) {
                break;
            }
            try {
                $num++;
                $this->messageHandles($message);
            } catch (\Throwable $e) {
                output('消息处理异常:文件' . $e->getFile() . ';第' . $e->getLine() . '行;错误信息' . $e->getMessage() . "内容:" . $message);
                $this->report('消息处理异常:' . $e->getMessage() . "\n内容:" . $message);
            }

        }
        return;
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

    public function consumeFromRequest(string $topic, array $message) {

        if (!key_exists($topic, $this->topicConsumersMap)) {
            throw new ConfigException('不存在的topic:' . $topic);
        }
        $this->initConsumers($this->topicConsumersMap[$topic]);
        $this->messageHandles(json_encode($message));
        return;
    }

    /**
     *
     * @param $message
     * @return mixed|void
     */
    private function messageHandles(string $message) {

        $message = json_decode($message, true);
        //todo  日志协议检查
        $this->logProtocolCheck($message);

        output('message处理:' . json_encode($message));
        return $this->messageFilter($message, function ($message) {
            $result = [];
            foreach ($this->consumers as $consumer) {
                output(' 消费信息:' . $message['logId']);
                $result[] = $res = $consumer->handle($message);
                if ($res === false) {
                    throw new WarringException(" 消费信息失败,结果:" . $res);
                }
            }
            return $result;
        });
    }

    //todo 协议没有检查参数类型
    protected function logProtocolCheck(array $message) {

        if ($lack = array_diff($this->protocolKeys, array_keys($message))) {
            throw new WarringException('不支持的日志协议:缺少关键key:' . implode(',', $lack));
        }
    }

    protected function messageFilter(array $message, \Closure $func) {

        return $func($message);
    }

    public function report(string $content) {
        try {
            reportLog($content, $this->reporterConfig);
        } catch (\Throwable $e) {
            output('report失败:文件' . $e->getFile() . ';第' . $e->getLine() . '行;错误信息' . $e->getMessage() . "内容:" . $content);
        }
    }

    function __destruct() {
        // TODO: Implement __destruct() method.
        unset($this->consumers);
        if ($this->redis)
            RedisPool::getInstance()->pushRedis($this->redis);
    }

}
