<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\manager\controller;

use service\DataService;
use app\manager\service\Node as NodeServer;
use think\App;
use think\Controller;

/**
 * 系统功能节点管理
 * Class Node
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:13
 */
class Node extends Controller
{

    private $service;

    public function __construct(App $app = null, NodeServer $service) {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 显示节点列表
     * @return string
     */
    public function index()
    {
        $group=$this->request->param('group','');
        $result=$this->service->searchNodes($group);
        return $this->jsonReturn(0,'操作成功',$result);
    }

    /**
     * 清理无效的节点记录
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function clear($group)
    {
        $this->service->clearNodes($group);
        return $this->jsonReturn();
    }

    public function  autoAdd($group){
        $this->service->autoAdd($group);
        return $this->jsonReturn();
    }

}
