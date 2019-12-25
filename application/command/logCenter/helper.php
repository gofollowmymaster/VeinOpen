<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/23
 * Time: 15:17
 * description:描述
 */

if (!function_exists('output')) {
    function output(string $msg = '') {
        echo "[".date("Y-m-d H:i:s")."] ".$msg.PHP_EOL;
    }
}
