<?php


namespace app\controller;

//回复消息统一处理
use TelegramBot\Api\Types\ForceReply;
use think\exception\ErrorException;

class Replymess extends Base
{
    private $bot;
    private $update;
    public function __construct($bot,$update)
    {
        $this->bot = $bot;
        $this->update = $update;
    }
    public function start(){
        $text = explode("_",$this->update->getMessage()->getReplyToMessage()->getText());
        $reply = $this->update->getMessage()->getText();
        switch ($text[0]){
            case $this->getSystemConfig("plaseadvcontent"):
                $advinfo = explode("|",$reply);
                try{
                    $res = $this->editadv($text[1],$advinfo[0],$advinfo[1]);
                }catch (ErrorException $e){
                    $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),"回复的格式不对","HTML",true,null);
                    exit();
                }
                $adv = $this->getAdvInfoCon($text[1]);
                $text = $res["msg"]."\n";
                $text = $text . $this->getSystemConfig("advcontent").": ".$adv["content"]."\n";
                $text = $text . $this->getSystemConfig("linkadd").": ".$adv["url"]."\n";
                $text = $text . $this->getSystemConfig("endtime").": ".$adv["endtime"]."\n";
                $text = $text . $this->getSystemConfig("advtypename").": ".$adv["name"]."\n";
                $text = $text . $this->getSystemConfig("buymoney").": ".$adv["money"]."\n";
                $arr = [
                    [
                        ['text' => $this->getSystemConfig("renewaladv"), "callback_data" => "gopay_".$adv["adv_id"]],
                        ['text' => $this->getSystemConfig("editadv"), "callback_data" => "editadv_".$adv["adv_id"]],
                    ],
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "advtypelist"],
                    ]
                ];
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
                //dump($this->update);exit();
                $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),$text,"HTML",true,null,$keyboard);
                break;
            case $this->getSystemConfig("memberrechmoney"):
                $data = $this->crateOrder((int)$reply,1,$this->update->getMessage()->getFrom()->getId());
                if($data["code"] == 0){
                    $text = $this->getSystemConfig("crateordererror");
                    $button = $arr = [
                        [
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "recharge"],
                        ]
                    ];
                }else{
                    $text = "订单号: ".$data["data"]["order_id"]."\n";
                    $text = $text."订单金额: ".$data["data"]["actual_amount"]." USDT\n";
                    $text = $text."支付token: `".$data["data"]["token"]."`\n";
                    $button = $arr = [
                        [
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "recharge"],
                        ]
                    ];
                }
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($button);
                $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),$text,"MarkDown",true,null,$keyboard);
                break;
            case $this->getSystemConfig("txje"):
                if((int)$reply < $this->getSystemConfig("least")){
                    $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),"最少提现金额是".$this->getSystemConfig("least")."USDT".",请重新提交","MarkDown",true,null);
                    $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),$this->getSystemConfig("txje"), "HTML",true,null,new ForceReply(true));
                    break;
                }
                $arr = $this->sendTx($this->update->getMessage()->getFrom()->getId(),$reply);
                if($arr["code"] == -1){
                    $button = [
                        [
                            ['text' => "设置提现token", "callback_data" => "settoken"],
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                        ]
                    ];
                }else{
                    $button = [
                        [
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                        ]
                    ];
                }
                $string = $arr["msg"];
                if($arr["code"] == 1){
                    $string = $string."\n\n";
                    $string = $string."提现金额: ".$arr["data"]["actualmoney"]."\n";
                    $string = $string."实际到账金额: ".$arr["data"]["money"]."\n";
                    $string = $string."剩余余额: ".$arr["data"]["surplus"]."\n";
                    $string = $string."到账token: ".$arr["data"]["utoken"]."\n";
                }
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($button);
                $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),$string,"MarkDown",true,null,$keyboard);
                break;
            case $this->getSystemConfig("settoken"):
                $button = [
                    [
                        ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "member"],
                    ]
                ];
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($button);
                $res = $this->setToken($this->update->getMessage()->getFrom()->getId(),$reply);
                $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),$res["msg"],"MarkDown",true,null,$keyboard);
                break;
            case $this->getSystemConfig("hfkeyword"):
                $key = explode(",",$reply);
                if(count($key) > 5){
                    $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),"标签过多,请重新提交","MarkDown",true,null);
                    $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),$this->getSystemConfig("hfkeyword")."_".$text[1], "HTML",true,null,new ForceReply(true));
                }else{
                    $res = $this->setlable($text[1],$reply);
                    $button = [
                        [
                            ['text' => $this->getSystemConfig("retnrunuppage"), "callback_data" => "groupman"],
                        ]
                    ];
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($button);
                    $this->bot->sendMessage($this->update->getMessage()->getFrom()->getId(),$res["msg"],"MarkDown",true,null,$keyboard);
                }
        }
    }
}