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
use Overtrue\EasySms\EasySms;

class SmsUtil
{
    /**
     * 校验短信验证码是否正确
     * @param $phone
     * @param $smsCode
     */
    public static function smsCodeCheck($phone, $smsCode)
    {

    }

    /**
     * 发短信
     * @param $phone
     * @param $message
     */
    public static function sendSmsMessage($phone, $param, $templateId){
        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                    'yunpian',
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/tmp/easy-sms.log',
                ],
                'yunpian' => [
                    'api_key' => '7eb0b89d61d80f15708ea72ab16fb817',
                ],
            ],
        ];

        switch ($templateId){
            case 1:
                $content  = "【券好赚】您的验证码是$param";
                $template = '2057556';
                $data     = ['code' => $param];
                break;
            case 2:
                $content  = "【想买清单】您的验证码是$param";
                $template = '2205368';
                $data     = ['code' => $param];
                break;
            default:
                $content  = "";
                $template = '';
                $data     = [];
        }

        $easySms = new EasySms($config);
        try{
            $easySms->send($phone, [
                'content'  => $content,
                'template' => $template,
                'data' => $data
            ]);
        } catch (\Exception $e){
            Logger::getInstance()->console($e->getMessage());
        }

    }
}