<?php

namespace app\manager\controller;

use app\manager\service\Space as SpaceServer;
use think\App;
use think\Controller;

/**
 * 管理员控制器
 * Class Space
 * @package app\manager\controller
 */
class Space extends Controller {

    private $service;

    public function __construct(App $app = null, SpaceServer $service) {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 场馆列表
     * @return \think\response\Json
     */
    public function index() {
        $search = $this->request->only(['firm_id','space_name','mail','phone','status'],'get');
        $search['firm_id']=session('user.firm_id')?:null;
        $result = $this->service->searchSpaces($search);
        return $this->jsonReturn(0, '操作成功', $result);
    }


    /**
     * 场馆编辑
     * @param $id
     * @return \think\response\Json
     */
    public function edit($id) {
        $firmId=session('user.firm_id');
        $user=$this->service->getSpaceById($id,$firmId);
        return $this->jsonReturn(0, '操作成功', $user);
    }

    /**
     * 修改场馆信息
     * @param $id
     * @return \think\response\Json
     */
    public function update($id) {
        //验证数据
        $param = $this->request->only(['id','space_name','desc','status'],'param');
        $this->validate($param, 'app\manager\validate\SpaceValidate');
        //执行更新
        $firmId=session('user.firm_id')?:null;
        unset($param['id']);
        $this->service->updateSpaceById($id,$firmId, $param);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 添加场馆
     * @return \think\response\Json
     */
    public function save() {
        $param = $this->request->only(['space_name','desc'],'post');
        $this->validate($param, 'app\manager\validate\SpaceValidate');

        $param['firm_id']=session('user.firm_id');
        $param['status']=0;
        $param['create_by']=session('user.id');
        $user=$this->service->addSpace($param);
        return $this->jsonReturn(0, '操作成功', $user);
    }

    /**
     * 场馆密码修改
     * @param $id
     * @return \think\response\Json
     */
    protected function modifyAppid($id) {

//        $this->service->updateSpaceById($id, $data);
        return $this->jsonReturn(0,'密码修改成功');
    }

    /**
     * 删除场馆
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete($id) {
        $firmId=session('user.firm_id');
        $this->service->delSpaceById($id,$firmId);
        return $this->jsonReturn();
    }
}
