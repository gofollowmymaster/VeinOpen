<?php
/**
 * Created by PhpStorm.
 * User: zh
 * Date: 2019/11/12
 * Time: 9:38
 */

namespace app\common\traits;



use app\common\exception\WarringException;

Trait DataSetTrait {

    private $data;

    private function setData(array $data) {
        if ($this->data) {
            throw new WarringException('非法操作!' . $this->description . '信息已存在');
        }
        $this->data = $data;
    }

    private function getData() {
        if (!$this->data) {
            throw new WarringException('没找到' . $this->description . '信息');
        }
        return $this->dataFilter();
    }

    private function dataFilter() {
        $readAbleField = $this->readAbleField;
        $data = array_filter($this->data, function ($item) use ($readAbleField) {
            return in_array($item, $readAbleField);
        }, ARRAY_FILTER_USE_KEY);
        return (object)$data;
    }

    public function __set($name, $value) {
        if (in_array($name, $this->readAbleField) && (in_array($name, $this->modifiableField) || !$this->data[$name])) {
            return $this->data[$name] = $value;
        }
        throw new WarringException('参数' . $name . '不可修改');
    }

    public function __get($name) {
        if (in_array($name, $this->readAbleField)) {
            return $this->data[$name];
        }
        throw new WarringException('不存在的参数:' . $name);
    }

    public function __toString() {
        return json_encode($this->data);
    }

    public function toArray() {
        return $this->dataFilter();
    }

}
