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
use app\command\logCenter\reporter\Reporter;
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
        $this->messager = new Reporter($config['reporter']);

    }

    public function execute(Input $input, Output $output) {
        $this->server = new \swoole_server('127.0.0.1', 9556);

        $this->server->set(['worker_num'       => $this->swooleConfig['worker_num'],
                            'task_worker_num'  => $this->swooleConfig['task_worker_num'],
                            'max_request'      => $this->swooleConfig['max_request'],
                            'task_max_request' => $this->swooleConfig['task_max_request'],
                            'daemonize'        => $this->swooleConfig['daemonize'],
                            'log_file'         => $this->swooleConfig['log_file'],]);
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
        echo '定时启用tast数量' . count($this->topics) . "\n";


        if ($workerId < $this->swooleConfig['worker_num']) {
            foreach ($this->topics as $topic) {

                $server->tick(3000, function () use ($server,$topic ) {
                    $server->task($topic);
                });
            }
        }

    }

    public function onStart($server) {
        swoole_set_process_name("swoole_master_ding"); //主进程命名
    }

    public function onReceive($server, $fd, $reactorId, $data) {
//        echo json_encode($server->stats());

        try {
            $onReceive = new OnReceive();
            $onReceive->index($server,$fd,$data);
        } catch (\Throwable $e) {
            $this->reporte('task执行异常:'.$e->getMessage());
            return '执行异常:' . $e->getFile().'第'.$e->getLine().'行'.$e->getMessage() . "\n";
        }finally{
            unset($onReceive);
        }
//        return "task finish {$workerId} {$taskId} " . $topic;
    }

    public function onTask($ws, $workerId, $taskId, $topic) {

        try {
            $onTask = new OnTask();
            $onTask->index($topic);
        } catch (\Throwable $e) {
            $this->reporte('task执行异常:'.$e->getMessage());
            return 'task执行异常:' . $e->getFile().'第'.$e->getLine().'行'.$e->getMessage() . "\n";
        }finally{
            unset($onTask);
        }
        return "task finish {$workerId} {$taskId} " . $topic;
    }

    public function onFinish($server, $taskId, $result) {
        echo date('Y-m-d H:i:s') . " : " . $result . "\n";
    }

    public function reporte(string $content){
        $this->messager->Report($content,'default');
    }


}
