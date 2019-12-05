<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 14:16
 */

namespace app\common\event\events;

class veinAuthSuccEvent extends Event {

    public function __construct(string $appId, string $veinUid) {
        $this->eventInfo['appId'] = $appId;
        $this->eventInfo['veinUid'] = $veinUid;
    }
}
