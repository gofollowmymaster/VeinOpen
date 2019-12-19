<?php

namespace app\manager\controller;

use app\common\service\DataService;
use app\manager\service\Menu as MenuServer;
use think\App;
use think\Controller;
use app\manager\model\Menu as MenuModel;

/**
 * Class Menu
 * @package app\manager\controller
 */
class Menu extends Controller {

    private $service;

    public function __construct(App $app = null, MenuServer $service) {
        parent::__construct($app);
        $this->service = $service;
    }

    /**
     * @return \think\response\Json
     */
    public function index() {

        $menus = $this->service->getMenus();
        return $this->jsonReturn(0, '操作成功', $menus);
    }
    public function fatherMenus() {
        $menus = $this->service->getFatherMenus();
        return $this->jsonReturn(0, '操作成功', $menus);
    }

    /**
     * @return \think\response\Json
     */
    public function create() {
        $pid = $this->request->param('pid');
        return $this->jsonReturn(0, '操作成功', ['pid'=>$pid]);
    }

    /**
     * @return \think\response\Json
     */
    public function save() {
        $param = $this->request->only(['pid' ,'title','url'],'post');
        $this->validate($param, 'app\manager\validate\MenuValidate');
        //todo 检查是否有操作父菜单权限?
        //执行保存
        $this->service->addMenu($param);
        return $this->jsonReturn();
    }

    /**
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit($id) {
        $menu = MenuModel::where(['id' => $id])->field('id,pid,title,url,status')->find()->toArray();
        isEmptyInDb($menu, '不存在的菜单');
        return $this->jsonReturn(0, '操作成功', $menu);
    }

    public function update($id) {
        //验证数据
        $param = $this->request->only(['id','pid' ,'title','url'],'post');
        $this->validate($param, 'app\manager\validate\MenuValidate');
        unset($param['id']);
        //执行更新
        $this->service->updateMenuById($id, $param);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 删除菜单
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete($id) {

        $this->service->delMenuById($id);
        //返回数据
        return $this->jsonReturn();
    }

    /**
     * 菜单禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function forbid($id) {
        $this->service->updateMenuById($id, ['status'=>0]);
        //返回数据
        return $this->jsonReturn();
    }



}
