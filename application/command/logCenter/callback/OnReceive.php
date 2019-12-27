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


    public function __construct() {

    }

    public function index($server, $fd, $data) {
        if (!$param = json_decode($data, true)) {
            throw new WarringException('参数错误:' . $data);
        }
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
//        output('推送数据结果:'.$res);

    }


}
