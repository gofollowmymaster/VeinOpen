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

use app\common\exception\AuthException;
use service\LogService;
use app\common\service\NodeService;
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
        $user = Db::name('SystemUser')->where(['username' => $data['username'], 'is_deleted' => '0'])->find();
        if(empty($user)){
            throw new AuthException('登录账号不存在，请重新输入!');
        }
        if(empty($user['status'])){
            throw new AuthException('账号已经被禁用，请联系管理员!');
        }
        if($user['password'] !== md5($data['password'])){
            throw new AuthException('登录密码错误，请重新输入!');
        }
        session('user', $user);

        //登陆事件后操作
        // 更新登录信息
        Db::name('SystemUser')->where(['id' => $user['id']])->update([
            'login_at'  => Db::raw('now()'),
            'login_num' => Db::raw('login_num+1'),
        ]);
        !empty($user['authorize']) && NodeService::applyAuthNode();
        LogService::write('系统管理', '用户登录系统成功');

        return json(['status'=>0,'msg'=>'登陆成功']);
    }

    /**
     * 退出登录
     */
    public function out()
    {
        !empty($_SESSION) && $_SESSION = [];
        [session_unset(), session_destroy()];
        LogService::write('系统管理', '用户退出系统成功');
        return json(['status'=>0,'msg'=>'退出成功']);
    }

}
