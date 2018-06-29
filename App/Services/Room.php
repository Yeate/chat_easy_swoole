<?php
namespace App\Services;

use EasySwoole\Core\Component\Di;


class Room
{
    public static function getRedis()
    {
        return Di::getInstance()->get('REDIS')->handler();
    }

    public static function testSet()
    {
        return self::getRedis()->set('test', '这是一个测试');
    }

    public static function testGet()
    {
        return self::getRedis()->get('test');
    }
}