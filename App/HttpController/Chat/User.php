<?php

namespace App\HttpController\Chat;

use App\Error\ErrorCode;
use App\HttpController\BaseController;
use App\Model\ChatUser;
use App\Utility\Tools;
use Carbon\Carbon;
use EasySwoole\Core\Http\AbstractInterface\Controller;
use EasySwoole\Core\Swoole\ServerManager;
/**
 * Class Index
 * @package App\HttpController
 */
class User extends BaseController
{
    public function findUser(){
        $name = $this->request()->getRequestParam('name');
        $user = $this->auth();
        if(!empty($user)){
            if(!empty($name)){
                $chatUser=ChatUser::where('name','like','%'.$name.'%')->page(20, $this->request());
                return $this->success($chatUser,'查找成功');
            }else{
                throw new \Exception('参数错误', ErrorCode::PARAM_INVITED);
            }
        }else{
            throw new \Exception("用户未登录", 1);
            
        }

        
    }

    public function register(){
        if ($this->request()->getMethod() == 'GET') {
            return $this->error(ErrorCode::REQUEST_INVITED, '请求不合法');
        }
    
        $data['name'] = $this->request()->getRequestParam('name');
        $data['email'] = $this->request()->getRequestParam('email');
        $data['nickname'] = $this->request()->getRequestParam('nickname');
        $data['password'] = bcrypt($this->request()->getRequestParam('password'));
        $this->_checkName($data['name']);
        $this->_checkEmail($data['email']);
        if(empty($data['nickname'])){
            $data['nickname'] = $data['name'];
        }
        $data = array_filter($data);
        if(count($data) != 4){
            throw new \Exception("参数错误", 1);
            
        }
        $result=ChatUser::create($data);
        return $this->success('','注册成功');
    }



    /**
     * 登录
     * @return bool
     * @throws \Exception
     */
    public function login()
    {
        $account = $this->request()->getRequestParam('account');
        $loginType = $this->request()->getRequestParam('login_type') ?? 1;
        $password = $this->request()->getRequestParam('password');
        
        if(!empty($password)){
            if(!empty($account)){
                switch ($loginType) {
                    case 1:
                        //手机号码 或者用户名 + 密码登录
                        $chatUser=ChatUser::where('name',$account)->first()
                        ;
                        if (!empty($chatUser) && password_verify($password, $chatUser->password)) {
                            //密码正确,返回token
                            $res = [
                                'chatUser' => $chatUser,
                                'login_token' => Tools::encryptWithOpenssl($chatUser->id),
                            ];
                            $this->_updateLoginTime($chatUser);
                            return $this->success($res, '登陆成功！');
                        } else {
                            throw new \Exception('账号或者密码不正确', ErrorCode::NORMAL_ERROR);
                        }
                        break;
                    case 2:
                        $chatUser=ChatUser::where('email',$account)->first();
                        if (bcrypt($password)==$chatUser->password) {
                            //密码正确,返回token
                            $res = [
                                'chatUser' => $chatUser,
                                'login_token' => Tools::encryptWithOpenssl($chatUser->id),
                            ];
                            $this->_updateLoginTime($chatUser);
                            return $this->success($res, '登陆成功！');
                        } else {
                            throw new \Exception('邮箱或者密码不正确', ErrorCode::NORMAL_ERROR);
                        }
                    default:
                        throw new \Exception('参数有误', ErrorCode::PARAM_INVITED);
                }
            }else{
                throw new \Exception("用户名不能为空", 2);
            }
        }else{
            throw new \Exception("密码不能为空", 2);
            
        }
        
    }

    private function _updateLoginTime($chatUser){
        $chatUser->last_login=Carbon::now()->toDateTimeString();
        $chatUser->save();

    }


    private function _checkName($name){
        if(!empty($name)){
            $chatUser=ChatUser::where('name',$name)->first();
            if(!empty($chatUser)){
                throw new \Exception("用户名已存在", 3);
            }
        }else{
            throw new \Exception("用户名不能为空", 2);
            
        }

    }

    private function _checkEmail($email){
        if(!empty($email)){
            $chatUser=ChatUser::where('email',$email)->first();
            if(!empty($chatUser)){
                throw new \Exception("邮箱已存在", 3);
            }
        }else{
            throw new \Exception("邮箱不能为空", 2);
            
        }
    }
}