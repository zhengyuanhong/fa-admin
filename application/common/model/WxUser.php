<?php

namespace app\common\model;

use think\Model;

/**
 * Class WxUser
 * @package app\common\model
 * @property  string id 自增id
 * @property  string username 用户名
 * @property  string avatar 头像
 * @property  string openid
 * @property  string updatetime 更新时间
 * @property  string createime 创建时间
 */

class WxUser extends Model
{
    protected $table = 'fa_wx_user';
    protected $autoWriteTimestamp = 'int';
}
