<?php
namespace App\Utility;
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/4/3
 * Time: 11:01
 */
use OpenSearch\Client\OpenSearchClient;
use EasySwoole\Config;

class OpensearchUtil {

    public static function getConnect(){
        $accessKeyId = Config::getInstance()->getConf('OPENSEARCH.OPENSEARCH_ACCESS_KEY');
        $secret = Config::getInstance()->getConf('OPENSEARCH.OPENSEARCH_ACCESS_SECRET');
        $endPoint = Config::getInstance()->getConf('OPENSEARCH.OPENSEARCH_HOST');
        $options = array('debug' => Config::getInstance()->getConf('OPENSEARCH.DEBUG'));
        $client = new OpenSearchClient($accessKeyId, $secret, $endPoint, $options);
        return $client;
    }
}