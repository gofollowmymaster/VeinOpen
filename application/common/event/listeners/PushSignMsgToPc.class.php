<?php
/**
 * Created by PhpStorm.
* User: zh
 * Date: 2019/9/4
 * Time: 14:29
 */

namespace app\common\event\listeners;

use Common\Service\SignScreen;
use Common\Service\AdminService;
use Common\Tools\RedisClient;
use app\common\event\events\Event;

/**
 * 推送签到PC端消息 监听者
 * Class PushSignMsgToPc
 * @package Common\Service\EventService\Listeners
 */
class PushSignMsgToPc extends EventListener {

    protected function _handle(Event $event) {
        $ss_service = new SignScreen();
        $sign_screen_data = $ss_service->my_rank($event->eventInfo['user_id']);
        $sign_screen_data['create_time'] = time();
        $sign_screen_data['type'] = 'rank';
        $sign_screen_data['action'] = 'push_info';

        $admin_list = (new AdminService())->admin_id_array($event->eventInfo['bus_id']);
        $sign_serial = C("SIGN_SERIAL");

        $info['action'] = "push_info";
        $info['user_id'] = $event->eventInfo['user_id'];
        $info['create_time'] = time();
        $info['sign_log_id'] = $event->eventInfo['id'];

        if (in_array($event->eventInfo['card_type'], [
            2,
            3,
        ])) {
            $info['sign_log_ids'] = [$event->eventInfo['id']];
        }

        $info['bus_id'] = $event->eventInfo['bus_id'];
        $info['type'] = 'sign';

        foreach ($sign_serial as $serial) {
            foreach ($admin_list as $admin_id) {
                $this->pushMsg($admin_id, $serial, $info, $sign_screen_data);
            }
        }
    }

    /**
     * 推送消息
     * @param $admin_id
     * @param $serial
     * @param $info
     * @param $sign_screen_data
     */
    private function pushMsg($admin_id, $serial, $info, $sign_screen_data) {
        try {
            $info['admin_id'] = $admin_id;

            $sign_screen_data['admin_id'] = $admin_id;
            $bus_fd_str = "online:admin:{$admin_id}:{$serial}:fd";
            $tcp_serial =  config('vein.veinTcpServer');
            $redis = new RedisClient(C('SEGW_REDIS_CONF'));
            $redis = $redis->getRedis(null, 'SEGWmaster');
            $count = $redis->sCard($bus_fd_str);

            //此服务器存在该场馆连接fd
            if ($count) {
                tcpPost(json_encode($info), $tcp_serial[$serial]['host'], $tcp_serial[$serial]['port']);
                tcpPost(json_encode($sign_screen_data), $tcp_serial[$serial]['host'], $tcp_serial[$serial]['port']);
            }
        } catch (\Exception $e) {
            report("推送签到消息失败:" . $e->getMessage() . ':$admin_id' . $admin_id . ':$serial' . $serial);
            logw("推送签到消息失败:" . $e->getMessage() . ':$admin_id' . $admin_id . ':$serial' . $serial, Event::logFileName);
        }
    }
}
