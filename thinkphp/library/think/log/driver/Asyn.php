<?php

namespace think\log\driver;

use think\App;
use think\facade\Log;
use think\facade\Request;

class Asyn {

    const MESSAGE_QUEUE = 'logCenter:queue:';
    const QUEUE_VOLUME  = 1000;


    private $config =  [
        'messageQueue'   => 'redis',
        'project' => 'default',
    ];
    private $redis;



    public function __construct(App $app, array $config = []) {
        $this->app = $app;

        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }


    public function save(array $log, $append = false) {

//        $topic=$this->getLogTopic(array_keys($log));
        $topic=$this->config['project'];
        if ($this->app->isDebug() && $append) {
            $this->getDebugLog($log);
        }

        $requestInfo = ['ip'   => $this->app['request']->ip(),
                        'method' => $this->app['request']->method(),
                        'host' => $this->app['request']->host(),
                        'uri' => $this->app['request']->url(),
                        'param' => $this->app['request']->param(),
        ];
        $log = $requestInfo + $log;

        $log['project']=$topic;
        $log['serverIp']=gethostbyname(gethostname());
        $log['time']=time();
        $log['logId']=md5(uniqid($topic,true));

        $message = json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this->send($message,$topic);

    }

    private function send(string $message, $destination) {

        $messageQueue=self::MESSAGE_QUEUE.$destination;
        if ( $this->config['messageQueue'] && !$this->isPassListVolume($messageQueue)) {
            $res = $this->getRedis()->lPush($messageQueue, $message);
        } else {
            $message = ["msgtype" => "text", "text" => ["content" => $message],
                        "at"      => ["atMobiles" => [], "isAtAll" => false]];
            $content = ['token' => $this->config[$destination]['token'], 'content' => $message];

            $res = $this->messager->handle(json_encode($content), $destination);
            $res = json_decode($res, true);
        }
        if (!$res || $res['errcode']) {
            throw new \Exception('发送Ding消息失败' . $res['errmsg'] ?? '');
        }
        return $res;
    }

    private function getLogTopic(array $topic) {

    }


    private function getRedis() {
        if ($this->redis) {
            return $this->redis;
        }
        $config['master'][0]=$this->config['server'];
        if(empty($config)){
          //todo 日志服务中出现异常需要记录本地强制日志
            $config=[];
        }
        return $this->redis = redis($config);
    }

    private function isPassListVolume($key) {
        if ($this->getRedis()->lLen($key) > self::QUEUE_VOLUME) {
            return true;
        }
        return false;
    }

    protected function getDebugLog(&$info) {

        // 获取基本信息
        $runtime = round(microtime(true) - $this->app->getBeginTime(), 10);
        $reqs = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';

        $memory_use = number_format((memory_get_usage() - $this->app->getBeginMem()) / 1024, 2);

        $info = ['runtime' => number_format($runtime, 6) . 's', 'reqs' => $reqs . 'req/s',
                 'memory'  => $memory_use . 'kb', 'file' => count(get_included_files()),] + $info;

    }


    //    private function __clone() {
    //    }

}
