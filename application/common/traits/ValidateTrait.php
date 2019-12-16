<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/13
 * Time: 9:38
 */

namespace app\common\traits;


Trait ValidateTrait {

    protected function requireWhenCreate($value, $rule, $data = []) {
        $id=$rule['id']??null;
       return ($id||$id===0||$id==='0') ?false: true;
    }

}
