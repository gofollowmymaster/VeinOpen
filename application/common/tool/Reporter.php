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


        $this->config = $config;
        $this->isAsyn = $config['asyn_mode'] ?? false;

    }



    public function Report(string $message) {
//        $message = self::buildMessage($message);
        try {
            $message=['controller'=>"LogController",'method'=>"consumeFromRequest","params"=>[
                "topic"=>'veinopen','message'=>$message
            ]];
                $this->send(json_encode($message));

        } catch (\Throwable $e) {
            Log::error('发送Ding消息失败:' . $e->getMessage());
        }
    }

    private function send(string $message) {

            $res=tcpPost($message, $this->config['Host'],  $this->config['Port']);
//            $res = $this->messager->handle(json_encode($message), $destination);
//            $res = json_decode($res, true);

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
