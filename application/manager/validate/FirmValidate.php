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
use think\Db;
use think\exception\ValidateException;
use think\Validate;

class FirmValidate extends Validate {
    use ValidateTrait;
    protected $rule = ['firm_name' => 'requireCallback:requireWhenCreate|chsDash|unique:system_firm',
                       'desc' => 'chsDash',
                       'phone' => 'mobile',
                       'mail' => 'email',
                       'status' => 'in:0,1',];

    protected $message = [
                          'firm_name.requireCallback' => '商家名不能为空',
                          'firm_name.chsDash' => '商家名格式错误',
                          'firm_name.unique' => '商家名已存在',
                          'desc.chsDash' => '描述信息有非法字符!',
                          'phone.mobile'     => '手机号格式错误！',
                          'mail.email' => '邮箱格式错误！',
                          'status.in'=>'错误的状态',
                          ];

}
