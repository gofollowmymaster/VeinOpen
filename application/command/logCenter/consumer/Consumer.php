<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/21
 * Time: 9:51
 * description:描述
 */

namespace app\command\logCenter\consumer;

interface Consumer {

    public function __construct(array $config);

    public function handle($content) ;
}
