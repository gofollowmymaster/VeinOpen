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

class RequestInfo {
    use SingletonTrait;

    private $data;
    private $canNotModifyFields = ['requestInfo', 'responseInfo', 'deviceInfo', 'userInfo'];
    private $objectMembers      = ['deviceInfo', 'userInfo'];

    private function __construct() {
        $this->data['userInfo'] = DeviceAuth::getInstance();
        $this->data['deviceInfo'] = DeviceInfo::getInstance();
    }

    public function request($param = null) {
        if (is_array($param)) {
            return array_filter($this->data['requestInfo'], function ($key) use ($param) {
                return in_array($key, $param);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $param ? $this->data['requestInfo'][$param] : $this->data['requestInfo'];
    }

    public function __set($name, $value) {
        if (in_array($name, $this->canNotModifyFields)&&$this->data[$name]) {
            throw new WarringException('无权修改该参数:' . $name);
        }
        $this->data[$name] = $value;
    }

    public function __get($name) {
        $result = $this->data[$name];
        if (is_null($result)) {
            $result = $this->data['requestInfo'][$name];
        }
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
