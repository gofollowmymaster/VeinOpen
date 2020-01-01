<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/23
 * Time: 15:17
 * description:描述
 */

use  app\command\logCenter\reporter\Reporter;

if (!function_exists('output')) {
    function output(string $msg = '') {
        echo "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL;
    }
}

if (!function_exists('reportLog')) {
    function reportLog(string $msg = '', array $config = []) {
        try {
            $config = $config ?: Config::get('logCenter.reporter.');
            $reporter = Reporter::getInstance($config);
            $reporter->Report($msg);
        } catch (\Throwable $e) {
            logToFile('report失败:文件' . $e->getFile() . ';第' . $e->getLine() . '行;错误信息' . $e->getMessage() . '内容=' . $msg);
        }
    }
}

if (!function_exists('logToFile')) {

    function logToFile($msg, $fileName = 'logCenter') {
        date_default_timezone_set('Asia/Chongqing');
        $logFile = sprintf("/mnt/online/log/qn-%s.%s.log", $fileName, date('Y-m-d', strtotime("today")));
        // 判断日志有没有达到2g, 如果达到就用不前时间戳重命名
        $flag = isOutSize($logFile);
        if ($flag) {
            // 重命名文件
            $str = date('Y-m-d', strtotime("today")) . '-' . time();
            $newName = sprintf("/mnt/online/log/qn-%s.%s.log", $fileName, $str);
            rename($logFile, $newName);
        }
        $hostName = phpversion() < "5.3.0" ? $_SERVER['HOSTNAME'] : gethostname();

        $fp = fopen($logFile, 'a');
        fwrite($fp, sprintf("%s\t%s\thostname=%s\n", date("H:i:s"), $msg, $hostName));
        fclose($fp);
    }
}
/**
 *判断日志文件是否超过大小，超过2G返回true ,否则返回false,文件不存在return false
 */
if (!function_exists('isOutSize')) {

    function isOutSize($logFile) {
        $config_size = 56200000;
        if (!file_exists($logFile)) {
            return false;
        }
        $size = filesize($logFile);
        if ($size < $config_size) {
            return false;
        } else {
            return true;
        }
    }
}
if (!function_exists('getRealIp')) {
    function getRealIp() {
        static $ip = false;
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        $ip = $ip ? $ip : $_SERVER['REMOTE_ADDR'];
        return $ip;
    }
}
if (!function_exists('posix_user_name')) {
    function posix_user_name() {
        $posix = posix_getpwuid(posix_getuid());
        return $posix['name'];
    }
}


