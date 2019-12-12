<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/10/21
 * Time: 18:12
 */

namespace app\common\dataset;

use  app\common\traits\DataSetTrait;
use  app\common\traits\SingletonTrait;

class DeviceInfo {
    use SingletonTrait;
    use DataSetTrait {
        DataSetTrait::__get as  getValue;
    }

    private $description     = '设备';
    private $readAbleField   = ['id', 'device_id', 'device_name', 'bus_id', 'device_type_id', 'appid', 'device_plan_id',
                                'ext_attr', 'validate', 'validateUp', 'four_phone'];
    private $modifiableField = ['appid'];

    public function setDeviceInfo(array $data) {
        $this->setData($data);
        return static::$instance;
    }

    public static function device() {
        return self::getInstance()->getData();
    }

    public function __get($name) {
        if (!($value = $this->getValue($name)) && $this->ext_attr) {
            $value = json_decode($this->ext_attr,true)[$name];
        }
        return $value;
    }
}
