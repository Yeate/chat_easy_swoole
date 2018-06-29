<?php
namespace App\WebSocket;


use App\Model\UserMessage;
use App\Model\UserRelation;
use App\Services\UserService;
use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Swoole\Task\TaskManager;

class User extends WebSocketController
{



    public function login(){
        $user = user_auth($this->request()->getArg('data')['token']);
        $user_id=$user->id;
        $fd = $this->client()->getFd();
        $fdExists=UserService::checkLoginStatus($user_id,$fd);
        if(!empty($fdExists)){
            $message = getSocketMsg(1,['message'=>'账号在另一处登录'],false);
            $this->_pushUser($fdExists, $message);
            
        }
        UserService::login($user_id, $fd);
        $message = getSocketMsg(0,['message'=>'登录成功'],true);
        $this->response()->write(json_encode($message));
    }



    public function addFriend(){
        $checkLogin = $this->_checkLogin();
        if(is_array($checkLogin)){
            $data=$this->request()->getArg('data');
            $beAdd = $data['be_add_user_id'];
            $user_id=$checkLogin['user_id'];
            $userRelation=UserRelation::isFriend($beAdd,$user_id)->first();
            if(!empty($userRelation)){
                $message = getSocketMsg(0,['message'=>'对方已经是你的好友了'],false);
                $this->response()->write(json_encode($message));
            }else{
                $userRelation=UserMessage::isSendAdd($beAdd,$user_id)->first();
                if(!empty($userRelation)){
                    $message = getSocketMsg(0,['message'=>'已经提交过加友申请'],false);
                    $this->response()->write(json_encode($message));
                }else{
                    $userMessage=UserMessage::create(['from_user_id'=>$user_id,'to_user_id'=>$beAdd,'msg'=>isset($data['msg'])?$data['msg']:'','type'=>2,'status'=>0]);
                    $message = getSocketMsg(0,['message'=>'好友申请成功'],true);
                    $this->_sendMsgToUser($beAdd,$userMessage);
                    $this->response()->write(json_encode($message));

                }
            }

        }
    }

    //待测
    public function addFriendPass(){
        $checkLogin = $this->_checkLogin();
        if(is_array($checkLogin)){
            $userMessage=UserMessage::where('to_user_id',$checkLogin['user_id'])->where('id',$data['id'])->first();
            $userMessage->update(['status'=>1]);
            UserService::addUserToSet($userMessage->from_user_id."_frieds",$userMessage->to_user_id);
            UserService::addUserToSet($userMessage->to_user_id."_frieds",$userMessage->from_user_id);
            UserRelation::create(['a_user_id'=>$userMessage->from_user_id,'b_user_id'=>$userMessage->to_user_id]);

        }
    }
    //待测
    public function addFriendDePass(){
        $checkLogin = $this->_checkLogin();
        if(is_array($checkLogin)){
            $data=$this->request()->getArg('data');
            UserMessage::where('to_user_id',$checkLogin['user_id'])->where('id',$data['id'])->update(['status'=>2]);
            $message = getSocketMsg(0,['message'=>'拒绝成功'],true);
            $this->response()->write(json_encode($message));

        }
    }

    private function _sendMsgToUser($beAdd,$userMessage){
        $fd = UserService::getUserFd($beAdd);
        if(!empty($fd)){
            $message = getSocketMsg(2,['msg'=>$userMessage->toArray()],true);
            $this->_pushUser($fd,$message);
        }
    }

    private function _checkLogin(){
        $user = user_auth($this->request()->getArg('data')['token']);
        $user_id=$user->id;
        $fd = $this->client()->getFd();
        $fdExists=UserService::checkLoginStatus($user_id,$fd);
        if(!empty($fdExists)){
            $message = getSocketMsg(0,['message'=>'账户未登录'],false);
            $this->response()->write(json_encode($message));
            return 0;
        }
        return ['user_id'=>$user_id,'fd'=>$fd];
    }

    private function _pushUser($fd,$msg){
        ServerManager::getInstance()->getServer()->push($fd,$msg);
    }

    // function hello()
    // {   

    //     $this->response()->write('call hello with arg:'.user_auth($this->request()->getArg('data')['token']));

    // }

    // public function who(){
    //     $this->response()->write('your fd is '.$this->client()->getFd());
    // }

    // public function index()
    // {
    //     $this->response()->write(Room::testSet());
    //     $this->response()->write("\n");
    //     $this->response()->write(Room::testGet());
    // }

    // function sendToUser()
    // {
    //     $param = $this->request()->getArg('data');
    //     $user=user_auth($param['token']);
    //     $fd=$param['fd'];
    //     $message=$param['message'];
    //     if(!empty($user)){
    //         TaskManager::async(function ()use($fd, $message){
    //             ServerManager::getInstance()->getServer()->push($fd, $message);
                
    //         });
    //         $data=json_encode(['success'=>true,'message'=>'发送成功','code'=>1]);
    //             $this->response()->write($data);
    //     }else{
    //         $data=json_encode(['success'=>false,'message'=>'用户未登录','code'=>2]);
    //         $this->response()->write($data);
            
    //     }

    //     //测试异步推送
        
    // }

    /**
     * 关闭连接
     * @param  string $fd 链接id
     */
    function close(int $fd)
    {
        $this->response()->write($data);
    }
}