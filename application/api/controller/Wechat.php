<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\helper\wechat\jwttoken;
use app\common\model\WxUser;
use app\helper\wechat\wechatHelper;

class Wechat extends Api{

    use jwttoken;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    public function getOpenid(){
        $code = input('code');
        if(empty($code)){
            $this->error('fail','error param');
        }

        $appid = Config('mini.appid');
        $secret = Config('mini.secret');

        $data = wechatHelper::grantOpenID($code,$appid,$secret);
        $data['access-token'] = self::createToken();

        $user = WxUser::where('openid',$data['openid'])->find();
        if(empty($user)){
            $user = new WxUser;
            $user->openid = $data['openid'];
            $user->save();
        }

        $this->success('success',$data);
    }
}
