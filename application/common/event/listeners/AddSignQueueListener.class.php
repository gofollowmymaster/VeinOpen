<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 14:29
 */

namespace app\common\event\listeners;

use Common\Service\EventService\Events\Event;
use Common\Service\RedisService;

/**
 * 推送签到指静脉认证信息  监听者
 * Class VeinWriteSignSetListener
 * @package Common\Service\EventService\Listeners
 */
class AddSignQueueListener extends EventListener {
    protected function _handle(Event $event) {
        $rs = new RedisService();
        $rs->set_new_sign_log($event->eventInfo['bus_id'], $event->eventInfo['create_time']);
    }

}
