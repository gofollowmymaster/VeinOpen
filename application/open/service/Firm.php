<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\open\service;

use \app\open\model\Firm as FirmModel;
use think\db\Query;

class Firm {

    private $model;

    public function __construct(FirmModel $model) {
        $this->model = $model;
    }

    public function searchFirms(array $search) {
        $query = new Query();

        foreach (['username', 'phone'] as $key) {
            if ((isset($search[$key]) && $search[$key] !== '')) {
                $query->whereLike($key, "%{$search[$key]}%");
            }
        }
        if (isset($search['firm_id'])&& $search['firm_id']!=='') {
            $query->where('firm_id', $search['firm_id']);
        }
        $result=$this->model->where($query)
                            ->field('id,firm_id,username,password,mail,phone,desc,status')
                            ->select()->toArray();
        return $result;
    }
    public function getFirmById($id){
        $result = $this->model->where(['id' => $id])->field('id,username,qq,mail,phone,firm_id,desc,authorize')->findOrFail()->toArray();
        isEmptyInDb($result, '不存在的用户');
        return $result;
    }

    public function updateFirmById(int $id, array $data) {
        $result = $this->model->save($data, ['id' => $id]);
        isModelFailed($result, '修改管理员信息失败!');
        return $this->model;
    }

    public function addFirm(array $data) {
        $result = $this->model->save($data);
        isModelFailed($result, '添加菜单失败');
        return $this->model;
    }

    public function delFirmById(int $id) {
        if (FirmModel::SUPERVISOR== $id) {
            throw new AuthException('非法操作！');
        }
        $result = $this->model->destroy($id);
        isModelFailed($result, '删除用户失败');
        return $result;
    }


}
