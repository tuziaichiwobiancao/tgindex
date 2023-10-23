<?php


namespace app\controller;

//管理用户进群
class Newmember extends Base
{
    private $bot;
    private $update;
    public function __construct($bot,$update)
    {
        $this->bot = $bot;
        $this->update = $update;
    }
    public function start(){
        //dump($this->update);exit();
        $members = $this->update->getMessage()->getNewChatMembers();
        $chatid = $this->update->getMessage()->getChat()->getId();
        $c = count($members);
        $nick = "";
        for($i = 0;$i<$c;$i++){
            if($members[$i]->isBot()){
                $this->bot->kickChatMember($chatid, $members[$i]->getId());
            }else{
                $nick = $nick.$members[$i]->getFirstName()." ".$members[$i]->getLastName().",";
                if($this->update->getMessage()->getFrom()->getId() == $members[$i]->getId()){
                    $up_tg_id = 0;
                }else{
                    $up_tg_id = $this->update->getMessage()->getFrom()->getId();
                }
                $arr = [
                    "tgid" => $members[$i]->getId(),             //记录会员id
                    "username" => $members[$i]->getUsername() == null?"":$members[$i]->getUsername(),    //记录会员用户名
                    "firstname" => $members[$i]->getFirstName() == null?"":$members[$i]->getFirstName(), //记录会员姓
                    "lastname" => $members[$i]->getLastName() == null?"":$members[$i]->getLastName(),    //记录会员名
                    "headimg" => "",           //记录会员头像文件id
                    "up_tg_id" => $up_tg_id,          //记录会员上级会员id
                ];
                $fname = $members[$i]->getFirstName() == null?"":$members[$i]->getFirstName();
                $lname = $members[$i]->getLastName() == null?"":$members[$i]->getLastName();
                //记录会员信息
                if($this->saveMember($arr)["code"] != 5){
                    hook('invitehook', ['mid'=>$up_tg_id,"number"=>1,"msg"=>"邀请新用户".$fname." ".$lname."奖励".$this->getSystemConfig("newmember")."积分"]);
                }

            }
        }
        ;
        $group = $this->getQunInfo($chatid);
        //存在该群组
        if($group["code"] != 0){
            $groupinfo = $group["data"];
            //不开启进群功能
            if($groupinfo["isenter"] == 0){
                exit();
            }
            //开启了自定义进群功能
            if($groupinfo["isenter"] == 1){
                $str = "";
                //开启了进群广告
                if($groupinfo["isenteradv"]){
                    $adv = $this->getAdvInfo(8,1);
                    if(!$adv) {
                        $str = $str . $this->getSystemConfig("toptext") . $this->getSystemConfig("defweladv") . "\n";
                    }else{
                        $str = $str . $this->getSystemConfig("toptext") . "@".$adv["url"]." - ".$adv["content"] . "\n";
                    }
                }
                $str = $str.strtr($groupinfo["entertext"], '{nick}', $nick);
                switch ($groupinfo["entertype"]){
                    //纯文本进群欢迎
                    case "0":
                        $this->bot->sendMessage($chatid,$str);
                        break;
                    //图片+文字进群欢迎
                    case "1":
                        $arr = json_decode($groupinfo["button"],true);
                        if(!$arr){
                            $this->bot->sendPhoto($chatid,API_DOMAIN.$groupinfo["fileurl"],$str,null);
                            break;
                        }
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
                        $this->bot->sendPhoto($chatid,API_DOMAIN.$groupinfo["fileurl"],$str,null,$keyboard);
                        break;
                        //视频+文字进群欢迎
                    case "2":
                        $arr = json_decode($groupinfo["button"],true);
                        if(!$arr){
                            $this->bot->sendVideo($chatid,API_DOMAIN.$groupinfo["fileurl"],null,$str,null);
                            break;
                        }
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
                        $this->bot->sendVideo($chatid,API_DOMAIN.$groupinfo["fileurl"],null,$groupinfo["entertext"],null,$keyboard);
                        break;
                }
                exit();
            }
        }
        //不存在群或设置系统定义的进群
        //echo $this->getSystemConfig("enteradv");exit();
        $n = "";
        if($this->getSystemConfig("enteradv")){
            $adv = $this->getAdvInfo(8,1);
            if(!$adv) {
                $n = $n . $this->getSystemConfig("toptext") . $this->getSystemConfig("defweladv") . "\n";
            }else{
                $n = $n . $this->getSystemConfig("toptext") . "@".$adv["url"]." - ".$adv["content"] . "\n";
            }
        }
        $n = $n . strtr($this->getSystemConfig("entertext"), '{nick}', $nick);
        switch ($this->getSystemConfig("entertype")){
            case "0":
                break;
            case "1":
                $this->bot->sendMessage($n);
                break;
            case "2":
                $arr = json_decode($this->getSystemConfig("enterbutton"),true);
                if(!$arr){
                    $this->bot->sendPhoto($chatid,API_DOMAIN.$this->getSystemConfig("enterfileurl"),$n,null);
                    break;
                }
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
                $this->bot->sendPhoto($chatid,API_DOMAIN.$this->getSystemConfig("enterfileurl"),$n,null,$keyboard);
                break;
            case "3":
                $arr = json_decode($groupinfo["button"],true);
                if(!$arr){
                    $this->bot->sendVideo($chatid,API_DOMAIN.$this->getSystemConfig("enterfileurl"),null,$n,null);
                    break;
                }
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
                $this->bot->sendVideo($chatid,API_DOMAIN.$this->getSystemConfig("enterfileurl"),null,$n,null,$keyboard);
                break;
        }
    }
}