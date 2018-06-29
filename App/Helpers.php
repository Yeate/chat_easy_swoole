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



