<?php


namespace app\controller;


use TelegramBot\Api\Types\ForceReply;
use think\Db;

class Funpages extends Base
{
    private $key;
    private $userid;
    private $bot;
    public function __construct($key,$bot,$userid)
    {
        $this->key = $key;
        $this->userid = $userid;
        $this->bot = $bot;
    }

    public function start(){
        $text = "";
        $button = [];
        $lang = $this->getLang();
        $key = explode("_",$this->key);
        switch($key[0]){
            case "advtypelist":
                $advtypeList = $this->getAdvTypeList()["data"];
                $c = count($advtypeList);
                for($i = 0;$i<$c;$i++){
                    $arra[$i] = [["text"=>$advtypeList[$i]["name"],"callback_data"=>"advtype_".$advtypeList[$i]["advtype_id"]]];
                }
                $arra[$i] = [
                    ["text"=>$this->getSystemConfig("myadv"),"callback_data"=>"myadv"],
                    ["text"=>$this->getSystemConfig("retnrunuppage"),"callback_data"=>"start"],
                ];
                $text = "";
                $arr = $arra;
                break;
            case "advtype":
                $res = $this->getAdvTypeInfo($key[1]);
                $text = $this->getSystemConfig("advtypetext").": ".$res["name"]."\n";
                $text = $text . $this->getSystemConfig("advtypemoney").": ".$res["money"].$lang["USDTm"]." \n\n";
                $text = $text . $res["tisp"];
                $button =  [
                    [
                        ['text' => $this->getSystemConfig("buyadv"), "callback_data" => "buy_".$key[1]],
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "advtypelist"],
                    ]
                ];
                $arr = $button;
                break;
                //购买广告
            case "buy":
                $res = $this->getAdvTypeInfo($key[1]);
                $arr = [
                    "advtype_id" => $res["advtype_id"]
                ];
                $data = $this->buyadv($res["money"],$res["advtype_id"],$this->userid);
                    $text = $data["msg"];
                    $button = $arr = [
                        [
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "advtypelist"],
                        ]
                    ];
                $arr = $button;
                break;
                //我的广告按钮
            case "myadv":
                $advInfo = $this->getMyAdv($this->userid);
                $c = count($advInfo);
                for($i = 0;$i<$c;$i++){
                    $arr[$i] = [["text"=>$advInfo[$i]["content"],"callback_data"=>"advinfo_".$advInfo[$i]["adv_id"]]];
                }
                $arr[$i] = [
                    ["text"=>$this->getSystemConfig("retnrunuppage"),"callback_data"=>"advtypelist"],
                ];
                break;
            case "advinfo":
                $adv = $this->getAdvInfoCon($key[1]);
                $text = $this->getSystemConfig("advcontent").": ".$adv["content"]."\n";
                $text = $text . $this->getSystemConfig("linkadd").": ".$adv["url"]."\n";
                $text = $text . $this->getSystemConfig("endtime").": ".$adv["endtime"]."\n";
                $text = $text . $this->getSystemConfig("advtypename").": ".$adv["name"]."\n";
                $text = $text . $this->getSystemConfig("buymoney").": ".$adv["money"]." USDT/月\n";
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("renewaladv"), "callback_data" => "gopay_".$adv["adv_id"]],
                        ['text' => $this->getSystemConfig("editadv"), "callback_data" => "editadv_".$adv["adv_id"]],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "advtypelist"],
                    ]
                ];
                break;
            case "editadv":
                $adv = $this->getAdvInfoCon($key[1]);
                $text = $this->getSystemConfig("actioneditadv").":\n";
                $text = $text . $this->getSystemConfig("advcontent").": ".$adv["content"]."\n";
                $text = $text . $this->getSystemConfig("linkadd").": ".$adv["url"]."\n";
                $text = $text . $this->getSystemConfig("endtime").": ".$adv["endtime"]."\n";
                $text = $text . $this->getSystemConfig("advtypename").": ".$adv["name"]."\n";
                $text = $text . $this->getSystemConfig("buymoney").": ".$adv["money"]."\n\n";
                $text = $text . $this->getSystemConfig("clickcopy").":`".$this->getSystemConfig("copycontent")."`"."\n";
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("renewaladv"), "callback_data" => "gopay_".$adv["adv_id"]],
                        ['text' => $this->getSystemConfig("editadv"), "callback_data" => "editadv_".$adv["adv_id"]],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "advtypelist"],
                    ]
                ];
                $this->bot->sendMessage($this->userid,$this->getSystemConfig("plaseadvcontent")."_".$adv["adv_id"], "HTML",true,null,new ForceReply(true));
                break;
            case "member":
                $member = $this->getMemberInfo($this->userid);
                $string = $this->getSystemConfig("membertop")."\n\n";
                $string = $string."ID: `".$member["tg_id"]."`\n";
                $string = $string.$this->getSystemConfig("memberusername").": @".$member["username"]."\n";
                $string = $string.$this->getSystemConfig("membermoney").": ".$member["money"]."  USDT\n";
                $string = $string.$this->getSystemConfig("membercoin").": ".$member["coin"]." \n\n";
                $string = $string."TRC20收款地址: ".$member["utoken"]." \n\n";
                $string = $string.$this->getSystemConfig("memberdhbl").":  1:".$this->getSystemConfig("bili")." \n\n";
                $string = $string.$this->getSystemConfig("memberlink").": `https://t.me/".$this->getSystemConfig("botusername")."?start=".$this->userid."`\n";
                $text = $string;
                $arr = [
                    [
                        //充值
                        ['text' => $this->getSystemConfig("memberrech"), "callback_data" => "recharge"],
                        //提现
                        ['text' => $this->getSystemConfig("membertx"), "callback_data" => "cash"],
                        //兑换
                        ['text' => $this->getSystemConfig("memberdh"), "callback_data" => "duihuan"],
                    ],
                    [
                        //充值记录
                        ['text' => $this->getSystemConfig("memberyemx"), "callback_data" => "memberyemx"],
                        //积分明细
                        ['text' => $this->getSystemConfig("memberjfmx"), "callback_data" => "jfmx"],
                        //账户安全
                        ['text' => $this->getSystemConfig("membersafe"), "callback_data" => "membersafe"],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "start"],
                    ]
                ];
                break;
            case "recharge":
                $arr = [
                    [
                        //充值
                        ['text' => "100 USDT", "callback_data" => "rech_100"],
                        //提现
                        ['text' => "200 USDT", "callback_data" => "rech_200"],
                        //兑换
                        ['text' => "500 USDT", "callback_data" => "rech_500"],
                    ],
                    [
                        //充值记录
                        ['text' => "1000 USDT", "callback_data" => "rech_1000"],
                        //积分明细
                        ['text' => "5000 USDT", "callback_data" => "rech_5000"],
                        //账户安全
                        ['text' => $lang["zdyje"], "callback_data" => "rech_other"],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                    ]
                ];
                $text = "";
                break;
            case "rech":
                if($key[1] == "other"){
                    $this->bot->sendMessage($this->userid,$this->getSystemConfig("memberrechmoney"), "HTML",true,null,new ForceReply(true));
                    exit();
                }
                $data = $this->crateOrder($key[1],1,$this->userid);
                if($data["code"] == 0){
                    $text = $this->getSystemConfig("crateordererror");
                    $button = $arr = [
                        [
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "recharge"],
                        ]
                    ];
                }else{
                    $text = $lang["orderid"].": ".$data["data"]["order_id"]."\n";
                    $text = $text.$lang["ordermoney"].": ".$data["data"]["actual_amount"]." USDT\n";
                    $text = $text.$lang["zftoken"].": `".$data["data"]["token"]."`\n";
                    $button = $arr = [
                        [
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "recharge"],
                        ]
                    ];
                }
                $arr = $button;
                break;
            case "cash":
                $this->bot->sendMessage($this->userid,$this->getSystemConfig("txje"), "HTML",true,null,new ForceReply(true));
                break;
            case "settoken":
                $this->bot->sendMessage($this->userid,$this->getSystemConfig("settoken"), "HTML",true,null,new ForceReply(true));
                break;
            case "membersafe":
                $member = $this->getMemberInfo($this->userid);
                $text = "ID: `".$member["tg_id"]."`\n";
                $text = $text.$lang["trcadd"].": ".$member["utoken"]."\n";
                $arr = [
                    [
                        ['text' => $lang["setting"].$lang["trcadd"], "callback_data" => "settoken"],
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                    ]
                ];
                break;
            case "groupman":
                if(array_key_exists(1,$key)){
                    $nextpage = $key + 1;
                }else{
                    $nextpage = 2;
                }
                $res = $this->getUserGroup($this->userid);
                $c = count($res["data"]);
                for($i = 0;$i<$c;$i++){
                    if($res["data"][$i]["group_type"] == 1){
                        $str = "👥 ";
                    }else{
                        $str = "📢 ";
                    }
                    $arr[$i] = [["text"=>$str.$res["data"][$i]["group_nick"],"callback_data"=>"group_".$res["data"][$i]["tggroup_id"]]];
                }
                $arr[$i] = [
                    ["text"=>$this->getSystemConfig("uppage"),"callback_data"=>"groupman_"],
                    ["text"=>$this->getSystemConfig("nextpage"),"callback_data"=>"groupman_".$nextpage],
                ];
                $arr[$i + 1] = [
                    ["text"=>$this->getSystemConfig("retnrunuppage"),"callback_data"=>"start"],
                ];
                break;
            case "group":
                if(array_key_exists(2,$key)){
                    switch ($key[2]){
                        case "edit":
                            $this->setinfo($key[1],$key[3]);
                            break;
                    }
                }
                $res = $this->getGroup($key[1]);
                $keyword = $this->getbq($key[1]);
                $s = "";
                for($i = 0;$i<count($keyword);$i++){
                    $s = $s ."#".$keyword[$i]["title"]." ";
                }
                $string = $lang["grouptype"]." : ";
                if($res["group_type"] == 1){
                    $string = $string.$lang["group"]."\n";
                }else{
                    $string = $string.$lang["pindao"]."\n";
                }
                $string = $string.$lang["groupnick"]." : ".$res["group_nick"]."\n";
                $string = $string.$lang["groupid"]." : ".$res["tggroup_id"]."\n";
                $string = $string.$lang["grouplink"]." : https://t.me/".$res["group_username"]."\n";
                $string = $string.$lang["biaoqian"]." : ".$s."\n";
                $string = $string.$lang["popcount"]." : ".$res["group_count"]."\n";
                $text = $string.$lang["secount"]." : ".$res["searchcount"]."\n";
                $arr = [
                    [
                        ['text' => $res["issearch"] == 1?"✅ ".$lang["seaction"]:"☑️ ".$lang["seaction"], "callback_data" => "group_{$key[1]}_edit_issearch"],
                    ],[
                        ['text' => $res["istop"] == 1?"✅ ".$lang["topadv"]:"☑️ ".$lang["topadv"], "callback_data" => "group_{$key[1]}_edit_istop"],
                    ],[
                        ['text' => $res["isenteradv"] == 1?"✅ ".$lang["weladv"]:"☑️ ".$lang["weladv"], "callback_data" => "group_{$key[1]}_edit_isenteradv"],
                    ],[
                        ['text' => $res["timetext"] == 1?"✅ ".$lang["timeadv"]:"☑️ ".$lang["timeadv"], "callback_data" => "group_{$key[1]}_edit_timetext"],
                    ],[
                        ['text' => $lang["setzdybq"], "callback_data" => "setkey_{$key[1]}"],
                    ],[
                        ['text' => $lang["zdywel"], "url"=>"https://".$_SERVER['HTTP_HOST']."/functionview/welcome.html?group=".$res["tggroup_id"]],
                        ['text' => $lang["zdytime"], "url"=>"https://".$_SERVER['HTTP_HOST']."/functionview/actiontime.html?group=".$res["tggroup_id"]],
                        ['text' => $lang["zdytop"], "url"=>"https://".$_SERVER['HTTP_HOST']."/functionview/advtop.html?group=".$res["tggroup_id"]],
                    ],[
                        ['text' => $lang["zdyadvkey"], "url"=>"https://".$_SERVER['HTTP_HOST']."/functionview/advtop.html?group=".$res["tggroup_id"]],
                    ],[
                        ['text' => $lang["deletegroup"], "callback_data" => "deletegroup_{$key[1]}"],
                        ['text' => $lang["updategroup"], "callback_data" => "updategroup_{$key[1]}"],
                    ],[
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "groupman"],
                    ]
                ];
                break;
            case "deletegroup":
                $this->deleteGroup($key[1]);
                $text = $lang["deletesuccess"];
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "groupman"],
                    ]
                ];
                break;
            case "updategroup":
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "groupman"],
                    ]
                ];
                $text = "";
                break;
            case "setkey":
                $this->bot->sendMessage($this->userid,$this->getSystemConfig("hfkeyword")."_".$key[1], "HTML",true,null,new ForceReply(true));
                break;
            case "jfmx":
                $string = "";
                if(array_key_exists(1,$key)) {
                    $data = $this->getCoinList($this->userid,(int)$key[1]);
                    if($key[1] > $data["last_page"]){
                        $text = $lang["nonextpage"];
                        $upage = $key[1];
                        $arr = [
                            [
                                ['text' => $this->getSystemConfig("uppage"), "callback_data" => "jfmx_".$upage],
                            ],[
                                ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                            ]
                        ];
                        break;
                    }
                    if($key[1] < 1){
                        $arr = [
                            [
                                ['text' => $this->getSystemConfig("nextpage"), "callback_data" => "jfmx_2"],
                            ],[
                                ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                            ]
                        ];
                        $text = $lang["thisonepage"];
                        break;
                    }
                    $upage = $key[1] + 1;
                    $npage = $key[1] - 1;
                }else{
                    $data = $this->getCoinList($this->userid);
                    $upage = 1;
                    $npage = 2;
                }

                foreach ($data["data"] as $item){
                    $string = $string . $item["msg"]."---".$item["addtime"]."\n";
                }
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("uppage"), "callback_data" => "jfmx_".$upage],
                        ['text' => $this->getSystemConfig("nextpage"), "callback_data" => "jfmx_".$npage],
                    ],[
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                    ]
                ];
                $text = $string;
                break;
            case "duihuan":
                $res = $this->duihuan($this->userid);
                $text = $res["msg"];
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                    ]
                ];
                break;
            case "cooperation":
                if(!array_key_exists(1,$key)){
                    $page = 1;
                }else{
                    $page = $key[1];
                }
                $res = $this->getKeyList($page);
                $c = count($res["data"]);
                for($i = 0;$i<$c;$i++){
                    $arr[$i] = [["text"=>$res["data"][$i]["title"],"callback_data"=>"keyword_".$res["data"][$i]["keyword_id"]]];
                }
                $upage = $page - 1;
                $nextpage = $page + 1;
                $arr[$i] = [
                    ["text"=>$this->getSystemConfig("uppage"),"callback_data"=>"cooperation_".$upage],
                    ["text"=>$this->getSystemConfig("nextpage"),"callback_data"=>"cooperation_".$nextpage],
                ];
                $arr[$i + 1] = [
                    ["text"=>$this->getSystemConfig("retnrunuppage"),"callback_data"=>"start"],
                ];
                //dump($arr);exit();
                $text = "";
                break;
            case "keyword":
                $keyid = $key[1];
                $keyinfo = $this->getKeyInfo($keyid);
                $money = $keyinfo["money"] + $this->getSystemConfig("addmoney");
                $text = "关键词 : ".$keyinfo["name"]."\n";
                $text = $text."购买金额 : ".$money." USDT\n";
                $text = $text."被搜索次数 : ".$keyinfo["sea"]."\n";
                $text = $text."关键词关联群组总数 : ".$keyinfo["weightcount"]."\n";
                $text = $text."购买该关键词的群组数 : ".$keyinfo["weightadv"]."\n";
                $arr = [
                    [
                        ['text' => "购买该关键词", "callback_data" => "selectgroup_".$keyid],
                        ['text' => "查看该关键词历史价格", "callback_data" => "seemoney_".$keyid],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "cooperation"],
                    ]
                ];
                break;
            case "selectgroup":
                if(array_key_exists(2,$key)){
                    $nextpage = $key[2] + 1;
                }else{
                    $nextpage = 2;
                }
                $res = $this->getUserGroup($this->userid);
                $c = count($res["data"]);
                for($i = 0;$i<$c;$i++){
                    if($res["data"][$i]["group_type"] == 1){
                        $str = "👥 ";
                    }else{
                        $str = "📢 ";
                    }
                    $arr[$i] = [["text"=>$str.$res["data"][$i]["group_nick"],"callback_data"=>"buykey_".$key[1]."_".$res["data"][$i]["tggroup_id"]]];
                }
                $arr[$i] = [
                    ["text"=>$this->getSystemConfig("uppage"),"callback_data"=>"selectgroup_".$key[1]],
                    ["text"=>$this->getSystemConfig("nextpage"),"callback_data"=>"selectgroup_".$key[1]."_".$nextpage],
                ];
                $arr[$i + 1] = [
                    ["text"=>$this->getSystemConfig("retnrunuppage"),"callback_data"=>"start"],
                ];
                break;
            case "buykey":
                $keyid = $key[1];
                $group_id = $key[2];
                $res = $this->buykey($keyid,$group_id,$this->userid);
                $text = $res["msg"];
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "cooperation"],
                    ]
                ];
                break;
            case "seemoney":
                if(array_key_exists(2,$key)){
                    $npage = $key[2] + 1;
                    $upage = $key[2] - 1;
                    $page = $key[2];
                }else{
                    $npage = 2;
                    $upage = 1;
                    $page = 1;
                }
                $resdata = $this->seeoldmoney($key[1],$page);
                $res = $resdata["data"]["data"];
                $str = "";
                foreach ($res as $item){
                    $str = $str . "价格 : ".$item["money"]." USDT ---时间:".$item["addtime"]."\n";
                }
                $text = $str;
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("uppage"), "callback_data" => "seemoney_".$upage],
                        ['text' => $this->getSystemConfig("nextpage"), "callback_data" => "seemoney_".$npage],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "cooperation"],
                    ]
                ];
                break;
            case "memberyemx":
                if(array_key_exists(1,$key)){
                    $npage = $key[2] + 1;
                    $upage = $key[2] - 1;
                    $page = $key[2];
                }else{
                    $npage = 2;
                    $upage = 1;
                    $page = 1;
                }
                $resdata = $this->yelist($this->userid,$page);
                $res = $resdata["data"]["data"];
                $str = "";
                foreach ($res as $item){
                    $str = $str . "消费金额 : ".$item["money"]." USDT ---备注:".$item["msg"]."---时间:".$item["addtime"]."\n";
                }
                $text = $str;
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("uppage"), "callback_data" => "memberyemx_".$upage],
                        ['text' => $this->getSystemConfig("nextpage"), "callback_data" => "memberyemx_".$npage],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                    ]
                ];
        }
        $button = $arr;
        return ["text"=>$text,"button"=>$button];
    }
}