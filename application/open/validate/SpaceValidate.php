<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 16:55
 * description:描述
 */

namespace app\open\validate;

use app\common\traits\ValidateTrait;
use think\Db;
use think\Validate;

class SpaceValidate extends Validate {
    use ValidateTrait;
    protected $rule = ['space_name' => 'requireCallback:requireWhenCreate|chsDash|unique:system_space',
                       'desc' => 'chsDash', 'phone' => 'mobile',
                       'status' => 'in:0,1',];

    protected $message = ['space_name.requireCallback' => '场馆名不能为空!',
                          'space_name.chsDash' => '场馆名格式错误!',
                          'space_name.unique'  => '场馆名已存在!',
                          'desc.chsDash' => '描述信息有非法字符!',
                          'phone.mobile'       => '手机号格式错误！',
                          'status.in' => '错误的状态!',];


    // 自定义验证规则
    protected function checkfirmId($firmId, $rule, $data = []) {
        $message=null;
        $result = Db::name('system_firm')->where('id', $firmId)->findOrEmpty();
        if (!$result) {
            $message='场馆信息错误';
        }
        return $message ?:true ;
    }

}
