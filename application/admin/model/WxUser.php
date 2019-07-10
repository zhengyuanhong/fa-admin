<?php

namespace app\admin\model;

use think\Model;


class WxUser extends Model
{

    

    //数据库
    protected $connection = 'database';
    // 表名
    protected $name = 'wx_user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'createdtime_text'
    ];
    

    



    public function getCreatedtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['createdtime']) ? $data['createdtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreatedtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
