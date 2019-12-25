<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\manager\service;

use \app\manager\model\Node as NodeModel;
use think\db\Query;

class Node {

    private $model;
    private $ignore = [ 'manager/login','manager/index/'];

    public function __construct(NodeModel $model) {
        $this->model = $model;
    }

    public function searchNodes($module = '') {

        $nodes=$this->getNodesInDbWithPnode($module);
        $nodes = arr2table($nodes, 'node', 'pnode');
        $groups = [];
        foreach ($nodes as $node) {
            unset($node['path']);
            $pnode = explode('/', $node['node'])[0];
            if ($node['node'] === $pnode) {
                $groups[$pnode]['node'] = $node;
            }
            $groups[$pnode]['list'][] = $node;
        }
        return $groups;
    }

    public function clearNodes(string $module) {
        $nodes = array_keys($this->getNodesAllDetail($module));
        $result = $this->model->whereNotIn('node', $nodes)->delete();
        isModelFailed($result, '清理无效节点记录失败!');
       return true;
    }

    public function autoAdd(string $module) {
        $nodesInFile=$this->getNodesAllInFIle($module);

        $nodesInFile = array_keys($nodesInFile);
        $nodesInDb = $this->model->whereLike('node',"{$module}%")->column('node');
        $newNodes=array_diff($nodesInFile,$nodesInDb);
        array_walk($newNodes, function (&$value) {
            $node = $value;
            $value = [];
            $value['is_menu'] = 0;
            $value['is_auth'] = 1;
            $value['is_login'] = 1;
            $value['status'] = 0;
            $value['node'] = $node;
        });
        if(count($newNodes)>0){
            $result=$this->model->insertAll($newNodes);
            isModelFailed($result, '清理无效节点记录失败!');
        }
        return true;
    }
    /**
     * 获取系统代码节点以及详情
     * @param array $nodes
     * @return array
     */
    private function getNodesAllDetail(string $module,$nodes = []) {
        $alias = $this->model->whereLike('node',"{$module}/%")->column('node,is_menu,is_auth,is_login,title');

        foreach ($this->getNodeInFile(env('app_path')."/{$module}") as $thr) {
            foreach ($this->ignore as $str) {
                if (stripos($thr, $str) === 0) {
                    continue 2;
                }
            }
            $tmp = explode('/', $thr);
            list($one, $two) = ["{$tmp[0]}", "{$tmp[0]}/{$tmp[1]}"];
            $nodes[$one] = array_merge(isset($alias[$one]) ? $alias[$one] : ['node' => $one, 'title' => '', 'is_menu' => 0, 'is_auth' => 0, 'is_login' => 0], ['pnode' => '']);
            $nodes[$two] = array_merge(isset($alias[$two]) ? $alias[$two] : ['node' => $two, 'title' => '', 'is_menu' => 0, 'is_auth' => 0, 'is_login' => 0], ['pnode' => $one]);
            $nodes[$thr] = array_merge(isset($alias[$thr]) ? $alias[$thr] : ['node' => $thr, 'title' => '', 'is_menu' => 0, 'is_auth' => 0, 'is_login' => 0], ['pnode' => $two]);
        }
        foreach ($nodes as &$node) {
            list($node['is_auth'], $node['is_menu'], $node['is_login']) = [intval($node['is_auth']), intval($node['is_menu']), empty($node['is_auth']) ? intval($node['is_login']) : 1];
        }
        return $nodes;
    }
    /**
     * 获取系统代码节点以
     * @param array $nodes
     * @return array
     */
    private function getNodesAllInFile(string $module) {

        $nodes=[];
        foreach ($this->getNodeInFile(env('app_path')."{$module}") as $thr) {
            foreach ($this->ignore as $str) {
                if (stripos($thr, $str) === 0) {
                    continue 2;
                }
            }
            $tmp = explode('/', $thr);
            list($one, $two) = ["{$tmp[0]}", "{$tmp[0]}/{$tmp[1]}"];
            $nodes[$one]= $nodes[$one]??$one;
            $nodes[$two]= $nodes[$two]??$two;
            $nodes[$thr]= $nodes[$thr]??$thr;
        }

        return $nodes;
    }

    public function getNodesInDbWithPnode(string $module=''){
        $nodes = $this->getNodesInDb(['module'=>$module]);
        return $this->addPnodeToNodes($nodes);
    }

    public function getNodesInDb(array $search=[],string $field='node,is_menu,is_auth,is_login,title') {

        $query=new Query();
        foreach (['status','is_menu','is_auth','is_login'] as $key){
            if ((isset($search[$key]) && $search[$key] !== '')) {
                $query->where($key, "$search[$key]");
            }
        }
        $module=$search['module']??'';
        $query->whereLike('node',"{$module}%");
        $nodesInDb=$this->model->where($query)->column($field);
        return $nodesInDb;
    }
    protected function addPnodeToNodes($nodesInDb){
        $nodes=[];
        foreach ($nodesInDb as $node) {
            $thr=$node['node'];
            foreach ($this->ignore as $str) {
                if (stripos($thr, $str) === 0) {
                    continue 2;
                }
            }
            $tmp = explode('/', $thr);
            if(count($tmp)>1){
                list($one, $two) = ["{$tmp[0]}", "{$tmp[0]}/{$tmp[1]}"];
                $nodes[$two] = array_merge($nodesInDb[$two]??[], ['pnode' => $one]);
                $nodes[$thr] = array_merge($nodesInDb[$thr]??[], ['pnode' => $two]);
            }else{
                $one=$tmp[0];
                $nodes[$one] = array_merge($nodesInDb[$one]??[] , ['pnode' => '']);
            }
        }
        return $nodes;
    }

    /**
     * 获取节点列表
     * @param string $dirPath 路径
     * @param array  $nodes 额外数据
     * @return array
     */
    private  function getNodeInFile($dirPath, $nodes = []) {
        foreach ($this->scanDirFile($dirPath) as $filename) {
            $matches = [];
            if (!preg_match('|/(\w+)/controller/(\w+)|', str_replace(DIRECTORY_SEPARATOR, '/', $filename), $matches) || count($matches) !== 3) {
                continue;
            }
            $className = env('app_namespace') . str_replace('/', '\\', $matches[0]);
            if (!class_exists($className))
                continue;
            foreach (get_class_methods($className) as $funcName) {
                if (strpos($funcName, '_') !== 0 && $funcName !== 'initialize' && $funcName !== 'registerMiddleware') {
                    $nodes[] = $this->parseNodeStr("{$matches[1]}/{$matches[2]}") . '/' . strtolower($funcName);
                }
            }
        }
        return $nodes;
    }

    /**
     * 驼峰转下划线规则
     * @param string $node
     * @return string
     */
    private  function parseNodeStr($node) {
        $tmp = [];
        foreach (explode('/', $node) as $name) {
            $tmp[] = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
        return trim(join('/', $tmp), '/');
    }

    /**
     * 获取所有PHP文件
     * @param string $dirPath 目录
     * @param array  $data 额外数据
     * @param string $ext 有文件后缀
     * @return array
     */
    private  function scanDirFile($dirPath, $data = [], $ext = 'php') {
        foreach (scandir($dirPath) as $dir) {
            if (strpos($dir, '.') === 0) {
                continue;
            }
            $tmpPath = realpath($dirPath . DIRECTORY_SEPARATOR . $dir);
            if (is_dir($tmpPath)) {
                $data = array_merge($data, $this->scanDirFile($tmpPath));
            } elseif (pathinfo($tmpPath, 4) === $ext) {
                $data[] = $tmpPath;
            }
        }
        return $data;
    }

    public function updateNodeById(int $id, array $data) {
        $result = $this->model->save($data, ['id' => $id]);
        isModelFailed($result, '修改节点失败');
        return $this->model;
    }

    public function delNodeById(int $id) {
        $result = $this->model->destroy($id);
        isModelFailed($result, '删除节点失败');
        return $result;
    }


}
