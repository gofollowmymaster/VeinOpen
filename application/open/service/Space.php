<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\manager\service;

use \app\manager\model\Space as SpaceModel;
use think\db\exception\DataNotFoundException;
use think\db\Query;

class Space {

    private $model;

    public function __construct(SpaceModel $model) {
        $this->model = $model;
    }

    public function searchSpaces(array $search) {
        $query = new Query();

        foreach (['firm_id', 'phone','status'] as $key) {
            if ((isset($search[$key]) && $search[$key] !== '')) {
                $query->where($key, "$search[$key]");
            }
        }
        if (isset($search['space_name']) && $search['space_name'] !== '') {
            $query->whereLike('space_name', "{$search['space_name']}%");
        }
        $result = $this->model->where($query)->field('id,firm_id,space_name,phone,desc,status')->paginate(10);
        return $result;
    }

    public function getSpaceById(int $id, $firmId) {
        $result = $this->model->where(['id' => $id,'firm_id'=>$firmId])->field('id,firm_id,space_name,phone,desc,status')
                              ->findOrEmpty()->toArray();
        isEmptyInDb($result, '商户没有该场馆');
        return $result;
    }

    public function updateSpaceById(int $id, $firmId, array $data) {
        $space=$this->model->where('id',$id)->where('firm_id',$firmId)->findOrEmpty()->toArray();
        isEmptyInDb($space, '商户没有指定场馆!');

        $result = $this->model->save($data, ['id' => $id]);
        isModelFailed($result, '修改场馆信息失败!');
        return $this->model;
    }

    public function addSpace(array $data) {
        $data['space_appid']=$this->generateAppid();
        $result = $this->model->save($data);
        isModelFailed($result, '添加场馆失败');
        return $this->model;
    }

    public function delSpaceById(int $id, $firmId) {
        $space=$this->model->where('id',$id)->where('firm_id',$firmId)->findOrEmpty()->toArray();
        isEmptyInDb($space, '商户没有指定场馆!');

        $result = $this->model->destroy($id);
        isModelFailed($result, '删除场馆失败');
        return $result;
    }

    private function generateAppid() {
        $appid = md5(uniqid());
        while ($this->model->where('space_appid', $appid)->value('space_appid')) {
            $appid = md5(uniqid());
        }
        return $appid;
    }


}
