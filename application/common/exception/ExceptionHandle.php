<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2019/12/5
 * Time: 10:55
 * description:描述
 */

namespace app\common\exception;

use app\common\tool\Reporter;
use Exception;
use think\Container;
use think\exception\DbException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Config;

class ExceptionHandle extends Handle {
    protected $ignoreReport = ['\\think\\exception\\ValidateException', '\\think\\exception\\RuntimeException',
                               '\\app\\common\\exception\\IngoreReportException',];

    public function render(Exception $e) {
        if ($e instanceof ValidateException) {
            return json(['message' => $e->getError(), 'status' => $e->getCode(), 'data' => []], 200);
        }
        $message = $this->cutHeaderMsg($e->getMessage());
        if ($e instanceof HttpException) {
            return response($message, $e->getStatusCode());
        }
        return json(['message' => $message, 'status' => $e->getCode(), 'data' => []], 200);
    }

    public function report(Exception $exception) {

        if (!$this->isIgnoreReport($exception)) {
            $log = $this->buildReportContent($exception);
            $config = Container::get('app')->config('reporter.');
            Reporter::getInstance($config)->Report($log);
        }
    }

    private function buildReportContent(\Throwable $exception) {
        // 收集异常数据
        $log = "\n请求时间:" . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $log .= "\n请求URI:" . $_SERVER['REQUEST_URI'];
        $log .= "\n来源IP:" . getRealIp();
        $log .= "\n请求异常:";

        if (Container::get('app')->isDebug()) {
            $data = ['file'    => $exception->getFile(), 'line' => $exception->getLine(),
                     'message' => $this->getMessage($exception), 'code' => $this->getCode($exception),];
            $log .= "[{$data['code']}]{$data['message']}\n异常位置:[{$data['file']}:{$data['line']}]";
        } else {
            $data = ['code' => $this->getCode($exception), 'message' => $this->getMessage($exception),];
            $log .= "[ {$data['code']}]{$data['message']}";
        }

        if (($exception instanceof \think\Exception) && $data = $exception->getData()) {
            unset($data['Database Config']);
            unset($data['PDO Error Info']['SQLSTATE']);
            unset($data['PDO Error Info']['Driver Error Code']);
            unset($data['Database Status']['Error Code']);
            $log .= arrayToStr($data);
        }

        if (Container::get('app')->config('log.record_trace')) {
            $log .= "\r\n" . $exception->getTraceAsString();
        }
        $log .= "\n请求信息:\n" . json_encode(requestInfo());

        return $log;
    }

    private function cutHeaderMsg(string $message) {

        $msg = explode(':', $message)[0];
        if (Config::get('app_debug') == false) {
            if ($msg == $message && mb_strlen($msg) > 20) {
                $msg = '处理异常';
            } elseif (mb_strlen($msg) > 20) {
                $msg = substr($msg, 0, 20);
            }
        }
        return $msg;
    }
}
