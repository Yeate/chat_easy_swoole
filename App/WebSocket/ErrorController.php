<?php
namespace App\WebSocket;


use App\Services\Error;
use App\Services\Room;
use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Swoole\Task\TaskManager;

class ErrorController extends WebSocketController
{



    function main()
    {   
        $data=$this->request()->getArg('data');
        $msg=$data['msg'];
        $code=$data['code'];
        $this->response()->write(Error::show($msg,$code));

    }


    
}