<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/11/4
 * Time: 10:05
 */

namespace app\common\tool;

use think\facade\Log;

class Reporter {
    const MESSAGE_LIST = 'reporter:send:notice:message';
    const LIST_VOLUME  = 1000;

    private static $instance;

    private $config;
    private $messenger;
    private $redis;
    private $isAsyn = false;


    private function __construct(array $config) {

        $this->config = $config['messenger']['groups'];
        $this->isAsyn = $config['asyn_mode'] ?? false;
        $messenger = 'app\common\tool\messenger\\' . $config['messenger']['name'];
        $this->messenger = new $messenger($this->config);
    }

    /**
     * 获取认证实例
     * @param $busId
     * @return DeviceGateRule
     * @throws \Exception
     */
    public static function getInstance(array $config) {
        if (empty(self::$instance)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }


    public function sendText(string $message, $destination = 'default') {
        $message = self::buildMessage($message, $destination);
        try {
            $destination = is_array($destination) ?: [$destination];
            foreach ($destination as $key => $value) {
                $this->send($message, $value);
            }
        } catch (\Throwable $e) {
            Log::error('发送Ding消息失败:' . $e->getMessage());
        }
    }

    private function send(string $message, $destination) {
        $message = ["msgtype" => "text", "text" => ["content" => $message],
                    "at"      => ["atMobiles" => [], "isAtAll" => false]];
        if ($this->isAsyn && !$this->isPassListVolume(self::MESSAGE_LIST)) {
            $content = ['token' => $this->config[$destination]['token'], 'content' => $message];
            $res = $this->getRedis()->lPush(self::MESSAGE_LIST, json_encode($content));
        } else {
            $res = $this->messenger->handle(json_encode($message), $destination);
            $res = json_decode($res, true);
        }
        if (!$res || $res['errcode']) {
            throw new \Exception('发送Ding消息失败' . $res['errmsg'] ?? '');
        }
    }

    //    /**
    //     * 发送钉钉连接消息
    //     * @param        $message
    //     * @param string $destination
    //     */
    //    function dingLink(array $message, $destination = 'default') {
    //        try {
    //            $message['text'] = self::buildMessage($message['text'], $destination);
    //            if (is_array($destination) && count($destination) > 0) {
    //                foreach ($destination as $key => $value) {
    //                    $this->sendLink($destination, $message);
    //                }
    //            } else {
    //                $this->sendLink($destination, $message);
    //            }
    //        } catch (\Throwable $e) {
    //            Log::error('发送Ding消息失败:' . $e->getMessage());
    //        }
    //    }
    //
    //    private function sendLink(string $destination, array $message) {
    //        $this->messenger->with($destination)
    //                        ->link($message['title'], $message['text'], $message['messageUrl'], $message['picUrl']);
    //    }

    private function buildMessage($message, $destination) {
        $keywords = $this->config[$destination]['keywords'];
        $message = $keywords . "\n\n" . $message . "\n";
        $message .= "\n请求时间:" . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $message .= "\n请求URI:" . $_SERVER['REQUEST_URI'];
        //        $message .= "\n请求信息:\n" . request();
        Log::error($message);
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
            //            $keywords = $this->config['default']['keywords'];
            //            $message = $keywords . "\nredis队列容量大于" . self::LIST_VOLUME;
            //            $this->messenger->with('default')->text($message);
            return true;
        }
        return false;
    }

    private function __clone() {
    }
}
