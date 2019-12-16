<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 16:55
 * description:描述
 */

namespace app\manager\validate;

use app\common\traits\ValidateTrait;
use think\Validate;

class RoleValidate extends Validate {
    use ValidateTrait;
    protected $rule =   [
        'title' => 'requireCallback:requireWhenCreate|chsDash',
        'desc' => 'chsDash',
        'sort' => 'number',
        'status'=>'in:0,1',
    ];

    protected $message  =   [
        'title.requireCallback' => '角色名不能为空!',
        'title.chsDash' => '角色名格式错误!',
        'desc.chsDash'     => '描述信息有非法字符!',
        'sort.number' => '排序不能为空！',
        'status.in' => '错误的状态！',
    ];
}
