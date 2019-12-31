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
use app\common\exception\WarringException;

class Messager implements Consumer {
    private $config;
    private $driver;
    private $redis;
    private $keyFilters = ['error'];

    public function __construct(array $config) {
        $this->config = $config;
        $driverName = 'app\command\logCenter\consumer\messager\\' . $config['type'];
        if (!class_exists($driverName)) {
            throw new ConfigException('配置异常,消息驱动' . $driverName . '不存在');
        }
        $this->keyFilters = array_merge($this->keyFilters, $config['filter']['level']);
        $this->driver = new $driverName($config['groups']);
        $this->redis = RedisPool::getInstance()->get();
    }

    public function handle(array $message) {
        // TODO: Implement handle() method.

        return $this->messageFilter($message, function ($message) {
            $message = $this->messageFormater($message);
            output( 'message send result:' . json_encode($this->driver->handle($message, 'default')) );
        });

    }

    private function messageFilter(array $message, \Closure $func) {

        if (!array_intersect(array_keys($message), $this->keyFilters)) {
            output( '该消息级别,被消费者过滤:' . $message['logId']);
            return;
        }
        $feature=(key_exists('uri', $message) ? $message['uri'] : 'uri') . (key_exists('param', $message) ?
                json_encode($message['param']) : 'param') . (key_exists('ip', $message) ? $message['ip'] :
                'ip');
        $featureCode = md5($feature);

        if ($this->redis->exists('logCenter:messager:filter:' . $featureCode)) {
            $this->redis->incr('logCenter:messager:filted');
            output("重复消息,已被消费者过滤!" . $message['logId']);
            return;
        }

        $this->redis->set('logCenter:messager:filter:'.$featureCode, true, $this->config['filter']['repeat']);

        return $func($message);
    }

    protected function messageFormater(array $content) {
        $message = "请求时间:" . $content['time'];
        $message .= "\n请求URI:" . $content['uri'];
        $message .= "\n异常:" . (is_array($content['error'])?json_encode($content['error'],JSON_UNESCAPED_UNICODE):$content['error']);
        $message .= "\n请求IP:" . $content['ip'];
        $message .= "\n服务IP:" . $content['serverIp'];
        $message .= "\n主题:" . $content['project'];
        $message .= "\nID:" . $content['logId'];
        unset($content['time']);
        unset($content['uri']);
        unset($content['error']);
        unset($content['ip']);
        unset($content['serverIp']);
        unset($content['logId']);
        unset($content['project']);

        $message .= "\n请求信息:" . arrayToStr($content);
        return $message;
    }

    public function __destruct() {
        // TODO: Implement __destruct() method.
        if ($this->redis)
            RedisPool::getInstance()->pushRedis($this->redis);
    }
}
