<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\manager\service;

use app\manager\model\Menu as MenuModel;
use app\manager\model\Node as NodeModel;

class Menu {

    private $model;

    public function __construct(MenuModel $model) {
        $this->model = $model;
    }

    public function updateMenuById(int $id, array $data) {
        $result = $this->model->save($data, ['id' => $id]);
        isModelFailed($result, '修改菜单失败');
        return $this->model;
    }

    public function addMenu(array $data) {
        $result = $this->model->save($data);
        isModelFailed($result, '添加菜单失败');
        return $this->model;
    }

    public function delMenuById(int $id) {
        $result = $this->model->destroy($id);
        isModelFailed($result, '删除菜单失败');
        return $result;
    }

    public function getUserMenuTree( bool $isLogin) {
        $nodes=session('user.nodes');
        $nodes=array_merge($nodes,['#']);
        $list = $this->model->where(['status' => '1'])->whereIn('url',$nodes)
                            ->order('sort asc,id asc')
                            ->field('id,pid,title,node,url,furl')
                            ->select()->toArray();
        $result = $this->buildMenuData(arr2tree($list), $nodes, $isLogin);

        return $result;
    }
    public function getUserRedirectMenu(){
        $nodes=session('user.nodes');
        return $this->model->where(['status' => '1'])->whereIn('url',$nodes)
                            ->order('sort asc,id asc')
                            ->column('furl');

    }
    public function getMenus() {
        $_menus = $this->model->where(['status' => '1'])->order('sort asc,id asc')->field('id,pid,title,url,furl')->selectOrFail()
                              ->toArray();
//        $_menus[] = ['title' => '顶级菜单', 'id' => '0', 'pid' => '-1'];
        $menus = arr2table($_menus);

        return $menus;
    }

    public function getFatherMenus() {
        $menus = $this->getMenus();
        foreach ($menus as $key => &$menu) {
            if (substr_count($menu['path'], '-') > 3) {
                unset($menus[$key]);
                continue;
            }
        }
        array_unshift($menus,['title' => '顶级菜单', 'id' => '0', 'pid' => '-1']);
        return $menus;
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus 当前菜单列表
     * @param array $nodes 系统权限节点数据
     * @param bool  $isLogin 是否已经登录
     * @return array
     */
    private function buildMenuData($menus, $nodes, $isLogin) {
        foreach ($menus as $key => &$menu) {
            !empty($menu['sub']) && $menu['sub'] = $this->buildMenuData($menu['sub'], $nodes, $isLogin);
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (preg_match('/^https?\:/i', $menu['url'])) {
                continue;
            } elseif ($menu['url'] !== '#') {
                $node = join('/', array_slice(explode('/', preg_replace('/[\W]/', '/', $menu['url'])), 0, 3));
                $menu['url'] = url($menu['url']) . (empty($menu['params']) ? '' : "?{$menu['params']}");
                if (isset($nodes[$node]) && $nodes[$node]['is_login'] && empty($isLogin)) {
                    unset($menus[$key]);
                } elseif (isset($nodes[$node]) && $nodes[$node]['is_auth'] && $isLogin && !auth($node)) {
                    unset($menus[$key]);
                }
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }
}
