<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/08
 * Time: 18:17
 */
namespace app\common\exception;

class GrpcException extends ApplicationException {
    public $needCallManager=true;

}
