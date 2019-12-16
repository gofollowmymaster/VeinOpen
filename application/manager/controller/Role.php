<?php



namespace app\manager\controller;

use app\manager\service\Role as RoleServer;
use think\App;
use think\Controller;

/**
 * 系统角色管理控制器
 * Class Role
 * @package app\manager\controller
 */
class Role extends Controller {

    private $service;

    public function __construct(App $app = null, RoleServer $service) {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * 角色列表
     * @return \think\response\Json
     */
    public function index() {
        $list = $this->service->getRoleList();
        return $this->jsonReturn(0, '操作成功', $list);
    }

    /**
     * 添加角色
     * @return \think\response\Json
     */
    public function save() {
        $param = $this->request->only(['title','desc','status','sort'],'post');
        $this->validate($param, 'app\manager\validate\RoleValidate');

        //执行保存
        $this->service->addRole($param);
        return $this->jsonReturn();
    }

    /**
     * 编辑角色
     * @param $id
     * @return \think\response\Json
     */
    public function edit($id) {
        $role=$this->service->getRoleById($id);
        return $this->jsonReturn(0, '操作成功', $role);
    }

    /**
     * 修改角色
     * @param $id
     * @return \think\response\Json
     */
    public function update($id) {
        //验证数据
        $param = $this->request->only(['id','title','desc','status','sort'],'param');
        $this->validate($param, 'app\manager\validate\RoleValidate');
        unset($param['id']);
        //执行更新
        $this->service->updateRoleById($id, $param);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 删除角色
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete($id) {

        $this->service->delRoleById($id);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 菜单角色
     * @param $id
     * @return \think\response\Json
     */
    protected function forbid($id) {
        $this->service->updateRoleById($id, ['status'=>0]);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 读取授权节点
     * @param string $auth
     */
    public function getAuthNode($id)
    {
        $result = $this->service->getAuthNode($id);
        return $this->jsonReturn(0, '操作成功', $result);
    }

    /**
     * 保存授权节点
     * @return \think\response\Json
     */
    public function saveAuthNode($id){

        $nodes=$this->request->post('nodes',[]);
        $this->service->saveAuthNode($id,$nodes);

        return $this->jsonReturn(0,'节点授权更新成功！');
    }

}
