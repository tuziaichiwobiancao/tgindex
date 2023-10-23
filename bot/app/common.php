<?php
if(!function_exists("get_system")){
    /*
     *
     * */
    function get_system(){
        echo 1;
    }
}

if(!function_exists("curl_request")) {
    /*
     * 发送CURL数据
     * @param string $url 接口地址
     * @param array $data 数据内容
     * @param string $method 请求类型
     * return string
     * */
    function curl_request($url, $data , $method = "POST")
    {
        if (is_array($data)) {
            //$data = json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        $curl = curl_init();
        //请求地址
        curl_setopt($curl, CURLOPT_URL, $url);
        //是否返回结果
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //请求超时设置（单位秒，0不做限制）
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10000);
        //https请求，（false为https）
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //请求方式POST
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        //参数设置
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        //执行
        $response = curl_exec($curl);
        //关闭
        curl_close($curl);
        //返回
        return $response;
    }
}

if(!function_exists("ejson")){
    /*
     * json输出
     * @param string $msg 消息提示
     * @param int $code 消息代码
     * @param array $data 数据
     * return string
     * */
    function ejson($msg,$code = 0,$data = null){
        $arr = ["code" => $code,"data"=>$data,"msg" => $msg,"time"=>time()];
        die(json_encode($arr,JSON_UNESCAPED_UNICODE));
    }
}

if(!function_exists("getSystemConfig")) {
    /*
     * 取出系统配置
     * @param string $name 数组关键词
     * return string
     * */
    function getSystemConfig($name)
    {
        try {
            //cache("system",null);
            if (cache("system") == null) {
                $systemInfo = $url = API_URL . "/system/getconfig.html";
                $resdata = file_get_contents($systemInfo);
                $res = json_decode($resdata, true);
                //缓存系统配置数据
                cache("system", $resdata, $res["data"]["cachetime"]);
            } else {
                $resdata = cache("system");
            }
            //设置缓存
            return json_decode($resdata, true)["data"][$name];
        } catch (Exception $e) {

        }
    }
}

if(!function_exists("getAdvInfo")) {
    /*
         * 取出广告
         * @param int $type 广告类型
         * @param int $number 取多少条
         * @param boolean $string 返回方式:false返回array,true返回string
         * return array or string
         * */
    function getAdvInfo($type = 1, $number = 1, $string = false)
    {
        try {
            if (cache("advinfo" . $type) == null) {
                $advInfo = $url = API_URL . "/adv/index.html";
                $res = curl_request($advInfo, ["advtype" => $type]);
                //缓存广告数据,尽可能少访问链接
                cache("advinfo" . $type, $res, (int)getSystemConfig("cachetime"));
            } else {
                $res = cache("advinfo" . $type);
            }
            $res = json_decode($res, true)["data"];
            $c = count($res);
            $resdata = $res[rand(0, $c - 1)];
            if ($string) {
                return "<a href='{$resdata["url"]}'>{$resdata["content"]}</a>";
            } else {
                return $resdata;
            }
        } catch (Exception $e) {

        }
    }
}

//获取不是采集的群组
if(!function_exists("getNoCj")){
    function getNoCj($page = 1)
    {
        try {
            $url = API_URL . "/group/getiscj.html?page=".$page;
            return json_decode(file_get_contents($url), true)["data"];
        } catch (Exception $e) {

        }
    }
}
//从群组池中获取一条未处理的群组进行处理
if(!function_exists("getNoHenl")){
    function getNohenl(){
        $url = API_URL."/group/gethenlno.html";
        return json_decode(file_get_contents($url),true)["data"];
    }
}

//aws文件上传
if(!function_exists("awsUpfile")){
    function awsUpfile(){
        set_time_limit(0);
        $credentials = new Aws\Credentials\Credentials("ak","sk");
        $s3 = new \Aws\S3\S3Client([
            "version" => "latest",
            "region" => 'ap-outheast-1',
            'credentials' => $credentials,
            'debug' => true,
        ]);
        $bucket = "text";
        $source = "文件路径";
        $upload = new \Aws\S3\MultipartUploader($s3,$source,[
            'bucket' => $bucket,
            'key' => '上传成功新文件名',
            'ACL' => 'public-read',
            'before_initiate' => function(\Aws\Command $command){
                $command["CacheControl"] = 'max-age=3600';
            },
        ]);
        try{
            $result = $upload->upload();
            $data = ['type' => 1,'data'=>urldecode($result["ObjectURL"])];
        }catch(\Aws\Exception\MultipartUploadException $e){
            $uploader = new \Aws\S3\MultipartUploader($s3,$source,['state'=>$e->getState()]);
            $data = ['type' =>0,'data'=>$e->getMessage()];
        }
        return $data;
    }
}

