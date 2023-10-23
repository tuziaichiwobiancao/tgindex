<?php

namespace addons\invite;    // 注意命名空间规范
class Common
{
    public function getKeyList($page){
        $url = API_URL."/keyword/index.html?page=".$page;
        $res = curl_request($url,["number"=>10]);
        $resData = json_decode($res,true)["data"];
        return $resData;
    }
}