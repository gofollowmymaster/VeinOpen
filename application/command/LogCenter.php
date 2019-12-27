<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/13
 * Time: 17:48
 * description:描述
 */

namespace app\command;

use app\command\logCenter\callback\OnReceive;
use app\command\logCenter\callback\OnTask;
use app\command\logCenter\redis\RedisPool;
use app\common\exception\WarringException;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;

class LogCenter extends Command {
    private   $server;
    protected $swooleConfig;
    protected $redisConfig;
    protected $topics;
    protected $messager;
    protected $reporterConfig;

    protected function configure() {
        $this->setName('LogCenter')->setDescription('logcenter');
    }

    public function __construct() {
        parent::__construct();
        $config = Config::get('logCenter.');
        $this->swooleConfig = $config['server']['swoole'];
        $this->redisConfig = $config['server']['redis'];
        $this->topics = array_keys($config['topicConsumersMap']);
        date_default_timezone_set('Asia/Shanghai');
        $this->reporterConfig=$config['reporter'];
    }

    public function execute(Input $input, Output $output) {
        $this->server = new \swoole_server('127.0.0.1', 9556);

        $this->server->set(['worker_num'       => $this->swooleConfig['worker_num'],
                            'task_worker_num'  => $this->swooleConfig['task_worker_num'],
                            'max_request'      => $this->swooleConfig['max_request'],
                            'task_max_request' => $this->swooleConfig['task_max_request'],
                            'daemonize'        => $this->swooleConfig['daemonize'],
                            'log_file'         => $this->swooleConfig['log_file'],
                            'open_eof_check' => true, //打开EOF检测
                            'package_eof' => $this->swooleConfig['package_eof'], //设置EOF
            ]);
        $this->server->on('WorkerStart', [$this, 'onWorkStart']);
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('Receive', [$this, 'onReceive']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);
        $this->server->start();
    }

    public function onWorkStart($server, $workerId) {
        try {
            RedisPool::getInstance($this->redisConfig);
        } catch (\Exception $e) {
            $server->shutdown();
            echo 'redis连接异常:' . $e->getMessage() . "\n";
        }

        require __DIR__.DIRECTORY_SEPARATOR.'logCenter/helper.php';

        //定时日志消费任务
        if ($workerId < $this->swooleConfig['worker_num']) {
            output( '定时启用tast数量' . count($this->topics) );
            foreach ($this->topics as $topic) {
                $param=['fd'=>'null','server'=>'tick','time'=>time(),
                        'request'=>['controller'=>'LogController','method'=>'consumeFromRedis',
                                  'params'=>[['topic'=>$topic]]]];
                $server->tick(5000, function () use ($server,$param ) {
                    $server->task($param);
                });
            }
        }
    }

    public function onStart($server) {
        swoole_set_process_name("swoole_master_log"); //主进程命名
    }

    public function onReceive($server, $fd, $reactorId, $data) {

        output("{$server->worker_id} {$fd}".'接受数据:'.$data=rtrim($data,$this->swooleConfig['package_eof']));
        try {

            $onReceive = new OnReceive();
            $onReceive->index($server,$fd,$data);
        } catch (\Throwable $e) {
            $this->report('请求执行异常:'.$e->getMessage());
            if (!$res=$server->send($fd,json_encode(['code'=>1,'msg'=>'请求执行异常:'.$e->getMessage()]) )) {
                output( "receive work finish {$server->worker_id} {$fd} " . '请求执行异常='.$data);
            }
        }finally{
            unset($onReceive);
        }
        output( "{$server->worker_id} {$fd} receive work finish " );
    }

    public function onTask($ws, $workerId, $taskId, $param) {

        try {
            $onTask = new OnTask();
            switch ($param['server']){
                case 'tick':
                    $onTask->tickTask($param);
                    break;
                case 'tcp':
                    $onTask->tcpTask($param);
                    break;
                default:
                    throw new WarringException('异常的任务来源!'.$param['server']);
            }


        } catch (\Throwable $e) {
            $this->report('task执行异常:'.$e->getMessage());
            output( 'task执行异常:' . $e->getFile().'第'.$e->getLine().'行'.$e->getMessage() );
        }finally{
            unset($onTask);
        }
        return "{$workerId} {$taskId} task finish  " ;
    }

    public function onFinish($server, $taskId, $result) {
        output(  $result );
    }



    public function report(string $content){
        try {
            reportLog($content,$this->reporterConfig);

        } catch (\Throwable $e) {
            output('report失败:文件'.$e->getFile().';第'.$e->getLine().'行;错误信息'.$e->getMessage().'内容='.$content);
        }
    }


}
