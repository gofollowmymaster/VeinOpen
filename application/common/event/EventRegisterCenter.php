<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 10:04
 */

namespace app\common\event;

use app\common\event\events\Event;
use app\common\event\events\LoginSuccessEvent;
use app\common\event\listeners\LoginSuccessListener;
use app\common\event\listeners\EventListener;


/**
 * 事件注册中心
 * Class EventRegisterCenter
 * @package Common\Service\EventService
 */
class EventRegisterCenter {

    private static $listen = [
        //指签到静脉验证成功事件
        LoginSuccessEvent::class  => [
            LoginSuccessListener::class,//指静脉4.0 写入场馆签到集合
        ],
    ];

    private static $eventAlias = [];
    //todo  临时添加事件监听者
    public static function addEventListeners(Event $event, EventListener $listener) {

    }

    /**
     * 获取某事件的所有监听者
     * @param $event
     * @return mixed
     */
    public static function getEventListeners(Event $event) {
        $event = getClass($event);
        if (!array_key_exists($event, self::$listen)) {
            throw new \Exception('没有定义事件:' . $event);
        }
        return self::$listen[$event] ?: [];
    }
}
