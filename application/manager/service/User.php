<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/12/4
 * Time: 17:32
 * description:描述
 */

namespace app\manager\service;

use \app\manager\model\User as UserModel;
use think\db\Query;

class User {

    private $model;

    public function __construct(UserModel $model) {
        $this->model = $model;
    }

    public function searchUsers(array $search) {
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
                            ->paginate(10);
        return $result;
    }
    public function getUserById($id){
        $result = $this->model->where(['id' => $id])->field('id,username,qq,mail,phone,firm_id,desc,authorize')->findOrEmpty()->toArray();
        isEmptyInDb($result, '不存在的用户');
        return $result;
    }

    public function updateUserById(int $id, array $data) {
        $result = $this->model->save($data, ['id' => $id]);

        isModelFailed($result, '修改管理员信息失败!');
        return $this->model;
    }

    public function addUser(array $data) {
        $result = $this->model->save($data);
        isModelFailed($result, '添加菜单失败');
        return $this->model;
    }

    public function delUserById(int $id) {
        if (UserModel::SUPERVISOR== $id) {
            throw new AuthException('非法操作！');
        }
        $result = $this->model->destroy($id);
        isModelFailed($result, '删除用户失败');
        return $result;
    }


}
