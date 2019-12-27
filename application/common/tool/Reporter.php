<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/11/4
 * Time: 10:05
 */

namespace app\common\tool;

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
        } else {
            $this->httpConfig = $config['http'];
        }
    }


    public function Report(array $message, $destination = 'default') {

        try {
            Log::error($message['error']);
            $message['logId'] = Log::getLog('logId');
            $handle = $this->handle;
            $res = $this->$handle($message, $destination);
            if (!$res || $res['errcode']) {
                throw new \Exception('发送Ding消息失败' . $res['errmsg'] ?? '');
            }
        } catch (\Throwable $e) {
            Log::error('发送Ding消息失败:' . $e->getMessage());
        }
    }

    private function http($message, $destination = 'default') {
        $message = self::buildMessage($message);
        $message = ["msgtype" => "text", "text" => ["content" => $message],
                    "at"      => ["atMobiles" => [], "isAtAll" => false]];
        $res = $this->messager->handle(json_encode($message), $destination);
        return json_decode($res, true);

    }

    private function rpc($message) {
        $message = ['controller' => "LogController", 'method' => "consumeFromRequest",
                    "params"     => ["topic" => 'veinopen', 'message' => $message]];
        $res = tcpPost(json_encode($message), $this->config['Host'], $this->config['Port']);
        $res = json_decode($res, true);
        return ['errcode' => $res['code'], 'errmsg' => $res['msg']];
    }

    private function queue($message, $destination = 'default') {
        $message = self::buildMessage($message);
        $message = ["msgtype" => "text", "text" => ["content" => $message],
                    "at"      => ["atMobiles" => [], "isAtAll" => false]];
        if (!$this->isPassListVolume(self::MESSAGE_LIST)) {
            $content = ['token' => $this->httpConfig['groups'][$destination]['token'], 'content' => $message];
            $res = $this->getRedis()->lPush(self::MESSAGE_LIST, json_encode($content));
        }
        return $res ?? false;

    }


    private function buildMessage($content) {
        $keywords = $this->keywords;
        $message = "请求时间:" . $content['time'];
        $message .= "\n请求URI:" . $content['uri'];
        $message .= "\n请求异常:" . $content['error'];
        $message .= "\n请求IP:" . $content['ip'];
        $message .= "\nID:" . $content['logId'];
        $message .= "\n请求信息:\n" . $content['info'];
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
