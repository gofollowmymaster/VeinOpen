<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/7
 * Time: 18:12
 */

namespace app\common\exception;
namespace Common\Service\Device\Request;

use Common\Traits\DataSetTrait;
use Common\Traits\SingletonTrait;

class DeviceAuth {
    use SingletonTrait;
    use DataSetTrait;

    private $description   = '用户';
    private $readAbleField = ['user_id', 'user_type', 'appid', 'bus_id', 'm_id', 'group_id', 'username', 'phone'];
    private $modifiableField = ['appid'];


    public function setUserInfo(array $data) {
        $this->setData($data);
        return static::$instance;
    }


    public static function user() {
        return self::getInstance()->getData();
    }


}
