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
use think\Validate;

class SpaceValidate extends Validate {
    use ValidateTrait;
    protected $rule = ['space_name' => 'requireCallback:requireWhenCreate|chsDash|unique:system_space',
                       'desc' => 'chsDash', 'phone' => 'mobile',
                       'firm_id'    => 'requireCallback:requireWhenCreate|number|checkfirmId:thinkphp',
                       'status' => 'in:0,1',];

    protected $message = ['space_name.requireCallback' => '商家名不能为空!',
                          'space_name.chsDash' => '商家名格式错误!',
                          'space_name.unique'  => '商家名已存在!',
                          'desc.chsDash' => '描述信息有非法字符!',
                          'phone.mobile'       => '手机号格式错误！',
                          'firm_id.requireCallback' => '归属商家不能为空！',
                          'firm_id.number'     => '商家ID格式错误！',
                          'status.in' => '错误的状态!',];


    // 自定义验证规则
    protected function checkfirmId($firmId, $rule, $data = []) {
        $message=null;
        $result = Db::name('system_firm')->where('id', $firmId)->findOrEmpty();
        if (!$result) {
            $message='商家信息错误';
        }
        return $message ?:true ;
    }

}
