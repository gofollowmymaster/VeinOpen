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

class MenuValidate extends Validate {
    protected $rule =   [
        'pid' => 'number',
        'title' => 'chsDash',
        'url' => 'require',
//        'id' => 'number',
    ];

    protected $message  =   [
        'pid.number' => '父节点ID无效！',
        'title.chsDash'     => '节点名称无效!',
        'url.require' => 'url不能为空！',
//        'id.number'     => '节点ID无效！',
    ];
}
