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
    private $isAsyn = false;

    private function __construct(array $config) {

        $this->config = $config['messager']['groups'];
        $this->isAsyn = $config['asyn_mode'] ?? false;
        $messager = 'app\common\tool\messager\\' . $config['messager']['type'];
        $this->messager = new $messager($this->config);
    }

//    /**
//     * 获取认证实例
//     * @param $busId
//     * @return DeviceGateRule
//     * @throws \Exception
//     */
//    public static function getInstance(array $config) {
//        if (empty(self::$instance)) {
//            self::$instance = new self($config);
//        }
//        return self::$instance;
//    }


    public function Report(string $message, $destination = 'default') {
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
            $res = $this->messager->handle(json_encode($message), $destination);
            $res = json_decode($res, true);
        }
        if (!$res || $res['errcode']) {
            throw new \Exception('发送Ding消息失败' . $res['errmsg'] ?? '');
        }
    }


    private function buildMessage($message, $destination) {
        $keywords = $this->config[$destination]['keywords'];
        $message = $keywords . "\n" . $message . "\n";
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
            return true;
        }
        return false;
    }

//    private function __clone() {
//    }
}
