<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/6
 * Time: 17:49
 * description:描述
 */

namespace app\manager\model;

use think\Model;
use think\model\Pivot;

class Access extends Pivot {
    protected $table = 'system_auth_node';
    protected $pk    = 'id';

    /**
     * 模型初始化
     * 模型初始化方法通常用于注册模型的事件操作。
     */
    protected static function init() {
        //TODO:初始化内容
    }



}
