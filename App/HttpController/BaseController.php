<?php

namespace App\HttpController;


use App\Model\ChatUser;
use App\Utility\Tools;
use EasySwoole\Core\Http\AbstractInterface\Controller;


class BaseController extends Controller{

    function index()
    {
        parent::index();
    }

    //用来返回错误信息（json）
    function error($code, $message)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                "success" => false,
                "code" => $code,
                "msg" => $message
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus(200);
            return true;
        } else {
            trigger_error("response has end");
            return false;
        }
    }

    function success($result = [], $message)
    {
        if (!$this->response()->isEndResponse()) {
            $data = Array(
                "success" => 'true',
                "data" => $result,
                'msg' => $message
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus(200);
            return true;
        } else {
            trigger_error("response has end");
            return false;
        }
    }


    /**
     * 获取登录用户
     * @return mixed
     */
    public function auth()
    {
        $token = $this->request()->getQueryParam('token');
        $id = Tools::decryptWithOpenssl($token);
        $user = ChatUser::find($id);
        return $user;
    }

    
}