<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\common\service;


use think\Db;

/**
 * 系统权限节点读取器
 * Class NodeService
 * @package extend
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/05/08 11:28
 */
class AuthService
{

    /**
     * 应用用户权限节点
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function applyAuthNode()
    {
        cache('need_access_node', null);
        $userid = session('user.id');
//        if (($userid = session('user.id'))) {
//            session('user', Db::name('SystemUser')->where(['id' => $userid])->find());
//        }
        if($userid==10000){
            $nodes=Db::name('SystemNode')->where(['status'=>1])->column('node');
            return session('user.nodes', $nodes);
        }
        if (($authorize = session('user.authorize'))) {
            $where = ['status' => '1'];
            $authorizeids = Db::name('SystemAuth')->whereIn('id', explode(',', $authorize))->where($where)->column('id');
            if (empty($authorizeids)) {
                return session('user.nodes', []);
            }
            $nodes = Db::name('SystemAuthNode')->whereIn('auth', $authorizeids)->column('node');
            return session('user.nodes', $nodes);
        }
        return false;
    }

    /**
     * 获取授权节点
     * @return array
     */
    public static function getAuthNode()
    {
        $nodes = cache('need_access_node');
        if (empty($nodes)) {
            $nodes = Db::name('SystemNode')->where(['is_auth' => '1'])->column('node');
            cache('need_access_node', $nodes);
        }
        return $nodes;
    }

    /**
     * 检查用户节点权限
     * @param string $node 节点
     * @return bool
     */
    public static function checkAuthNode($node)
    {
        list($module, $controller, $action) = explode('/', str_replace(['?', '=', '&'], '/', $node . '///'));
        $currentNode = self::parseNodeStr("{$module}/{$controller}") . strtolower("/{$action}");
        if (session('user.username') === 'admin' || stripos($node, 'admin/index') === 0) {
            return true;
        }
        if (!in_array($currentNode, self::getAuthNode())) {
            return true;
        }
        $userNodes=session('user.nodes');
        return in_array($currentNode, (array)session('user.nodes'));
    }

    /**
     * 驼峰转下划线规则
     * @param string $node
     * @return string
     */
    public static function parseNodeStr($node)
    {
        $tmp = [];
        foreach (explode('/', $node) as $name) {
            $tmp[] = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
        return trim(join('/', $tmp), '/');
    }


}
