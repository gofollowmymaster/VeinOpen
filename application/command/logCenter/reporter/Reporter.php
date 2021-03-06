<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/11/4
 * Time: 10:05
 */

namespace app\command\logCenter\reporter;

use app\common\traits\SingletonTrait;
use think\facade\Log;

class Reporter {
    use SingletonTrait;
    const MESSAGE_LIST = 'reporter:send:notice:message';
    const LIST_VOLUME  = 1000;

    private static $instance;

    private $config;
    private $messager;
    private $redis;
    private $handle;
    private $keywords;

    private function __construct(array $config) {

        $this->config = $config[$config['type']];
        $this->handle = $config['type'];
        $this->keywords = $config['keywords'];
        if ($config['type'] == 'http') {
            $messager = 'app\common\tool\messager\\' . $this->config['messagerType'];
            $this->messager = new $messager($this->config['groups']);
        }

    }
    private function messageFilter(string $message, \Closure $func) {

        $featureCode = md5($message);

        if ($this->getRedis()->exists('logCenter:reporter:filter:' . $featureCode)) {
            $this->getRedis()->incr('logCenter:reporter:filted');
            output("重复通知,已过滤!" . $message);
            return;
        }

        $this->getRedis()->set('logCenter:reporter:filter:'.$featureCode, true, 120);

        return $func($message);
    }

    public function Report(string $message, $destination = 'default') {

        try {
            $this->messageFilter($message,function ($message)use($destination){
                $message = self::buildMessage($message);
                $handle = $this->handle;
                $res = $this->$handle($message, $destination);
                if (!$res || $res['errcode']) {
                    throw new \Exception('发送Ding消息失败' . $res['errmsg'] ?? '');
                }
            });


        } catch (\Throwable $e) {
            logToFile('发送Ding消息失败:文件'.$e->getFile().';第'.$e->getLine().'行;错误信息'.$e->getMessage(). $e->getMessage(),'reporter');
        }
    }

    private function http($message, $destination = 'default') {

        $message = ["msgtype" => "text", "text" => ["content" => $message],
                    "at"      => ["atMobiles" => [], "isAtAll" => false]];
        $res = $this->messager->handle(json_encode($message), $destination);
        return json_decode($res, true);

    }

    private function rpc($message) {
        $message = ['controller' => "LogController", 'method' => "consumeFromRequest",
                    "params"     => ["topic" => 'veinopen', 'message' => $message]];
        return tcpPost(json_encode($message), $this->config['Host'], $this->config['Port']);

    }

    private function queue($message, $destination = 'default') {
        $message = self::buildMessage($message);
        $message = ["msgtype" => "text", "text" => ["content" => $message],
                    "at"      => ["atMobiles" => [], "isAtAll" => false]];
        if (!$this->isPassListVolume(self::MESSAGE_LIST)) {
            $content = ['token' => $this->config[$destination]['token'], 'content' => $message];
            $res = $this->getRedis()->lPush(self::MESSAGE_LIST, json_encode($content));
        }
        return $res ?? false;

    }


    private function buildMessage($message) {
        $keywords = $this->keywords;
        $message = $keywords . "\n" . $message . "\n";
        return $message;
    }

    private function getRedis() {
        if ($this->redis) {
            return $this->redis;
        }
        return $this->redis = redis();
    }

    private function isPassListVolume($key) {
        if ($this->getRedis()->lLen($key) > self::LIST_VOLUME) {
            return true;
        }
        return false;
    }

}
