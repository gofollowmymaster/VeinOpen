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
use think\facade\Config;

class OnReceive {
    private $consumerConfig;
    private $topicConsumersMap;
    private $consumers;
    private $redis;

    public function __construct() {

    }

    public function index($server,$fd,$data) {

    }



}
