<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\manager\service;

use app\manager\model\Access;
use app\manager\model\Role as RoleModel;
use app\manager\service\Node as NodeServer;

class Role {

    private $model;

    public function __construct(RoleModel $model) {
        $this->model = $model;
    }

    public function getRoleList() {
        $result = $this->model->field('id,title,status,sort,desc')->order('sort asc')->paginate(10);
        return $result;
    }
    public function getRoleById($id){
        $result = $this->model->where(['id' => $id])->field('id,title,sort,status,desc')->findOrEmpty()->toArray();
        isEmptyInDb($result, '不存在的角色');
        return $result;
    }
    public function updateRoleById(int $id, array $data) {
        $result = $this->model->update($data, ['id' => $id]);
        isModelFailed($result, '编辑角色失败');
        return $this->model;
    }

    public function addRole(array $data) {
        $result = $this->model->save($data);
        isModelFailed($result, '添加角色失败');
        return $this->model;
    }

    public function delRoleById(int $id) {
        $result = $this->model->destroy($id);
        isModelFailed($result, '删除菜单失败');
        return $result;
    }

    /**
     * 读取授权节点
     * @param string $auth
     */
    public function getAuthNode($auth)
    {
        $node=new NodeServer(new \app\manager\model\Node());
        $nodes = $node->getNodesInDb();
        $nodes=$this->addPnodeToNodes($nodes);
        $checked =Access::where(['auth' => $auth])->column('node');
        foreach ($nodes as &$node) {
            $node['checked'] = in_array($node['node'], $checked);
        }
        $result = $this->buildNodeTree(arr2tree($nodes, 'node', 'pnode', '_sub_'));
       return $result;
    }

    /**
     * 节点数据拼装
     * @param array $nodes
     * @param int $level
     * @return array
     */
    protected function buildNodeTree($nodes, $level = 1)
    {
        foreach ($nodes as $key => $node) {
            if (!empty($node['_sub_']) && is_array($node['_sub_'])) {
                $node[$key]['_sub_'] = $this->buildNodeTree($node['_sub_'], $level + 1);
            }
        }
        return $nodes;
    }

    public function saveAuthNode(int $auth,$nodes){

        $data=[];
        foreach ($nodes as $node) {
            $data[] = ['auth' => $auth, 'node' => $node];
        }
        Access::transaction(function ()use($auth,$data){
            $res=Access::where('auth',$auth)->delete();
            isModelFailed($res,'修改授权节点失败!');
            $res=Access::insertAll($data);
            isModelFailed($res,'修改授权节点失败!');
        });
        return true;
    }


}
