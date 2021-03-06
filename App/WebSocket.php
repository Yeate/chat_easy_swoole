<?php
namespace App;

use App\Socket\Controller\WebSocket\Index;
use App\WebSocket\ErrorController;
use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Core\Socket\Common\CommandBean;

class WebSocket implements ParserInterface
{

    public static function decode($raw, $client)
    {
        //检查数据是否为JSON
        $commandLine = json_decode($raw, true);
        if (!is_array($commandLine)) {
            return 'unknown command';
        }
        $CommandBean = new CommandBean();
        $control = isset($commandLine['controller']) ? 'App\\WebSocket\\'. ucfirst($commandLine['controller']) : '';
        $action = $commandLine['action'] ?? 'none';
        $data = $commandLine['data'] ?? null;
        $token = $commandLine['token'] ?? null;
        // dd(user_auth($token));
        if(!empty(user_auth($token))){
            //找不到类时访问默认Index类
            $data['token']=$token;
            $CommandBean->setControllerClass(class_exists($control) ? $control : Index::class);
            $CommandBean->setAction(class_exists($control) ? $action : 'controllerNotFound');
            $CommandBean->setArg('data', $data);
            return $CommandBean;
        }else{
            $CommandBean->setControllerClass(ErrorController::class);
            $CommandBean->setAction('main');
            $CommandBean->setArg('data', ['msg'=>'用户未登录','code'=>91]);
            return $CommandBean;
        }

    }

    public static function encode(string $raw, $client): ?string
    {
        // TODO: Implement encode() method.
        return $raw;
    }
}