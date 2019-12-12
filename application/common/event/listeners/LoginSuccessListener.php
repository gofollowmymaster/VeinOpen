<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/9/4
 * Time: 14:29
 */

namespace app\common\event\listeners;

use app\common\event\events\Event;
use app\common\service\NodeService;
use app\common\service\LogService;
use think\Db;

/**
 * 登陆成功  监听者
 * Class VeinWriteSignSetListener
 * @package Common\Service\EventService\Listeners
 */
class LoginSuccessListener extends EventListener {
    protected function _handle(Event $event) {
        $user = $event->eventInfo;
        // 更新登录信息
        Db::name('SystemUser')->where(['id' => $user['id']])
                              ->update(['login_at'  => Db::raw('now()'),
                                        'login_num' => Db::raw('login_num+1'),]);
        !empty($user['authorize']) && NodeService::applyAuthNode();
        LogService::write('系统管理', '用户登录系统成功');
    }

}
