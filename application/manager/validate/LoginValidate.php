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

class LoginValidate extends Validate {
    protected $rule =   [
        'username' => 'require|chsDash',
        'password' => 'require|alphaDash|min:6',
    ];

    protected $message  =   [
        'username.require' => '登录账号不能为空！',
        'username.chsDash'     => '登录账号有非法字符！',
        'password.require' => '登录密码不能为空！',
        'password.min'     => '登录密码长度不能少于6位有效字符！',
        'password.alphaDash'     => '登录密码有非法字符！',
    ];
}
