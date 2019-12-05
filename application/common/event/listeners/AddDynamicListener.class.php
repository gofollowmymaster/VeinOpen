<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 14:29
 */

namespace app\common\event\listeners;

use Common\Service\EventService\Events\Event;
use Common\Service\SignService;

/**
 * 推送签到指静脉认证信息  监听者
 * Class VeinWriteSignSetListener
 * @package Common\Service\EventService\Listeners
 */
class AddDynamicListener extends EventListener {
    protected function _handle(Event $event) {
        $SignService = new SignService();
        $SignService->add_dynamic($event->eventInfo['bus_id'], $event->eventInfo['user_id']);
    }

}
