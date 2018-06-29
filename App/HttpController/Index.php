<?php

namespace App\HttpController;

use EasySwoole\Core\Http\AbstractInterface\Controller;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Class Index
 * @package App\HttpController
 */
class Index extends Controller
{
    /**
     * 首页方法
     * @author : evalor <master@evalor.cn>
     */
    function index()
    {
        DB::schema()->create('chat_users', function ($table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('nickname');
            $table->string('password');
            $table->timestamp('last_login');
            $table->timestamps();
        });
    }
}