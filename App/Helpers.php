<?php

use App\Model\ChatUser;
use App\Utility\Tools;


function ddd(...$args)
{
    http_response_code(500);
    call_user_func_array('dd', $args);
}


if (!function_exists('bcrypt')) {
    function bcrypt($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

if (!function_exists('user_auth')) {
    function user_auth($token)
    {

        $id = Tools::decryptWithOpenssl($token);
        $user = ChatUser::find($id);
        return $user;
    }
}

if (!function_exists('getSocketMsg')) {
    /**
     * [getSocketMsg description]
     * @param  [type] $type    [description]
     * @param  [type] $data    [description]
     * @param  [type] $success [description]
     * @return [type]          [description]
     */
    function getSocketMsg($type,$data,$success)
    {
        $msg=['success'=>$success,'type'=>$type];
        switch ($type) {
            case '1':
                // 弹窗通知
                $msg=['success'=>$success,'type'=>$type,'message'=>$data['message']];
                break;
            case '0':
                // 弹窗通知
                $msg=['success'=>$success,'type'=>$type,'message'=>$data['message']];
                break;
            case '2':
                // 加好友消息
                $msg=['success'=>$success,'type'=>$type,'data'=>$data];
                break;
            default:
                # code...
                break;
        }
        return json_encode($msg);
        
    }
}



