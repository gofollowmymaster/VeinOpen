<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/10/21
 * Time: 17:58
 */


namespace app\common\dataset;

use app\common\exception\WarringException;
use  app\common\traits\SingletonTrait;

class RequestLog {
    use SingletonTrait;

    private $data;
    private $canNotModifyFields = [];
    private $objectMembers      = [];

    private function __construct() {

    }

    public function __set($name, $value) {
        if (in_array($name, $this->canNotModifyFields)&&$this->data[$name]) {
            throw new WarringException('无权修改该参数:' . $name);
        }
        $this->data[$name] = $value;
    }

    public function __get($name) {
        $result = $this->data[$name];
        return $result;
    }

    public function __toString() {
        $result = [];
        foreach ($this->data as $key => $value) {
            if (in_array($key, $this->objectMembers)) {
                $result[$key] = $value->toArray();
                continue;
            }
            $result[$key] = $value;
        }
        return json_encode($result);
    }
}
