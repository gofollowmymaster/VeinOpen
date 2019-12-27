<?php

namespace think\log\driver;

use app\common\dataset\RequestLog;
use think\App;

class Asyn {

    const MESSAGE_QUEUE = 'logCenter:queue:';
    const QUEUE_VOLUME  = 0;


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

        $request = ['ip'   => $this->app['request']->ip(),
                        'method' => $this->app['request']->method(),
                        'host' => $this->app['request']->host(),
                        'uri' => $this->app['request']->url(),
                        'param' => $this->app['request']->param(),
        ];
        $log['extro'] =  RequestLog::getInstance()->response;
        $log = $request + $log;
        $log['project']=$topic;
        $log['serverIp']=gethostbyname(gethostname());
        $log['time']=date('Y-m-d H:i:s',time());

        return $this->send($log,$topic);

    }

    private function send(array $content, $destination) {

        $messageQueue=self::MESSAGE_QUEUE.$destination;
        if ( !$this->isPassListVolume($messageQueue)) {
            $res = $this->getRedis()->lPush($messageQueue, json_encode($content));
        } else {
            $message = ['controller' => "LogController", 'method' => "consumeFromRequest",
                        "params"     => ["topic" => 'veinopen', 'message' => $content]];
            $res=tcpPost(json_encode($message), $this->config['LogHost'],  $this->config['LogPort']);
            $res=json_decode($res,true);
        }
        if (!$res || $res['code']) {
            logToFile('发送Ding消息失败' . ($res['msg'] ?? '').'message='.json_encode($content));
            throw new \Exception('发送Ding消息失败' . $res['msg'] ??'' );
        }
        return $res;
    }


    private function getRedis() {
        if ($this->redis) {
            return $this->redis;
        }
        $config['master'][0]=$this->config['QueueServer'];
        if(empty($config)){
          //todo 日志服务中出现异常需要记录本地强制日志
            $config=[];
        }
        return $this->redis = redis($config);
    }

    private function isPassListVolume($key) {
        if ($this->getRedis()->lLen($key) >= self::QUEUE_VOLUME) {
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
