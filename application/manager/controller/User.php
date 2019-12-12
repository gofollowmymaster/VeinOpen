<?php

namespace app\manager\controller;

use app\manager\service\User as UserServer;
use think\App;
use think\Controller;
use think\exception\ValidateException;

/**
 * 管理员控制器
 * Class User
 * @package app\manager\controller
 */
class User extends Controller {

    private $service;

    public function __construct(App $app = null, UserServer $service) {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 用户列表
     * @return \think\response\Json
     */
    public function index() {
        $search = $this->request->get();
        $result = $this->service->searchUsers($search);
        return $this->jsonReturn(0, '操作成功', $result);
    }


    /**
     * 用户编辑
     * @param $id
     * @return \think\response\Json
     */
    public function edit($id) {

        $user=$this->service->getUserById($id);
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
        $this->validate($param, 'app\manager\validate\UserValidate');

        //执行更新
        $this->service->updateUserById($id, $param);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 添加用户
     * @return \think\response\Json
     */
    public function save() {
        $param=$this->request->post();
        $this->validate($param, 'app\manager\validate\UserValidate');

        $param['create_by']=session('user.id');
        $user=$this->service->addUser($param);
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
        $this->service->updateUserById($id, $data);
        return $this->jsonReturn(0,'密码修改成功');
    }



    /**
     * 删除用户
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete($id) {

        $this->service->delUserById($id);
        return $this->jsonReturn();
    }

}
