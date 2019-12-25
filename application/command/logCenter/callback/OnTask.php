<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/17
 * Time: 18:17
 * description:描述
 */

namespace app\command\logCenter\callback;



class OnTask {


    //todo  task中做路由分发工作 添加controller模块
    public function __construct() {

    }

    public function tickTask($task) {
        output ('来自'.$task['server']."的任务:请求数据:".json_encode($task['request']));
        $class= $task['request']['controller'];
        $method= $task['request']['method'];
        $params= $task['request']['params'];
        $class='app\command\logCenter\controller\\'.$class;
        $classObj = new $class();
        $response = call_user_func_array([$classObj, $method], $params);

        return ['server'=>'tick','request'=> $task['request'],'response'=>$response];
    }

    public function tcpTask($task) {
        output ('来自'.$task['server']."的任务:请求数据:".json_encode($task['request'])."\n");
        $class= $task['request']['controller'];
        $method= $task['request']['method'];
        $params= $task['request']['params'];

        $class='app\command\logCenter\controller\\'.$class;
        $classObj = new $class();
        $response = call_user_func_array([$classObj, $method], $params);

        return ['server'=>'tick','request'=> $task['request'],'response'=>$response];
    }

}
