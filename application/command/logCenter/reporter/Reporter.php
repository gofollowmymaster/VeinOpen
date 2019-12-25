<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/11/4
 * Time: 10:05
 */

namespace app\command\logCenter\reporter;

class Reporter {
    const MESSAGE_LIST = 'reporter:send:notice:message';
    const LIST_VOLUME  = 1000;


    private $config;
    private $messager;
    private $redis;
    private $isAsyn = false;

    public function __construct(array $config) {

        $this->config = $config['groups'];
        $this->isAsyn = $config['asyn_mode'] ?? false;
        $messager = 'app\command\logCenter\reporter\messager\\' . $config['type'];
        $this->messager = new $messager($this->config);
    }


    public function Report(string $message, $destination = 'default') {
        $message = self::buildMessage($message, $destination);
        try {
            $destination = is_array($destination) ?: [$destination];
            foreach ($destination as $key => $value) {
                $this->send($message, $value);
            }
        } catch (\Throwable $e) {
            output ('发送Ding消息失败:' . $e->getMessage());
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
            output ('发送Ding消息失败' . $res['errmsg'] ?? '');
        }
    }


    private function buildMessage($message, $destination) {
        $keywords = $this->config[$destination]['keywords'];
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
