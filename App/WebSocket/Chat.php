<?php
namespace App\WebSocket;


use App\Services\Room;
use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Swoole\Task\TaskManager;

class Chat extends WebSocketController
{

    //    function actionNotFound(?string $actionName)
    // {
    //     $this->response()->write("action call {$actionName} not found");
    // }

    function hello()
    {   

        $this->response()->write('call hello with arg:'.user_auth($this->request()->getArg('data')['token']));

    }

    public function who(){
        $this->response()->write('your fd is '.$this->client()->getFd());
    }

    public function index()
    {
        $this->response()->write(Room::testSet());
        $this->response()->write("\n");
        $this->response()->write(Room::testGet());
    }

    function sendToUser()
    {
        $param = $this->request()->getArg('data');
        $user=user_auth($param['token']);
        $fd=$param['fd'];
        $message=$param['message'];
        if(!empty($user)){
            TaskManager::async(function ()use($fd, $message){
                ServerManager::getInstance()->getServer()->push($fd, $message);
                
            });
            $data=json_encode(['success'=>true,'message'=>'发送成功','code'=>1]);
                $this->response()->write($data);
        }else{
            $data=json_encode(['success'=>false,'message'=>'用户未登录','code'=>2]);
            $this->response()->write($data);
            
        }

        //测试异步推送
        
    }

    /**
     * 关闭连接
     * @param  string $fd 链接id
     */
    function close(int $fd)
    {
        $this->response()->write($data);
    }
}