<?php
namespace App\Utility;
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/4/4
 * Time: 21:11
 */
use App\Model\Coupon;
use App\Model\TaobaoUser;
use Carbon\Carbon;
use EasySwoole\Config;
use Exception;
use TopClient\request\TbkCouponGetRequest;
use TopClient\request\TbkDataReportRequest;
use TopClient\request\TbkItemCouponGetRequest;
use TopClient\request\TbkJuTqgGetRequest;
use TopClient\request\TbkPrivilegeGetRequest;
use TopClient\request\TbkScAdzoneCreateRequest;
use TopClient\request\TbkDgMaterialOptionalRequest;
use TopClient\request\TbkScNewuserOrderGetRequest;
use TopClient\request\TbkTpwdCreateRequest;
use TopClient\request\WirelessShareTpwdQueryRequest;
use TopClient\TopClient;
use TopClient\request\TbkItemInfoGetRequest;


class TopClientUtil {

    const SERVICE_KEY = 'top_client';

    protected $topClient;

    protected $app_key;

    protected $app_secret;

    protected $adzone_id;

    protected $format = 'json';

    function __construct()
    {
        $this->topClient = new TopClient();
        $taobaoConfig = Config::getInstance()->getConf('topclient');
        $this->config = $taobaoConfig;
        $this->appKey = $this->config['app_key'];
        $this->appSecret = $this->config['app_secret'];
        $this->adzone_id = $this->config['adzone_id'];
        $this->format = 'json';
        $this->topClient->appkey    = $this->appKey;
        $this->topClient->secretKey = $this->appSecret;
        $this->topClient->format    = $this->format;
        $this->report_client = new TopClient();
        $this->report_client->appkey    = '24549760';
        $this->report_client->secretKey = '35b7be0216f50253313d02eb3e93155b';
        $this->report_client->format    = $this->format;
    }

    public function setAppKey($appKey){
        $this->appKey = $appKey;
    }

    public function setAppSecret($appSecret){
        $this->appSecret = $appSecret;
    }

    /**
     * 获取淘宝商品信息（简版）
     * @param $item_id
     * @return bool
     */
    public function getTaobaoItemInfo($item_id)
    {
        $item_id=(string)$item_id;
        $req = new TbkItemInfoGetRequest();
        $req->setFields('num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick');
        $req->setPlatform(2);
        $req->setNumIids($item_id);
        try {
            $resp = $this->topClient->execute($req);
            $res  = json_decode(json_encode($resp), true);

            if (isset($res['results']) && isset($res['results']['n_tbk_item'])) {
                return $res['results']['n_tbk_item'][0];
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getTqgItems($pid, $start_time, $end_time, $page_size, $page){
        $req = new TbkJuTqgGetRequest();
        $pid_array = explode('_', $pid);
        if (count($pid_array) != 4) {
            throw new \Exception('PID格式有误！');
        }
        $req->setAdzoneId('17408035');
        $req->setFields("num_iid,click_url,pic_url,reserve_price,zk_final_price,total_amount,sold_num,title,category_name,start_time,end_time");
        $req->setStartTime($start_time);
        $req->setEndTime($end_time);
        $resp = $this->topClient->execute($req);
    }

    /**
     * 高佣金接口 返回优惠券信息 需要授权token
     * 需要授权
     * @param $item_id
     * @param $session_key
     * @param $pid
     * @return bool
     * @throws Exception
     */
    public function getTaobaoCouponInfo($item_id, $session_key, $pid, $me = '')
    {
        $pid_array = explode('_', $pid);
        if (count($pid_array) != 4) {
            throw new Exception('PID格式有误！');
        }
        $req = new TbkPrivilegeGetRequest();
        $req->setItemId("$item_id");
        if ($me){
            $req->setMe($me);
        }
        $req->setAdzoneId($pid_array[3]);
        $req->setPlatform(2);
        $req->setSiteId($pid_array[2]);
        try{
            $resp = $this->topClient->execute($req, $session_key);
        } catch (Exception $e){
            if (str_contains($e->getMessage(), 'Connection reset by peer')){
                $resp = $this->topClient->execute($req, $session_key);
            }
        }

        $res  = json_decode(json_encode($resp), true);
//        if (isset($res['code']) && ($res['code'] == 27 || $res['code'] == 26)){
//            //高佣授权失效
//            $taobaoUser = TaobaoUser::where('taobao_token', $session_key)->first();
//            $taobaoUser->token_expire_time = new Carbon();
//            $taobaoUser->save();
//            BearyChatRobot::notify('@沈洋 妈妈帮调用高佣接口失败了!!!,请检查高佣授权是否过期或掉线', $resp);
//        }
        if (isset($res['result']) && isset($res['result']['data'])) {
            $data = $res['result']['data'];
            if (isset($data['coupon_click_url'])){
                $this->dataReport($pid, $data['coupon_click_url'], $item_id);
            }
            return $data;
        }
        return $res;
    }

    /**
     * @param $pic
     * @param $title
     * @param $url
     * @return string
     */
    public function getTKL($pic, $title, $url, $pid)
    {
        $tkl = '';
        $req = new TbkTpwdCreateRequest;
        $req->setText($title);
        $req->setUrl($url);
        $req->setLogo($pic);
        $req->setExt("{}");
        $resp = $this->topClient->execute($req);
        $res = json_decode(json_encode($resp), true);
        $param = ['logo' => $pic, 'text' => $title, 'url' => $url];
        if (isset($res['msg']) && $res['msg'] != '') {
            $tkl = '';
        } else {
            if (isset($res['data'])) {
                $tkl = !empty($res['data']['model']) ? $res['data']['model'] : '';
            }
        }
        return $tkl;
    }

    public function searchCoupon($qstr, $pid, $page_size, $page){
        $page_size=(string)$page_size;
        $page=(string)$page;
        $pid_array = explode('_', $pid);
        if (count($pid_array) != 4) {
            throw new Exception('PID格式有误！');
        }
        $this->topClient->appkey    = $this->appKey;
        $this->topClient->secretKey = $this->appSecret;
        $this->topClient->format    = $this->format;
        $req = new TbkItemCouponGetRequest;
        $req->setPid($pid);
        $req->setPlatform(2);
        $req->setPageSize($page_size);
        $req->setQ($qstr);
        $req->setPageNo($page);
        $resp = $this->topClient->execute($req);
        $res = json_decode(json_encode($resp), true);
        if (isset($res['results']) && isset($res['results']['tbk_coupon'])){
            return $res['results']['tbk_coupon'];
        } else{
            return [];
        }
    }

    public function dataReport($pid, $url, $itemId){
        $req = new TbkDataReportRequest;
        $current_time = new Carbon();
        $dataId = $current_time->timestamp;
        $detail_url = "https://item.taobao.com/item.htm?id=$itemId";
        $coupon = Coupon::where('item_id', $itemId)->first();
        if ($coupon){
            $title = $coupon->item_title;
            $desc = $coupon->description;
        } else {
            $title = $itemId.'商品';
            $desc = $itemId.'商品描述';
        }
        $pid_array = explode('_', $pid);
        if (count($pid_array) != 4) {
            return [];
        }
        $account = $pid_array[1];
        $req->setData("dataId=$dataId|appkey=24549760|src=byn_bynbyn|srcName=必应鸟|pid=$pid|account=$account|time=$current_time|mediumType=导购分享|mediumName=省钱快报券多多|mediumId=33832617|memberNum=14196294|itemId=$itemId|originUrl=$detail_url|tbkUrl=$url|itemTitle=$title|itemDescription=$desc|tbCommand=|extraInfo=");
        $req->setType("1");
        $resp = $this->report_client->execute($req);
        return $resp;
    }

    public function queryTkl($tkl){
        $req = new WirelessShareTpwdQueryRequest;
        $req->setPasswordContent("$tkl");
        $resp = $this->topClient->execute($req);
        $res = json_decode(json_encode($resp), true);
        if ($res){
            return $res;
        }
    }

    public function analysistkl($tkl){
        if ($tkl){
            $res = $this->queryTkl($tkl);
            $title = isset($res['content']) ? $res['content'] : '';
            $itemId = 0;
            $url = $res['url'];
            preg_match('/\/i([\d]*)/', $url, $match);
            if (isset($match[1]) && $match[1]){
                $itemId = $match[1];
            }else{
                preg_match('/[\&|\?]id\=(\d*)/', $url, $match);
                if (isset($match[1]) && $match[1]){
                    $itemId = $match[1];
                }
            }
            return ['title' => $title, 'itemId' => $itemId];
        }
    }

    function unicode_decode($name){
        $json = '{"str":"'.$name.'"}';
        $arr = json_decode($json,true);
        if(empty($arr)) return '';
        return $arr['str'];
    }

    public function getNewUserOrders($pid, $sessionKey, $page, $pageSize){
        $pid_array = explode('_', $pid);
        if (count($pid_array) != 4) {
            throw new Exception('PID格式有误！');
        }
        $req = new TbkScNewuserOrderGetRequest();
        $req->setPageSize("$pageSize");
        $req->setAdzoneId($pid_array[3]);
        $req->setPageNo("$page");
        $req->setSiteId($pid_array[2]);
        $resp = $this->topClient->execute($req, $sessionKey);
        $res = json_decode(json_encode($resp), true);
        if (isset($res['results'])){
            return $res['results']['data'];
        }
        return [];
    }

    public function getCoupon($itemId, $me, $activityId){
        $req = new TbkCouponGetRequest();
        if ($itemId){
            $req->setItemId("$itemId");
        }
        if ($me){
            $req->setMe($me);
        }
        if ($activityId){
            $req->setActivityId($activityId);
        }
        $resp = $this->topClient->execute($req);
        $res = json_decode(json_encode($resp), true);
        if (isset($res['data']['coupon_amount'])){
            return $res['data'];
        } else {
            return [];
        }
    }

    public function createAdzone($siteId, $name, $sessionKey){
        $req = new TbkScAdzoneCreateRequest;
        $req->setSiteId("$siteId");
        $req->setAdzoneName($name);
        $resp = $this->topClient->execute($req, $sessionKey);
        $res = json_decode(json_encode($resp), true);
        if (isset($res['data']) && isset($res['data']['model'])){
            return $res['data']['model'];
        } else {
            BearyChatRobot::notify('@沈洋 创建推广位接口报错了!!! site_id['.$siteId.']', $resp);
            return [];
        }
    }

    public function superSearch($qstr, $pageSize, $pageNo, $filter, $sort){
        $req = new TbkDgMaterialOptionalRequest();
        $req->setQ($qstr);
        $req->setAdzoneId($this->adzone_id);
        $req->setPageNo("$pageNo");
        $req->setPageSize("$pageSize");
        if (isset($filter['start_dsr'])){
            $req->setStartDsr($filter['start_dsr']);
        }
        if (isset($filter['end_tk_rate'])){
            $req->setEndTkRate($filter['end_tk_rate']);
        }
        if (isset($filter['start_tk_rate'])){
            $req->setStartTkRate($filter['start_tk_rate']);
        }
        if (isset($filter['is_overseas'])){
            $req->setIsOverseas($filter['is_overseas']);
        }
        if (isset($filter['is_tmall'])){
            $req->setIsTmall($filter['is_tmall']);
        }
        if (isset($filter['has_coupon'])){
            $req->setHasCoupon($filter['has_coupon']);
        }
        if (isset($filter['end_price'])){
            $req->setEndPrice($filter['end_price']);
        }
        if (isset($filter['start_price'])){
            $req->setStartPrice($filter['start_price']);
        }
        if ($sort){
            $req->setSort($sort);
        }
        $resp = $this->topClient->execute($req);
        $res = json_decode(json_encode($resp), true);
        if (empty($res['result_list'])){
            return [];
        }
        return $res;
    }
}