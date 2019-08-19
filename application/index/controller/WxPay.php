<?php
namespace app\index\controller;

use app\common\wxpay\wxPayApi;
use Exception;
use think\Env;

class WxPay{

    public function pay(){

        $trade_order_id = time();//商户网站内部ID，此处time()是演示数据
        $appid =config('wx_pay.appid');//  '201906121045';
        $appsecret = config('wx_pay.appsecret');
        $my_plugin_id = 'my-plugins-id';

        $data = array(
            'version' => '1.1',//固定值，api 版本，目前暂时是1.1
            'lang' => 'zh-cn', //必须的，zh-cn或en-us 或其他，根据语言显示页面
            'plugins' => $my_plugin_id,//必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
            'appid' => $appid, //必须的，APPID
            'trade_order_id' => $trade_order_id, //必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+
            'payment' => 'wechat',//必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
            'total_fee' => '0.1',//人民币，单位精确到分(测试账户只支持0.1元内付款)
            'title' => 'zhengyuanhong', //必须的，订单标题，长度32或以内
            'time' => time(),//必须的，当前时间戳，根据此字段判断订单请求是否已超时，防止第三方攻击服务器
            'notify_url' => Env::get('app.url').'/notify.php', //必须的，支付成功异步回调接口
            'return_url' => Env::get('app.url'),//必须的，支付成功后的跳转地址
            'callback_url' => Env::get('app.url'),//必须的，支付发起地址（未支付或支付失败，系统会会跳到这个地址让用户修改支付信息）
            'modal' => null, //可空，支付模式 ，可选值( full:返回完整的支付网页; qrcode:返回二维码; 空值:返回支付跳转链接)
            'nonce_str' => str_shuffle(time())//必须的，随机字符串，作用：1.避免服务器缓存，2.防止安全密钥被猜测出来
        );

        $hashkey = $appsecret;
        $data['hash'] = wxPayApi::generate_xh_hash($data, $hashkey);
        /**
         * 个人支付宝/微信官方支付，支付网关：https://api.xunhupay.com
         * 微信支付宝代收款，需提现，支付网关：https://pay.wordpressopen.com
         */
        $url = 'https://api.xunhupay.com/payment/do.html';

        try {
            $response = wxPayApi::http_post($url, json_encode($data));
            /**
             * 支付回调数据
             * @var array(
             *      order_id,//支付系统订单ID
             *      url//支付跳转地址
             *  )
             */
            $result = $response ? json_decode($response, true) : null;
            if (!$result) {
                throw new Exception('Internal server error', 500);
            }

            $hash = wxPayApi::generate_xh_hash($result, $hashkey);
            if (!isset($result['hash']) || $hash != $result['hash']) {
                throw new Exception(__('Invalid sign!', XH_Wechat_Payment), 40029);
            }

            if ($result['errcode'] != 0) {
                throw new Exception($result['errmsg'], $result['errcode']);
            }

            $pay_url = $result['url'];
            header("Location: $pay_url");
            exit;
        } catch (Exception $e) {
            echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
            //TODO:处理支付调用异常的情况
        }
    }

    public function notify(){

        $appid =config('wx_pay.appid');//  '201906121045';
        $appsecret = config('wx_pay.appsecret');
        $my_plugin_id       = 'my-plugins-id';

        $data = $_POST;
        foreach ($data as $k=>$v){
            $data[$k] = stripslashes($v);
        }
        if(!isset($data['hash'])||!isset($data['trade_order_id'])){
            echo 'failed';exit;
        }

//自定义插件ID,请与支付请求时一致
        if(isset($data['plugins'])&&$data['plugins']!=$my_plugin_id){
            echo 'failed';exit;
        }

//APP SECRET
        $appkey =$appsecret;
        $hash =wxPayApi::generate_xh_hash($data,$appkey);
        if($data['hash']!=$hash){
            //签名验证失败
            echo 'failed';exit;
        }

//商户订单ID
        $trade_order_id =$data['trade_order_id'];

        if($data['status']=='OD'){
            /************商户业务处理******************/
            //TODO:此处处理订单业务逻辑,支付平台会多次调用本接口(防止网络异常导致回调失败等情况)
            //     请避免订单被二次更新而导致业务异常！！！
            //     if(订单未处理){
            //         处理订单....
            //      }

            //....
            //...
            /*************商户业务处理 END*****************/
        }else{
            //处理未支付的情况
        }

//以下是处理成功后输出，当支付平台接收到此消息后，将不再重复回调当前接口
        echo 'success';
        exit;
    }
}
