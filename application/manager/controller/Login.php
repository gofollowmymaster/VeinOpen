<?php

namespace app\manager\controller;

use app\common\event\events\LoginSuccessEvent;
use app\common\exception\AuthException;
use service\LogService;
use think\Controller;
use think\Db;


class Login extends Controller
{

    /**
     * 控制器基础方法
     */
    public function initialize()
    {
        if (session('user.id') && $this->request->action() !== 'out') {
            throw new AuthException('您已经登陆!');
        }
    }

    /**
     * 用户登录
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index( )
    {
        // 输入数据效验
        $data = [
            'username' => $this->request->post('username', ''),
            'password' => $this->request->post('password', ''),
        ];
        $this->validate($data, 'app\manager\validate\LoginValidate');

        // 用户信息验证

        $user = Db::name('SystemUser')->where(['username' => $data['username'], 'is_deleted' => '0',])->find();
        if(empty($user)){
            throw new AuthException('登录账号不存在，请重新输入!');
        }
        if(empty($user['status'])){
            throw new AuthException('账号已经被禁用，请联系管理员!');
        }
        if($user['password'] !== md5($data['password'])){
            throw new AuthException('登录密码错误，请重新输入!');
        }
        //todo 商家状态 需要验证

        session('user', $user);
        //触发登陆成功事件
        triggerEvent(new LoginSuccessEvent($user));

        return $this->jsonReturn(0,'登陆成功');
    }

    /**
     * 退出登录
     */
    public function out()
    {
        !empty($_SESSION) && $_SESSION = [];
        [session_unset(), session_destroy()];

        LogService::write('系统管理', '用户退出系统成功');

        return $this->jsonReturn(0,'退出成功');

    }

}
