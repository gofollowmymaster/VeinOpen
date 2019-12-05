<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 14:35
 */

namespace app\common\event\listeners;

use Common\Service\SignScreen;
use Common\Service\EventService\Events\Event;

/**
 * 签到排名监听者
 * Class DoSignRankListener
 * @package Common\Service\EventService\Listeners
 */
class DoSignRankListener extends EventListener {
    protected function _handle(Event $event) {
        $ss_service = new SignScreen();
        $ss_service->do_sign_rank([
            'bus_id' => $event->eventInfo['bus_id'], 'user_id' => $event->eventInfo['user_id'], 'card_type' => 1,
        ]);
    }
}
