<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/17
 * Time: 18:17
 * description:描述
 */

namespace app\command\logCenter\callback;

use app\command\logCenter\redis\RedisPool;
use app\common\exception\ConfigException;
use app\common\exception\WarringException;
use think\facade\Config;

class OnReceive {


    public function __construct() {

    }

    public function index($server,$fd,$data) {
        if($param=json_decode($data,true)){
            throw new WarringException('参数错误:'.$data);

        }
        $param=['fd'=>$fd,'server'=>'tcp','time'=>time(),
                'request'=>['controller'=>$param['controller'],'method'=>$param['method'],
                            'params'=>$param['params']]];
        $server->task($param);

    }



}
