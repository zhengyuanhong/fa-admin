<?php
namespace app\common\helper\wechat;

use Firebase\JWT\JWT;
class jwttoken {
    public static function createToken(){
         $key = 'zhengyuanhong';

         $token = array(
             'lat'=>time(),
             'nbf'=>time()+10,
             'exp'=>time()+3000,
         );

         $jwt = JWT::encode($token,$key);
         return $jwt;
    }
}
