<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/5
 * Time: 14:52
 * description:描述
 */

namespace app\common\tool\messager;

use app\common\tool\Http;

class Ding extends Messager {
    private $client;
    private $config;
    private $gate = "https://oapi.dingtalk.com/robot/send?access_token=";

    public function __construct(array $config) {
        $this->client = new Http();
        $this->config = $config;
    }

    public function handle(string $message,$destination) {

        $token=$this->config[$destination]['token'];
        $url = $this->gate . $token;
        $options['header']=["Content-Type:application/json; charset=utf-8"];
        $options['timeout']=1;
        return $this->client->post($url,$message,$options);
    }
}
