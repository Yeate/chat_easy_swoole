<?php
namespace App\Services;

use EasySwoole\Core\Component\Di;


class UserService
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
     * 登录
     * @param  int    $userId 用户id
     * @param  int    $fd     连接id
     * @return bool
     */
    public static function login(int $userId, int $fd)
    {
        self::getRedis()->zRemRangeByScore('online', $userId, $userId);
        self::getRedis()->zAdd('online', $userId, $fd);
        $a_user=UserRelation::where('a_user_id',$user_id)->pluck('b_user_id')->toArray();
        $b_user=UserRelation::where('b_user_id',$user_id)->pluck('a_user_id')->toArray();
        $user = array_merge($a_user,$b_user);
        if(!empty($user)){
            foreach ($user as $key => $value) {
                self::addUserToSet($userId."_frieds",$value);
            }
        }
    }

        /**
     * 添加好友到集合
     * @param  int    $userId 用户id
     * @param  int    $fd     连接id
     * @return bool
     */
    public static function addUserToSet(int $key, int $user_id)
    {
        self::getRedis()->sAdd($key,$user_id);
    }

    /**
     * 获取用户id
     * @param  int    $fd
     * @return int    userId
     */
    public static function getUserId(int $fd)
    {
        return self::getRedis()->zScore('online', $fd);
    }

    /**
     * 获取用户fd
     * @param  int    $userId
     * @return array         用户fd集
     */
    public static function getUserFd(int $userId)
    {
        return !empty(self::getRedis()->zRangeByScore('online', $userId, $userId))?self::getRedis()->zRangeByScore('online', $userId, $userId)['0']:0;
    }


    /**
     * 检查用户登录情况
     * @param  int    $userId
     * @return array         用户fd集
     */
    public static function checkLoginStatus(int $userId, int $fd)
    {
        $redis_fd = self::getRedis()->zRangeByScore('online', $userId, $userId);
        if(!empty($redis_fd) && !in_array($fd,$redis_fd)){
            return $redis_fd['0'];
        }else{
            return 0;
        }
    }

    

    /**
     * 关闭连接
     * @param  string $fd 链接id
     */
    public static function close(int $fd)
    {
        $user_id=self::getUserId($fd);
        self::getRedis()->delete($user_id."_frieds");
        self::getRedis()->zRem('online', $fd);

    }
}