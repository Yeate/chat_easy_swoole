<?php
namespace App\Utility;

use GuzzleHttp\Client;
use EasySwoole\Config;

class BearyChatRobot
{
    public static function notify($title, $content)
    {
        $bearyChatHock = Config::getInstance()->getConf('BEARYCHAT_HOOK');
        if (!$bearyChatHock){
            return;
        }

        $client = new Client();

        $data                   = [];
        $data['text']           = $title;
        $data['attachments'][]  = ['text' => $content];

        $client->request('POST', $bearyChatHock, [
            'form_params' => ['payload' => json_encode($data)]
        ]);
    }
}