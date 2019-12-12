<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/12/6
 * Time: 14:16
 */

namespace app\common\event\events;

class LoginSuccessEvent extends Event {

    public function __construct(array $user) {
        $this->eventInfo = $user;
    }
}
