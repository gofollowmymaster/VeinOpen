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
     protected $server;
    protected $swooleConfig;
    protected $redisConfig;
    protected $topics;
    protected $messager;
    protected $reporterConfig;
    protected $echo;

    protected function configure() {
        $this->setName('LogCenter')->addArgument('operation', Argument::OPTIONAL, "operation")
             ->addOption('daemon', 'd', Option::VALUE_NONE, 'is daemon?')->setDescription('logcenter');
    }

    public function __construct() {
        set_error_handler(['app\command\logCenter\HandlerException', 'appError']);
        register_shutdown_function(['app\command\logCenter\HandlerException', 'fatalError']);
        require __DIR__ . DIRECTORY_SEPARATOR . 'logCenter/helper.php';
        parent::__construct();
        $config = Config::get('logCenter.');
        $this->swooleConfig = $config['server']['swoole'];
        $this->redisConfig = $config['server']['redis'];
        $this->topics = array_keys($config['topicConsumersMap']);
        date_default_timezone_set('Asia/Shanghai');
        $this->reporterConfig = $config['reporter'];
    }

    public function execute(Input $input, Output $output) {

        $this->checkCli();
        $this->checkExtension();
        $operation = trim($input->getArgument('operation'));
        $option = $input->hasOption('daemon') ? : false;
        $this->echo = $output;
        $this->showUsageUI($operation);
        $this->parseCommand($operation, $option);

    }


    protected  function parseCommand(string $operation,bool $option) {
        if ($option) {
            $this->swooleConfig['set']['daemonize']=1;
        }
        switch ($operation) {
            case 'start':
                $this->start();
                break;
            case 'status':
                $this->status();
                break;
            case 'reload':
                $this->reload();
                break;
            case 'stop':
                $this->stop();
                break;
            default:
                echo "Bad Operation." . PHP_EOL;
        }
    }

    private  function start() {
        $this->server = new \swoole_server($this->swooleConfig['tcpHost'], $this->swooleConfig['tcpPort']);

        $this->server->set($this->swooleConfig['set']);
        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('ManagerStart', [$this, 'onManagerStart']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('Receive', [$this, 'onReceive']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->start();
    }



    public  function onStart($server) {
        try {
            $masterProcessName = $this->swooleConfig['masterProcessName'];
            swoole_set_process_name($masterProcessName);
            echo str_pad("Master", 18, ' ', STR_PAD_BOTH) . str_pad($masterProcessName, 26, ' ', STR_PAD_BOTH) . str_pad($server->master_pid, 16, ' ', STR_PAD_BOTH) . str_pad(posix_getppid(), 16, ' ', STR_PAD_BOTH) . str_pad(posix_user_name(), 16, ' ', STR_PAD_BOTH) . PHP_EOL;

            file_put_contents($this->swooleConfig['masterPidFile'], $server->master_pid . "-" . posix_getppid() . "-" . posix_user_name());

        } catch (\Throwable $e) {
            $this->echo->writeln('启动服务异常:' . $e->getMessage());
        }
    }

    public  function onManagerStart($serv) {
        try {
            $managerProcessName = $this->swooleConfig['managerProcessName'];
            echo str_pad("Manager", 20, ' ', STR_PAD_BOTH) . str_pad($managerProcessName, 24, ' ', STR_PAD_BOTH) . str_pad($serv->manager_pid, 16, ' ', STR_PAD_BOTH) . str_pad(posix_getppid(), 16, ' ', STR_PAD_BOTH) . str_pad(posix_user_name(), 16, ' ', STR_PAD_BOTH) . PHP_EOL;

            file_put_contents($this->swooleConfig['managerPidFile'], $serv->manager_pid . "-" . posix_getppid() . "-" . posix_user_name());

        } catch (\Throwable $e) {
            $this->echo->writeln('启动服务(manager)异常:' . $e->getMessage());
        }

    }

    public  function onWorkerStart($server, $workerId) {

        try {
            RedisPool::getInstance($this->redisConfig);
        } catch (\Exception $e) {
            $server->shutdown();
            $this->echo->writeln('redis连接异常:' . $e->getMessage());
        }

        $config=$this->swooleConfig;
        if ($workerId >= $config['set']['worker_num']) {
            swoole_set_process_name($config['taskProcessName']);
            echo str_pad("Task", 18, ' ', STR_PAD_BOTH) . str_pad($config['taskProcessName'], 24, ' ', STR_PAD_BOTH) . str_pad($server->worker_pid, 20, ' ', STR_PAD_BOTH) . str_pad(posix_getppid(), 12, ' ', STR_PAD_BOTH) . str_pad(posix_user_name(), 20, ' ', STR_PAD_BOTH) . PHP_EOL;

            file_put_contents($config['taskPidFile'], $server->worker_pid . "-" . posix_getppid() . "-" . posix_user_name() . '|', FILE_APPEND);


        } else {
            swoole_set_process_name($config['workerProcessName']);
            echo str_pad("Worker", 18, ' ', STR_PAD_BOTH) . str_pad($config['workerProcessName'], 26, ' ', STR_PAD_BOTH) . str_pad($server->worker_pid, 16, ' ', STR_PAD_BOTH) . str_pad(posix_getppid(), 16, ' ', STR_PAD_BOTH) . str_pad(posix_user_name(), 16, ' ', STR_PAD_BOTH) . PHP_EOL;

            file_put_contents($config['workerPidFile'], $server->worker_pid . "-" . posix_getppid() . "-" . posix_user_name() . '|', FILE_APPEND);
            output('定时启用tast数量' . count($this->topics));
            foreach ($this->topics as $topic) {
                $param = ['fd'      => 'null', 'server' => 'tick', 'time' => time(),
                          'request' => ['controller' => 'LogController', 'method' => 'consumeFromRedis',
                                        'params'     => [['topic' => $topic]]]];
                $server->tick(5000, function () use ($server, $param) {
                    $server->task($param);
                });
            }
        }

    }

    public  function onReceive($server, $fd, $reactorId, $data) {

        $data = rtrim($data, $this->swooleConfig['set']['package_eof']);
        try {

            $onReceive = new OnReceive();
            $onReceive->index($server, $fd, $data);
        } catch (\Throwable $e) {
            $this->report('TCP请求执行异常:' . $e->getMessage());
            if (!$res = $server->send($fd, json_encode(['code' => 1, 'msg' => 'TCP请求执行异常:' . $e->getMessage()]))) {
                output("receive work finish {$server->worker_id} {$fd} " . '请求执行异常=' . $data);
            }
        } finally {
            unset($onReceive);
        }
        output("{$server->worker_id} {$fd} receive work finish ");
    }

    public  function onTask($ws, $workerId, $taskId, $param) {

        try {
            $onTask = new OnTask();
            switch ($param['server']) {
                case 'tick':
                    $onTask->tickTask($param);
                    break;
                case 'tcp':
                    $onTask->tcpTask($param);
                    break;
                default:
                    throw new WarringException('异常的任务来源!' . $param['server']);
            }

        } catch (\Throwable $e) {
            $this->report('task执行异常:' . $e->getMessage());
            output('task执行异常:' . $e->getFile() . '第' . $e->getLine() . '行' . $e->getMessage());
        } finally {
            unset($onTask);
        }
        return "{$workerId} {$taskId} task finish  ";
    }

    public  function onFinish($server, $taskId, $result) {
        output($result);
    }

    public  function report(string $content) {
        reportLog($content, $this->reporterConfig);
    }
    public  function status() {
        $config = $this->swooleConfig;
        if (!file_exists($config['masterPidFile']) || !file_exists($config['managerPidFile']) || !file_exists($config['workerPidFile'])) {
            output("暂无启动的服务");
            return false;
        }

        $this->showProcessUI($config);

        $masterPidString = trim(@file_get_contents($config['masterPidFile']));
        $masterPidArr = explode('-', $masterPidString);

        echo str_pad("Master", 18, ' ', STR_PAD_BOTH) . str_pad($config['masterProcessName'], 26, ' ', STR_PAD_BOTH) . str_pad($masterPidArr[0], 16, ' ', STR_PAD_BOTH) . str_pad($masterPidArr[1], 16, ' ', STR_PAD_BOTH) . str_pad($masterPidArr[2], 16, ' ', STR_PAD_BOTH) . PHP_EOL;

        $managerPidString = trim(@file_get_contents($config['managerPidFile']));
        $managerPidArr = explode('-', $managerPidString);

        echo str_pad("Manager", 20, ' ', STR_PAD_BOTH) . str_pad($config['managerProcessName'], 24, ' ', STR_PAD_BOTH) . str_pad($managerPidArr[0], 16, ' ', STR_PAD_BOTH) . str_pad($managerPidArr[1], 16, ' ', STR_PAD_BOTH) . str_pad($managerPidArr[2], 16, ' ', STR_PAD_BOTH) . PHP_EOL;

        $workerPidString = rtrim(@file_get_contents($config['workerPidFile']), '|');
        $workerPidArr = explode('|', $workerPidString);
        if (isset($workerPidArr) && !empty($workerPidArr)) {
            foreach ($workerPidArr as $key => $val) {
                $v = explode('-', $val);
                echo str_pad("Worker", 18, ' ', STR_PAD_BOTH) . str_pad($config['workerProcessName'], 26, ' ', STR_PAD_BOTH) . str_pad($v[0], 16, ' ', STR_PAD_BOTH) . str_pad($v[1], 16, ' ', STR_PAD_BOTH) . str_pad($v[2], 16, ' ', STR_PAD_BOTH) . PHP_EOL;
            }
        }

        $taskPidString = rtrim(@file_get_contents($config['taskPidFile']), '|');
        $taskPidArr = explode('|', $taskPidString);
        if (isset($taskPidArr) && !empty($taskPidArr)) {
            foreach ($taskPidArr as $key => $val) {
                $v = explode('-', $val);
                echo str_pad("Task", 18, ' ', STR_PAD_BOTH) . str_pad($config['taskProcessName'], 24, ' ', STR_PAD_BOTH) . str_pad($v[0], 20, ' ', STR_PAD_BOTH) . str_pad($v[1], 12, ' ', STR_PAD_BOTH) . str_pad($v[2], 20, ' ', STR_PAD_BOTH) . PHP_EOL;
            }
        }

    }
    protected  function reload() {
        $config = $this->swooleConfig;

        if (!file_exists($config['masterPidFile'])) {
            output("暂无启动的服务");
            return false;
        }

        $masterPidString = trim(file_get_contents($config['masterPidFile']));
        $masterPidArr    = explode( '-', $masterPidString);

        if (!\swoole_process::kill($masterPidArr[0], 0)) {
            output("PID:{$masterPidArr[0]} 不存在");
            return false;
        }

        \swoole_process::kill($masterPidArr[0], SIGUSR1);

        @unlink($config['workerPidFile']);
        @unlink($config['taskPidFile']);

        output("热加载成功");
        return true;
    }
    protected  function stop() {
        $config = $this->swooleConfig;

        if (!file_exists($config['masterPidFile'])) {
            output("暂无启动的服务");
            return false;
        }

        $masterPidString = trim(file_get_contents($config['masterPidFile']));
        $masterPidArr    = explode( '-', $masterPidString);

        if (!\swoole_process::kill($masterPidArr[0], 0)) {
            output("PID:{$masterPidArr[0]} 不存在");
            return false;
        }
        $this->echo->writeln('停止服务中...');
        $this->echo->writeln('请耐心等待');
        \swoole_process::kill($masterPidArr[0]);

        $time = time();
        while (true) {
            usleep(2000);
            if (!\swoole_process::kill($masterPidArr[0], 0)) {
                unlink($config['masterPidFile']);
                unlink($config['managerPidFile']);
                unlink($config['workerPidFile']);
                unlink($config['taskPidFile']);
                output("服务关闭成功");
                break;
            } else {
                if (time() - $time > 30) {
                    output("服务关闭失败，请重试");
                    break;
                }
            }
        }
        return true;
    }

    protected  function checkCli() {
        if (php_sapi_name() !== 'cli') {
            exit(output('服务只能运行在cli sapi模式下'));
        }
    }

    protected  function checkExtension() {
        if (!extension_loaded('swoole')) {
            exit(output('请安装swoole扩展'));
        }
    }

    protected  function showUsageUI($operation) {

        if (!$operation) {
            echo PHP_EOL;
            echo "-------------------------------------------------" . PHP_EOL;
            echo "|              Swoole-LogCenter                 |" . PHP_EOL;
            echo "|-----------------------------------------------|" . PHP_EOL;
            echo '|     USAGE: php think LogCenter operation      |' . PHP_EOL;
            echo '|-----------------------------------------------|' . PHP_EOL;
            echo '|      1. start       以debug模式开启服务       |' . PHP_EOL;
            echo '|      2. start -d on 以daemon模式开启服务      |' . PHP_EOL;
            echo '|      3. status      查看服务状态              |' . PHP_EOL;
            echo '|      4. reload      热加载                    |' . PHP_EOL;
            echo '|      5. stop        关闭服务                  |' . PHP_EOL;
            echo "-------------------------------------------------" . PHP_EOL;
            echo PHP_EOL;
            exit;
        }
    }

    protected  function showProcessUI(array $config) {
        //        if ($config['set']['daemonize'] == true) {
        //            return false;
        //        }
        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;
        echo "|" . str_pad("启动/关闭", 92, ' ', STR_PAD_BOTH) . "|" . PHP_EOL;
        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;

        echo str_pad("Start success.", 50, ' ', STR_PAD_BOTH) . str_pad("php think  LogCenter stop", 50, ' ', STR_PAD_BOTH) . PHP_EOL;

        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;
        echo "|" . str_pad("版本信息", 92, ' ', STR_PAD_BOTH) . "|" . PHP_EOL;
        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;

        echo str_pad("Swoole Version:" . SWOOLE_VERSION, 30, ' ', STR_PAD_BOTH) . str_pad("PHP Version:" . PHP_VERSION, 70, ' ', STR_PAD_BOTH) . PHP_EOL;

        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;
        echo "|" . str_pad("IP 信息", 90, ' ', STR_PAD_BOTH) . "|" . PHP_EOL;
        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;

        echo str_pad("IP:" . $config['tcpHost'], 50, ' ', STR_PAD_BOTH) . str_pad("PORT:" . $config['tcpPort'], 50, ' ', STR_PAD_BOTH) . PHP_EOL;

        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;
        echo "|" . str_pad("进程信息", 92, ' ', STR_PAD_BOTH) . "|" . PHP_EOL;
        echo str_pad("-", 90, '-', STR_PAD_BOTH) . PHP_EOL;

        echo str_pad("Swoole进程", 20, ' ', STR_PAD_BOTH) . str_pad('进程别名', 30, ' ', STR_PAD_BOTH) . str_pad('进程ID', 18, ' ', STR_PAD_BOTH) . str_pad('父进程ID', 18, ' ', STR_PAD_BOTH) . str_pad('用户', 18, ' ', STR_PAD_BOTH) . PHP_EOL;
    }


}
