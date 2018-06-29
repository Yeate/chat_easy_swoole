<?php
namespace App;

use EasySwoole\Config;
use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Message\Message;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDOException;

class ExceptionHandler implements ExceptionHandlerInterface
{
	private $response;

   
    
    public function handle( \Throwable $exception, Request $request, Response $response )
    {
        $this->response=$response;
        if ($exception instanceof ModelNotFoundException){
            return $this->message('数据不存在!', 200, 200);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->message('请求地址错误!', 404, 404);
        }
        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            $message = '';
            foreach ($errors as $key => $value){
                $message = $value[0];
                break;
            }
            return $this->message($message, 299, 200);
        }
        if ($exception instanceof AuthorizationException) {
            $message = '没有权限访问';
            return $this->message($message, 299, 200);
        }
        if ($exception instanceof UnauthorizedHttpException){
            $message = '请登录后继续操作';
            return $this->message($message, 101, 200);
        }
        if ($exception instanceof MethodNotAllowedHttpException){
            return $this->message('请求错误!', 405, 405);
        }

        $message = $exception->getMessage();
        return $this->message($message, $exception->getCode(), 200);
        
        if ($exception instanceof PDOException){
            if ($exception->getCode() == 2006){
                // 初始化数据库
                $dbConf = Config::getInstance()->getConf('database');
                $capsule = new Capsule;
                // 创建链接
                $capsule->addConnection($dbConf);
            }
        }
        return $this->message($exception->getMessage(),$exception->getCode());
    }


    public function message($message, $errorCode,$status)
    {
        $data = Array(
            "success" => false,
            "code" => $errorCode,
            "msg" => $message
        );
        $this->response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->response->withHeader('Content-type', 'application/json;charset=utf-8');
        $this->response->withStatus($status);
        return true;
    }
    
}