<?php
namespace App\Services;

use EasySwoole\Core\Component\Di;


class Error
{
    public static function show($errorMsg,$errorCode)
    {
        return json_encode(['success'=>false,'message'=>$errorMsg,'code'=>$errorCode]);
    }

    
}