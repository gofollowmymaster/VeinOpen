<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\manager\service;

use \app\manager\model\Firm as FirmModel;
use think\db\Query;

class Firm {

    private $model;

    public function __construct(FirmModel $model) {
        $this->model = $model;
    }

    public function searchFirms(array $search) {
        $query = new Query();

        foreach (['mail', 'phone','status'] as $key) {
            if ((isset($search[$key]) && $search[$key] !== '')) {
                $query->where($key, "$search[$key]");
            }
        }
        if (isset($search['firm_name']) && $search['firm_name'] !== '') {
            $query->whereLike('firm_name', "{$search['firm_name']}%");
        }
        $result = $this->model->where($query)->field('id,firm_name,mail,phone,desc,status')->select()
                              ->toArray();
        return $result;
    }

    public function getFirmById($id) {
        $result = $this->model->where(['id' => $id])->field('id,firm_name,mail,phone,desc,status')
                              ->findOrFail()->toArray();
        isEmptyInDb($result, '不存在的商家');
        return $result;
    }

    public function updateFirmById(int $id, array $data) {
        $result = $this->model->save($data, ['id' => $id]);
        isModelFailed($result, '修改商家信息失败!');
        return $this->model;
    }

    public function addFirm(array $data) {
        $data['appid']=$this->generateAppid();
        $result = $this->model->save($data);
        isModelFailed($result, '添加商家失败');
        return $this->model;
    }

    public function delFirmById(int $id) {
        $result = $this->model->destroy($id);
        isModelFailed($result, '删除商家失败');
        return $result;
    }

    private function generateAppid() {
        $appid = md5(uniqid());
        while ($this->model->where('appid', $appid)->value('appid')) {
            $appid = md5(uniqid());
        }
        return $appid;
    }


}
