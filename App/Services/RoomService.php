<?php
namespace App\Services;

use EasySwoole\Core\Component\Di;


class RoomService
{
    /**
     * 获取Redis连接实例
     * @return object Redis
     */
    protected static function getRedis()
    {
        return Di::getInstance()->get('REDIS')->handler();
    }

    /**
     * 进入房间
     * @param  int    $roomId 房间id
     * @param  int    $userId userId
     * @param  int    $fd     连接id
     * @return
     */
    public static function joinRoom(int $roomId, int $fd)
    {
        $userId = self::getUserId($fd);
        self::getRedis()->zAdd('rfMap', $roomId, $fd);
        self::getRedis()->hSet("room:{$roomId}", $fd, $userId);
    }

    

  

    /**
     * 获取RoomId
     * @param  int    $fd
     * @return int    RoomId
     */
    public static function getRoomId(int $fd)
    {
        return self::getRedis()->zScore('rfMap', $fd);
    }

    /**
     * 获取room中全部fd
     * @param  int    $roomId roomId
     * @return array         房间中fd
     */
    public static function selectRoomFd(int $roomId)
    {
        return self::getRedis()->hKeys("room:{$roomId}");
    }

    /**
     * 退出room
     * @param  int    $roomId roomId
     * @param  int    $fd     fd
     * @return
     */
     public static function exitRoom(int $roomId, int $fd)
     {
         self::getRedis()->hDel("room:{$roomId}", $fd);
         self::getRedis()->zRem('rfMap', $fd);
     }

    // /**
    //  * 关闭连接
    //  * @param  string $fd 链接id
    //  */
    // public static function close(int $fd)
    // {
    //     // $roomId = self::getRoomId($fd);
    //     // self::exitRoom($roomId, $fd);
    //     self::getRedis()->zRem('online', $fd);
    // }
}