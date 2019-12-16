<?php

namespace app\manager\controller;

use app\manager\service\Firm as FirmServer;
use think\App;
use think\Controller;

/**
 * 管理员控制器
 * Class Firm
 * @package app\manager\controller
 */
class Firm extends Controller {

    private $service;

    public function __construct(App $app = null, FirmServer $service) {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 用户列表
     * @return \think\response\Json
     */
    public function index() {
        $search = $this->request->only(['firm_name','mail','phone','status'],'get');
        $result = $this->service->searchFirms($search);
        return $this->jsonReturn(0, '操作成功', $result);
    }


    /**
     * 用户编辑
     * @param $id
     * @return \think\response\Json
     */
    public function edit($id) {

        $user=$this->service->getFirmById($id);
        return $this->jsonReturn(0, '操作成功', $user);
    }

    /**
     * 修改用户信息
     * @param $id
     * @return \think\response\Json
     */
    public function update($id) {
        //验证数据
        $param = $this->request->only(['id','firm_name' ,'desc', 'phone', 'mail', 'status'],'param');
        $this->validate($param, 'app\manager\validate\FirmValidate');
        unset($param['id']);
        //执行更新
        $this->service->updateFirmById($id, $param);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 添加用户
     * @return \think\response\Json
     */
    public function save() {
        $param = $this->request->only(['firm_name' ,'desc', 'phone', 'mail', 'status'],'post');
        $this->validate($param, 'app\manager\validate\FirmValidate');

        $param['create_by']=session('user.id');
        $user=$this->service->addFirm($param);
        return $this->jsonReturn(0, '操作成功', $user);
    }

    /**
     * 商家appid修改
     * @param $id
     * @return \think\response\Json
     */
    protected function modifyAppid($id) {

//        $this->service->updateFirmById($id, $data);
        return $this->jsonReturn(0,'密码修改成功');
    }



    /**
     * 删除用户
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete($id) {

        $this->service->delFirmById($id);
        return $this->jsonReturn();
    }

}
