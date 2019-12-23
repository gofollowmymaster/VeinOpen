<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2019/12/23
 * Time: 10:37
 * description:描述
 */

class Log {
    private $consumerConfig;
    private $topicConsumersMap;
    private $consumers;
    private $redis;

    public function __construct() {
        $config = Config::get('logCenter.');
        $this->topicConsumersMap = $config['topicConsumersMap'];
        $this->consumerConfig = $config['consumers'];
    }

    public function index($server,$fd,$data) {
        $this->initConsumers($this->topicConsumersMap[$data['topic']]);
        $this->redis = RedisPool::getInstance()->get();
        $this->messageHandles($data);
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
}
