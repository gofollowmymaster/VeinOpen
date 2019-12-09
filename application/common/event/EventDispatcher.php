<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/9/4
 * Time: 9:57
 */

namespace app\common\event;

use app\common\event\events\Event;
use app\common\event\listeners\EventListener;

/**
 * 事件分发(调度)器
 * 事件触发后通过事件分发器通知监听该事件的监听者
 * 本类为单例 调用时必须先触发事件
 * Class EventDispatcher
 * @package Common\Service\EventService
 */
class EventDispatcher {


    private static $instance;
    private        $event;                 # 当前事件
    protected      $expectinos = [];       # 事件监听者执行过程中产生的异常
    public static  $needAbort  = false;   # 监听者异常后中断后续监听者执行

    private function __construct() {

    }

    /**
     * 获取时间分发器实例
     * @param $busId
     * @return DeviceGateRule
     * @throws \Exception
     */
    private static function getInstance() {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 添加事件
     * @param Event $event
     */
    private function setEvent(Event $event) {
        $this->event = $event;
        return self::$instance;
    }

    /**
     * 调配事件监听者
     * @throws \Exception
     */
    private function eventHandle() {
        $this->_before();
        $listeners = EventRegisterCenter::getEventListeners($this->event);

        while ($listener = array_shift($listeners)) {
            try {
                if (!class_exists($listener)) {
                    throw new \Exception('监听者不存在');
                }
                $listener = new $listener();
                if (!($listener instanceof EventListener)) {
                    throw new \Exception('非法的监听者:' . getclass($listener));
                }
                $listener->handle($this->event);
            } catch (\Exception $e) {
                $this->expectinos[] = $e;
                if (self::$needAbort) {
                    throw new \Exception('监听者执行中断:异常信息:' . json_encode($this->expectinos));
                }
            }
        }
        $this->_after();
    }

    /**
     * todo 事件支持异步则放入队列
     * 事件分发完成前操作
     */
    private function _before() {

    }

    /**
     * 事件分发完成后操作
     */
    private function _after() {
        if (count($this->expectinos) > 0) {
            logw('监听者执行异常:异常信息:' . json_encode($this->expectinos), Event::logFileName);
            $this->expectinos = [];
        }
        $this->event = null;
    }

    /**
     * 触发事件
     * @param Event $event
     * @throws \Exception
     */
    public static function trigger(Event $event) {
        try {
            self::getInstance()->setEvent($event)->eventHandle();
        } catch (\Throwable $e) {
            report('事件' . getClass($event) . '执行错误:' . $e->getMessage());
        }
    }
}
