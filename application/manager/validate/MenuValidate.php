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

class MenuValidate extends Validate {
    use ValidateTrait;
    //todo 验证父级菜单存在
    protected $rule =   [
//        'id' => 'number',
        'pid' => 'requireCallback:requireWhenCreate|number',
        'title' => 'requireCallback:requireWhenCreate|chsDash',
        'url' => 'requireCallback:requireWhenCreate|checkUrl:thinkphp',
        'furl' => 'requireCallback:requireWhenCreate',
    ];

    protected $message  =   [
//        'id.number' => 'ID格式错误！',
        'pid.number' => '父节点ID无效！',
        'pid.requireCallback' => '父节点不能为空！',
        'title.chsDash'     => '节点名称无效!',
        'title.requireCallback'     => '节点名称不能为空!',
        'url.requireCallback' => '后端节点不能为空！',
        'furl.requireCallback' => '前端URL不能为空！',
    ];

    // 自定义验证规则
    protected function checkUrl($url, $rule, $data = []) {

        $message=null;
        if($url){
            $result = Db::name('system_node')->where('node', $url)->findOrEmpty();
            if (!$result) {
                $message='不存在的节点!';
            }
        }
        return $message ?: true;
    }
}
