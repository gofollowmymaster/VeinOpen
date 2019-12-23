<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/21
 * Time: 9:49
 * description:描述
 */
namespace app\command\logCenter\consumer;

use app\common\exception\ConfigException;

class Messager implements Consumer{
    private $config;
    private $driver;
    private $keyFilters=['error','emergency','critical','alert','warning'];
    public function __construct(array $config){
        $this->config=$config;
        $driverName='app\command\logCenter\consumer\messager\\'.$config['type'];
        if(!class_exists($driverName)){
            throw new ConfigException( '配置异常,消息驱动' . $driverName . '不存在');
        }
        $this->driver=new $driverName($config['groups']);
    }
    public function handle($message) {
        // TODO: Implement handle() method.
        return $this->messageFilter($message,function ($message){
            echo 'message send result:'.json_encode($this->driver->handle(json_encode($message),'default'))."\n";
        });

    }

    private function messageFilter(array $message,\Closure $func){
        if(!array_intersect(array_keys($message),$this->keyFilters)){
            echo '消息被消费者过滤:'.json_encode($message)."\n";
            return ;
        }
        return $func($message);
    }
}
