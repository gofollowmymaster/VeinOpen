<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 16:55
 * description:描述
 */

namespace app\firm\validate;

use think\Db;
use think\exception\ValidateException;
use think\Validate;

class FirmValidate extends Validate {
    protected $rule = ['username' => 'require|chsDash|unique:system_user',
                       'desc' => 'chsDash',
                       'phone' => 'number',
                       'mail' => 'email',
                       'firm_id'   => 'requireCallback:checkRequire|number|checkFirm:thinkphp',
                       'authorize' => 'checkAuthorize:thinkphp',];

    protected $message = [
                          'username.require' => '管理员名不能为空',
                          'username.chsDash' => '管理员名格式错误',
                          'username.unique' => '管理员名已存在',
                          'desc.chsDash' => '描述信息有非法字符!',
                          'phone.number'     => '手机号格式错误！',
                          'mail.email' => '邮箱格式错误！',
                          'firm_id.requireCallback'=>'请选择商家',
                          'firm_id.number'=>'商家信息错误',
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
