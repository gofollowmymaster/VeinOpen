<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/7
 * Time: 15:47
 */

namespace app\common\repository;

use app\common\exception\WarringException;

class CabinetRepository extends RedisDriver {

    const USER_USING_CABINET_NO     = "<<appid>>:<<userId>>:cabinetNumber";
    const TODAY_CABINET_USiNG_LOG   = "cabinet:<<appid>>:<<deviceId>>:using:log";
    const CABINET_USING_SET         = "cabinet:<<appid>>:<<deviceId>>:using";
    const CABINET_FREE_SET          = "cabinet:<<appid>>:<<deviceId>>:free";
    const PRIORITY_CABINET_ALL_SET  = "cabinet:<<appid>>:<<deviceId>>:priority:<<num>>:template";
    const PRIORITY_CABINET_FREE_SET = "cabinet:<<appid>>:<<deviceId>>:priority:<<num>>:free";
    const CABINET_FREEZE_SET        = "cabinet:<<appid>>:<<deviceId>>:freeze";

    const BUS_CLEAN_CABINET_CONFIG = 'need:clean:cabinet:bussiness';

    public function getCabinetFree(string $appid, string $deviceId) {
        $key = $this->buildRedisKey(self::CABINET_FREE_SET, $appid, $deviceId);
        return $this->sMembers($key);
    }

    public function getUserUsingCabinetNo(string $appid, int $userId) {
        $key = $this->buildRedisKey(self::USER_USING_CABINET_NO, $appid, $userId);
        return $this->get($key);
    }

    public function getUsingCabinetNo(string $appid, string $deviceId) {
        $key = $this->buildRedisKey(self::CABINET_USING_SET, $appid, $deviceId);
        return $this->sMembers($key);
    }

    public function getFreeCabinetNo(string $appid, string $deviceId) {
        $key = $this->buildRedisKey(self::CABINET_FREE_SET, $appid, $deviceId);
        return $this->sMembers($key);
    }

    public function getFreeCabinetNoByPriority(string $appid, string $deviceId, int $priority) {
        $key = $this->buildRedisKey(self::PRIORITY_CABINET_FREE_SET, $appid, $deviceId, $priority);
        return $this->sMembers($key);
    }

    public function getFreezeCabinetNo(string $appid, string $deviceId) {
        $key = $this->buildRedisKey(self::CABINET_FREEZE_SET, $appid, $deviceId);
        return $this->sMembers($key);
    }

    public function isUsing(string $appid, string $deviceId,  $cabinetNo) {
        $key = $this->buildRedisKey(self::CABINET_USING_SET, $appid, $deviceId);
        return $this->sIsMember($key, $cabinetNo);
    }

    public function isFreeze(string $appid, string $deviceId,  $cabinetNo) {
        $key = $this->buildRedisKey(self::CABINET_FREEZE_SET, $appid, $deviceId);
        return $this->sIsMember($key, $cabinetNo);
    }

    public function isFree(string $appid, string $deviceId,  $cabinetNo) {
        $key = $this->buildRedisKey(self::CABINET_FREE_SET, $appid, $deviceId);
        return $this->redis->sIsMember($key, $cabinetNo);
    }

    public function rentCabinet($userId, $deviceId, $appid, $cabinetNo) {

        $keys['cabinetFreeSetKey'] = $this->buildRedisKey(self::CABINET_FREE_SET, $appid, $deviceId);
        $keys['cabinetPriority1FreeSetKey'] = $this->buildRedisKey(self::PRIORITY_CABINET_FREE_SET, $appid, $deviceId, 1);
        $keys['cabinetPriority2FreeSetKey'] = $this->buildRedisKey(self::PRIORITY_CABINET_FREE_SET, $appid, $deviceId, 2);
        $keys['cabinetPriority3FreeSetKey'] = $this->buildRedisKey(self::PRIORITY_CABINET_FREE_SET, $appid, $deviceId, 3);
        $keys['cabinetUsingSetKey'] = $this->buildRedisKey(self::CABINET_USING_SET, $appid, $deviceId);
        $keys['userUsingCabinetNoKey'] = $this->buildRedisKey(self::USER_USING_CABINET_NO, $appid, $userId);
        $needClear = $this->sIsMember(self::BUS_CLEAN_CABINET_CONFIG, $appid) ?: false;
        $this->pipeLine(function ($pipe) use ($keys, $deviceId, $cabinetNo, $needClear) {
            $pipe->sRem($keys['cabinetFreeSetKey'], $cabinetNo);
            $pipe->sRem($keys['cabinetPriority1FreeSetKey'], $cabinetNo);
            $pipe->sRem($keys['cabinetPriority2FreeSetKey'], $cabinetNo);
            $pipe->sRem($keys['cabinetPriority3FreeSetKey'], $cabinetNo);
            $pipe->sAdd($keys['cabinetUsingSetKey'], $cabinetNo);
            $pipe->set($keys['userUsingCabinetNoKey'], $deviceId . '|' . $cabinetNo);
            if ($needClear) {
                $tomorrow = strtotime(date('Y-m-d', strtotime('+1 day')));
                $pipe->expireAt($keys['userUsingCabinetNoKey'], $tomorrow + 10800);
            }
        });
    }


    private function buildRedisKey() {
        $params = func_get_args();
        $subject = $params[0];
        unset($params[0]);
        $params = array_values($params);
        $pattern = array_fill(0, count($params), '/<<[\w]+>>/');
        $result = preg_replace($pattern, $params, $subject, 1);
        if (!$result || strpos('>>', $result)) {
            throw new WarringException('redis键正则替换异常!' . $subject);
        }
        return $result;
    }
}
