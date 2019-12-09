<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\manager\service;

use \app\manager\model\Auth as AuthModel;

class Auth {

    private $model;

    public function __construct(AuthModel $model) {
        $this->model = $model;
    }

    public function getAuthList() {
        $result = $this->model->order('sort asc')->paginate(10);
        return $result;
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

    public function getUserMenuTree(array $nodes, bool $isLogin) {
        $list = $this->model->where(['status' => '1'])->order('sort asc,id asc')->field('id,pid,title,node,url')
                            ->select()->toArray();
        $result = $this->buildMenuData(arr2tree($list), $nodes, $isLogin);

        return $result;
    }


}
