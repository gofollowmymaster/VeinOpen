<?php


namespace app\manager\controller;

use app\common\exception\AuthException;

use app\manager\service\User as UserServer;
use app\manager\service\Menu as MenuServer;
use think\App;
use think\Controller;
use think\Db;


class Index extends Controller {

    /**
     * @param MenuServer $menuServer
     * @return \think\response\Json
     * @throws AuthException
     */
    public function menus(MenuServer $menuServer) {
        //        NodeService::applyAuthNode();

        $menus = $menuServer->getUserMenuTree(!!session('user'));
        if (empty($menus) && !session('user.id')) {
            throw new AuthException('没有任何权限');
        }
        return $this->jsonReturn(0, '操作成功', $menus);
    }


    /**
     * @param UserServer $userServer
     * @return \think\response\Json
     * @throws AuthException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pass(UserServer $userServer) {
        $data = $this->request->post();
        $userId = session('user.id');
        if ($data['password'] !== $data['repassword']) {
            throw new AuthException('两次输入的密码不一致，请重新输入！');
        }
        $user = Db::name('SystemUser')->where('id', $userId)->find();
        if (md5($data['oldpassword']) !== $user['password']) {
            throw new AuthException('旧密码错误,请重新输入');
        }
        $userServer->updateUserById($userId, [ 'password' => md5($data['password'])]);

        return $this->jsonReturn(0, '密码修改成功，下次请使用新密码登录！');
    }

    /**
     * @param UserServer $userServer
     * @return \think\response\Json
     */
    public function info(UserServer $userServer) {
        $userId = session('user.id');
        $param = $this->request->only(['username' ,'desc', 'phone', 'mail'],'post');
        $this->validate($param, 'app\manager\validate\UserValidate');
        $userServer->updateUserById($userId,$param);
        return $this->jsonReturn();
    }

    /**
     * miss路由 所有错误路由转发到这儿
     * @return \think\response\Json
     */
    public function miss() {
        return $this->jsonReturn(1, '请求地址错误');
    }
}
