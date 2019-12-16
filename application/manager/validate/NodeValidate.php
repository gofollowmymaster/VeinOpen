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

class NodeValidate extends Validate {

    protected $rule =   [
        'title' => 'chsDash',
        'is_menu' => 'in:0,1',
        'is_auth' => 'in:0,1',
        'is_login' => 'in:0,1',
        'status' => 'in:0,1',
        'action'=>'in:title,menu,auth,login,status',
    ];

    protected $message  =   [
        'title.chsDash'     => '节点名称无效!',
        'is_menu.in' => '错误输入！',
        'is_auth.in' => '错误输入！',
        'is_login.in' => '错误输入！',
        'status.in' => '错误输入！',
        'action.in' => '无效操作！',
    ];
}
