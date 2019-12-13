<?php

namespace app\firm\controller;

use app\firm\service\Firm as FirmServer;
use think\App;
use think\Controller;
use think\exception\ValidateException;

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
        $search = $this->request->get();
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
        $param = $this->request->param();
        $this->validate($param, 'app\manager\validate\FirmValidate');

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
        $param=$this->request->post();
        $this->validate($param, 'app\manager\validate\FirmValidate');

        $param['create_by']=session('user.id');
        $user=$this->service->addFirm($param);
        return $this->jsonReturn(0, '操作成功', $user);
    }

    /**
     * 用户密码修改
     * @param $id
     * @return \think\response\Json
     */
    public function pass($id) {

        $post = $this->request->post();
        if ($post['password'] !== $post['repassword']) {
            throw new ValidateException('两次输入的密码不一致！');
        }
        if (strlen($post['password'])<6) {
            throw new ValidateException('密码长度不能小于6位');
        }
        $data = ['password' => md5($post['password'])];
        $this->service->updateFirmById($id, $data);
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
