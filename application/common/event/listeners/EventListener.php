<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 10:22
 */

namespace app\common\event\listeners;

use app\common\event\events\Event;
/**
 * Class EventListener
 * @package Common\Service\EventService\Listeners
 */
abstract class EventListener {

    public function handle(Event $event) {
        $this->_before($event);
        $this->_handle($event);
        $this->_after($event);
    }

    /**
     * 监听者处理前钩子
     * @param Event $event
     */
    protected function _before(Event $event) {
    }

    /**
     * 监听者处理后钩子
     * @param Event $event
     */
    protected function _after(Event $event) {
    }

    /**
     * 监听者自定义处理逻辑
     * @param Event $event
     * @return mixed
     */
    protected abstract function _handle(Event $event);
}
