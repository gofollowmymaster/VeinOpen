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
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;

class ExceptionHandle extends Handle {
    protected $ignoreReport = ['\\think\\exception\\ValidateException',
                               '\\think\\exception\\RuntimeException',
                               '\\app\\common\\exception\\IngoreReportException',];

    public function render(Exception $e) {
        if ($e instanceof ValidateException) {
            return json(['message'=>$e->getError(),  'status'=>$e->getCode()],200);
        }
        if ($e instanceof HttpException) {
            return response($e->getMessage(), $e->getStatusCode());
        }
        return json(['message'=>$e->getMessage(),'status'=>$e->getCode()], 200);
    }

    public function report(Exception $exception) {

        if (!$this->isIgnoreReport($exception)) {
            // 收集异常数据
            if (Container::get('app')->isDebug()) {
                $data = [
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'message' => $this->getMessage($exception),
                    'code'    => $this->getCode($exception),
                ];
                $log = "[{$data['code']}]{$data['message']}[{$data['file']}:{$data['line']}]";
            } else {
                $data = [
                    'code'    => $this->getCode($exception),
                    'message' => $this->getMessage($exception),
                ];
                $log = "[{$data['code']}]{$data['message']}";
            }

            if (Container::get('app')->config('log.record_trace')) {
                $log .= "\r\n" . $exception->getTraceAsString();
            }

            $config=Container::get('app')->config('reporter.');
            Reporter::getInstance($config)->sendText($log);
        }
    }
}
