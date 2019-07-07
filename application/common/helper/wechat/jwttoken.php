<?php
namespace app\common\helper\wechat;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

trait jwttoken {

     protected static $key = 'abcdefg';

    public static function createToken($openid = ''){
         $token = array(
             'lat'=>time(),
             'nbf'=>time()+10,
             'exp'=>time()+3000,
             'wx_openid'=>$openid
         );
         $jwt = JWT::encode($token,self::$key);
         return $jwt;
    }

    public static function checkToken($token){
        try{
            $jwtAuth = JWT::decode($token, self::$key, array('HS256'));
            $authInfo = (array)$jwtAuth;
            if(empty($authInfo['wx_openid'])){
                return false;
            }else{
                return $authInfo;
            }
        }catch (SignatureInvalidException $e) {
            return false;
        } catch (ExpiredException $e){
            return false;
        }
    }

    public static function refreshToken($jwt)
    {
        $decoded = JWT::decode($jwt, self::$key, array('HS256'));
        $decoded_array = (array)$decoded;
        $jwt = self::createToken($decoded_array['wx_openid']);
        return $jwt;
    }
}
