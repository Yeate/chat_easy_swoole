<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/4/2
 * Time: 20:28
 */
namespace App\Utility;
use EasySwoole\Config;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Utility\Curl\Request;

class Tools{
    /**
     * 加解密
     * @param $data
     * @return string
     */
    public static function decryptWithOpenssl($data){
        $key = Config::getInstance()->getConf('ENCRYPT.key');
        $iv = Config::getInstance()->getConf('ENCRYPT.iv');
        return openssl_decrypt(base64_decode($data),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv);
    }

    public static function encryptWithOpenssl($data){
        $key = Config::getInstance()->getConf('ENCRYPT.key');
        $iv = Config::getInstance()->getConf('ENCRYPT.iv');
        return base64_encode(openssl_encrypt($data,"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));
    }


    /**构建会话加密函数，默认30天超时
     * @param $id
     * @param int $exptime
     * @return string
     */
    public static function sessionEncrypt($id, $exptime=2592000){
        $exptime = time() + $exptime;
        return self::encryptWithOpenssl($id.'|'.$exptime);
    }

    /**
     * 验证会话token是否有效
     * @param $raw
     * @return bool
     */
    public static function sessionCheckToken($raw){

        //如果解密不出文本返回失败
        if(!$data = self::decryptWithOpenssl($raw)){
            Logger::getInstance()->console('解密不出文本');
            return false;
        }

        Logger::getInstance()->console($data);
        $token = explode('|', $data);
        //如果分离出来的openid或者exptime为空 返回失败
        if(!isset($token[0]) || !isset($token[1])){
            Logger::getInstance()->console('分离不出openid exptime');
            return false;
        }
        //如果时间过期，返回失败
        if( $token[1] < time()){
            Logger::getInstance()->console('时间过期于：' . date('Y-m-d', $token[1] ));
            return false;
        }

        return true;
    }

    public static function shortUrl($url){
        $url     = urlencode($url);
        $app_key = Config::getInstance()->getConf('sina.SINA_APP_KEY');
        $api_url = Config::getInstance()->getConf('sina.SHORT_URL_API');
        $api_url = sprintf($api_url, $app_key, $url);

        $request = new Request($api_url);
        $response = $request->exec();
        $body = $response->getBody();
        if ($body){
            $res = json_decode($body, true);
            if ($res && is_array($res)){
                return $res[0]['url_short'] ?? $url;
            } else {
                return $url;
            }
        } else {
            return $url;
        }
    }

    public static function fen_to_yuan($amount){
        return floatval(bcdiv($amount, 100, 2));
    }

}