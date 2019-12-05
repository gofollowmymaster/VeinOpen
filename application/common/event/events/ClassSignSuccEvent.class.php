<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 14:16
 */

namespace app\common\event\events;

class ClassSignSuccEvent extends Event {

    public function __construct(int $signId) {
        $this->eventInfo = M('signLog')->where(['id' => $signId])->find();
    }
}
