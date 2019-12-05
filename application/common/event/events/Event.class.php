<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 10:18
 */

namespace app\common\event\events;

abstract class Event {
    const logFileName = 'event';   # 事件系统产生的日志文件名
    public $eventInfo;             # 事件内容信息
}
