<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 16:55
 * description:描述
 */

namespace app\manager\validate;

use think\Validate;

class RoleValidate extends Validate {
    protected $rule =   [
        'title' => 'chsDash',
        'desc' => 'chsDash',
        'sort' => 'number',
    ];

    protected $message  =   [
        'title.chsDash' => '角色名格式错误',
        'desc.chsDash'     => '描述信息有非法字符!',
        'sort.number' => '排序不能为空！',
    ];
}
