<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 16:55
 * description:描述
 */

namespace app\manager\validate;

use think\Db;
use think\exception\ValidateException;
use think\Validate;

class FirmValidate extends Validate {
    protected $rule = ['firm_name' => 'require|chsDash|unique:system_firm',
                       'desc' => 'chsDash',
                       'phone' => 'mobile',
                       'mail' => 'email',
                       'status' => 'in:0,1',];

    protected $message = [
                          'firm_name.require' => '商家名不能为空',
                          'firm_name.chsDash' => '商家名格式错误',
                          'firm_name.unique' => '商家名已存在',
                          'desc.chsDash' => '描述信息有非法字符!',
                          'phone.mobile'     => '手机号格式错误！',
                          'mail.email' => '邮箱格式错误！',
                          'status.in'=>'错误的状态',
                          ];


    // 自定义验证规则
    protected function checkAuthorize($value, $rule, $data = []) {
        $roles = explode(',', $value) ?: [];

        try {
            foreach ($roles as $role) {
                if (!is_numeric($role)) {
                    throw new ValidateException('授权角色错误');
                }
                $result = Db::name('system_auth')->where('id', $role)->findOrEmpty();
                if (!$result) {
                    throw new ValidateException('授权角色不存在');
                }
            }
            $result = true;
        } catch (\Throwable $e) {
            $result = false;
            $message = $e->getMessage() ?: '授权角色错误';
        }
        return $result ? true : $message;
    }

    // 自定义验证规则
    protected function checkFirm($firmId, $rule, $data = []) {

        $message=null;
        if (!is_numeric($firmId)) {
            $message='错误的商家ID';
        }
        //非商家账号为0
        if($firmId){
            $result = Db::name('open_firm')->where('id', $firmId)->findOrEmpty();
            if (!$result) {
                $message='错误的商家ID';
            }
        }
        return $message ?: true;
    }

    protected function checkRequire($firmId, $rule, $data = []) {
        $result=false;
        if (!isset($rule['id'])&&(!isset($firmId)||$firmId=='')) {
            $result=true;
        }
        return $result;
    }
}
