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

class Node extends Model {
    protected $table = 'system_node';
    protected $pk    = 'id';

    /**
     * 模型初始化
     * 模型初始化方法通常用于注册模型的事件操作。
     */
    protected static function init() {
        //TODO:初始化内容
    }
    public function role()
    {
        return $this->belongsToMany('Role','\\app\\model\\Access');
    }

}
