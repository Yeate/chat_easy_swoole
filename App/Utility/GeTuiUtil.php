<?php

namespace App\Utility;

use App\Model\GeTuiSendHistory;


class GeTuiUtil
{
    /**
     * [个推发送接口]
     * @param [type] $data      [array('title'=>'','content'=>'')]
     * @param [type] $CID       [CID]
     * @param [type] $appUserId [appUserId]
     * @param string $template [默认即可]
     * @param string $payload [默认即可]
     */
    public static function SendMessage($data, $cid, $appUserId, $template = 'IGtTransmissionTemplate', $payload = '')
    {
        if (!empty($payload)) {
            $payloads['type'] = 2;
        } else {
            $payloads['type'] = 1;
        }
        if (isset($data['title']) && isset($data['content']) && !empty($cid)) {
            $data_arr['title'] = $data['title'];
            $data_arr['content'] = $data['content'];
            $data_arr['payload'] = $payloads;
            $data_json = json_encode($data_arr);
            $config = array("type" => "HIGH", "title" => $data['title'], "body" => $data['content']);
            $geTuiService=new GeTuiService();
            $result = $geTuiService->pushMessageToSingle($template, $config, $data_json, $cid, json_encode($payloads));
            if (isset($result['result']) && $result['result'] == "TokenMD5NoUsers") {
                $geTuiSendHistory = new GeTuiSendHistory();
                $geTuiSendHistory->app_user_id = $appUserId;
                $geTuiSendHistory->task_id = 0;
                $geTuiSendHistory->content = 0;
                $geTuiSendHistory->status = 'TokenMD5NoUsers';
            } else {
                $geTuiSendHistory = new GeTuiSendHistory();
                $geTuiSendHistory->app_user_id = $appUserId;
                $geTuiSendHistory->task_id = $result['taskId'];
                $geTuiSendHistory->content = $data['content'];
                $geTuiSendHistory->status = $result['status'];
            }

            $geTuiSendHistory->save();
        }
    }
}