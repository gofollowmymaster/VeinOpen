<?php


namespace app\manager\middleware;

use app\common\exception\AuthException;
use app\common\service\AuthService;
use think\Db;
use think\Request;

/**
 * 系统权限访问管理
 * Class Auth
 * @package app\admin\middleware
 */
class Auth
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handle($request, \Closure $next)
    {
        list($module, $controller, $action) = [$request->module(), $request->controller(), $request->action()];
        $access = $this->buildAuth($node = AuthService::parseNodeStr("{$module}/{$controller}/{$action}"));
        // 登录状态检查
        if (!empty($access['is_login']) && !session('user')) {
            throw new AuthException('抱歉，您还没有登录获取访问权限！');
        }
        // 访问权限检查
        if (!empty($access['is_auth']) && !auth($node)) {
            throw new AuthException('抱歉，您没有访问该模块的权限！');
        }
        // 模板常量声明
        app('view')->init(config('template.'))->assign(['classuri' => AuthService::parseNodeStr("{$module}/{$controller}")]);
        return $next($request);
    }

    /**
     * 根据节点获取对应权限配置
     * @param string $node 权限节点
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function buildAuth($node)
    {
        $info = Db::name('SystemNode')->cache(true, 30)->where(['node' => $node])->find();
        return [
            'is_menu'  => intval(!empty($info['is_menu'])),
            'is_auth'  => intval(!empty($info['is_auth'])),
            'is_login' => empty($info['is_auth']) ? intval(!empty($info['is_login'])) : 1,
        ];
    }
}
