<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 14:55
 * description:描述
 */

namespace app\common\tool\messager;

abstract class Messager {

    abstract public function handle(string $message,$destination) ;
}
