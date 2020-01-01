<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/17
 * Time: 18:17
 * description:描述
 */

namespace app\command\logCenter\callback;


use app\common\exception\WarringException;


class OnReceive {
    protected $protocolKeys=['controller','method','params'];

    public function __construct() {

    }
    //todo 协议没有检查参数类型
    public function index($server, $fd, $data) {
        $param=$this->TcpProtocolCheck($data);

        $param = ['fd'      => $fd, 'server' => 'tcp', 'time' => time(),
                  'request' => ['controller' => $param['controller'], 'method' => $param['method'],
                                'params'     => $param['params']]];

        $rs = $server->task($param);
        if ($rs === false) {
            $return = ['code' => 1, 'msg' => '失败'];
        } else {
            $return = ['code' => 0, 'msg' => '成功'];
        }
        $return=json_encode($return);

        if (!$res=$server->send($fd, $return)) {
            throw new WarringException('返回信息失败:fd='.$fd.'message='.$return);
        }

    }
    protected function TcpProtocolCheck(string $data) {
        if (!$request = json_decode($data, true)) {
            throw new WarringException('不支持的日志协议:不是json字符串' . $data);
        }
        if($lack=array_diff($this->protocolKeys,array_keys($request))){
            throw new WarringException('不支持的日志协议:缺少关键key:'.implode(',',$lack));
        }
        return $request;
    }


}
