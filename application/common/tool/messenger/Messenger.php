<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2019/12/5
 * Time: 14:55
 * description:描述
 */

namespace app\common\tool\messenger;

abstract class Messenger {

    abstract public function handle(string $message,$destination) ;
}
