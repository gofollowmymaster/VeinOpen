<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 14:29
 */

namespace app\common\event\listeners;

use app\common\event\events\Event;

/**
 * 推送签到指静脉认证信息  监听者
 * Class VeinWriteSignSetListener
 * @package Common\Service\EventService\Listeners
 */
class VeinWriteSignSetListener extends EventListener {
    protected function _handle(Event $event) {
        vein_write_sign_set($event->eventInfo['appId'], $event->eventInfo['veinUid']);
    }

}
