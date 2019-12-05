<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/11/12
 * Time: 9:38
 */
namespace app\common\traits;


Trait SingletonTrait{

    private static $instance;

    private function __construct() {

    }

    /**
     * 获取认证实例
     * @param $busId
     * @return DeviceGateRule
     * @throws \Exception
     */
    public static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {
    }
}
