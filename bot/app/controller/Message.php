<?php


namespace app\controller;


use TelegramBot\Api\Exception;
use think\exception\ErrorException;
use think\response\Json;

class Message extends Base
{
    private $update;
    private $bot;
    private $token;
    public function __construct($update,$bot)
    {
        $this->update = $update;
        $this->bot = $bot;
    }
    //开始
    public function start($token){
        $content = new Contents();

        //退群操作
        if($this->update->getMessage()->getLeftChatMember()){
            exit();
        }
        //用户进群处理
        if($this->update->getMessage()->getNewChatMembers()){
            $newmember = new Newmember($this->bot,$this->update);
            $newmember->start();
            exit();
        }
        //发送的消息处理
        //$content->getButton($this->update->getMessage()->getText());

            $text = $this->update->getMessage()->getText();
            //记录会员数据
            $arr = [
                "chatid" => $this->update->getMessage()->getChat()->getId(),
                "userid" => $this->update->getMessage()->getFrom()->getId(),
                "type" => $this->update->getMessage()->getChat()->getType(),
                "text" => $text,
                "username" => $this->update->getMessage()->getFrom()->getUsername() == null ? "" : $this->update->getMessage()->getFrom()->getUsername(),
                "firstName" => $this->update->getMessage()->getFrom()->getFirstName() == null ? "" : $this->update->getMessage()->getFrom()->getFirstName(),
                "lastName" => $this->update->getMessage()->getFrom()->getLastName() == null ? "" : $this->update->getMessage()->getFrom()->getLastName(),
            ];
            $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
            $this->sendMess($json);
            $arrd = [
                "tgid" => $this->update->getMessage()->getFrom()->getId(),             //记录会员id
                "username" => $this->update->getMessage()->getFrom()->getUsername() == null ? "" : $this->update->getMessage()->getFrom()->getUsername(),    //记录会员用户名
                "firstname" => $this->update->getMessage()->getFrom()->getFirstName() == null ? "" : $this->update->getMessage()->getFrom()->getFirstName(), //记录会员姓
                "lastname" => $this->update->getMessage()->getFrom()->getLastName() == null ? "" : $this->update->getMessage()->getFrom()->getLastName(),    //记录会员名
                "headimg" => "",           //记录会员头像文件id
                "up_tg_id" => "",                                                                                  //记录会员上级会员id
            ];
            //记录会员信息
            $this->saveMember($arrd);
            
        switch ($this->update->getMessage()->getChat()->getType()){
            //私信机器人
            case "private":
                try{$this->update->getMessage()->getReplyToMessage()->getText();
                    //echo $this->update->getMessage()->getReplyToMessage()->getText();exit();
                    $reply = new Replymess($this->bot,$this->update);
                    $reply->start();
                    exit();
                }catch (\Error $e){}
                $shoulu = new Shoulu();
                if(count(explode("@",$text)) == 2){
                    $user = explode("@",$text);
                    $res = $shoulu->index($token,$user[1],$this->update->getMessage()->getChat()->getId(),0);
                    $this->bot->sendMessage($this->update->getMessage()->getChat()->getId(),$res,"html",true,$this->update->getMessage()->getMessageid());
                    exit();
                }elseif(count(explode("t.me/",$text)) == 2){
                    $user = explode("t.me/",$text);
                    $res = $shoulu->index($token,$user[1],$this->update->getMessage()->getChat()->getId(),0);
                    $this->bot->sendMessage($this->update->getMessage()->getChat()->getId(),$res,"html",true,$this->update->getMessage()->getMessageid());
                    exit();
                }
                //dump($this->update);exit();
                //开启私信搜索功能
                if($this->getSystemConfig("issearch")){
                    $this->bot->sendMessage($this->update->getMessage()->getChat()->getId(),$content->getContent($this->update->getMessage()->getText()),"HTML",true,$this->update->getMessage()->getMessageid(),$content->getButton($this->update->getMessage()->getText()));
                }
                break;
                //超级群组
            case "supergroup":
                //获取到群组搜索的字符字数比对后台设定的字符字数
                if(strlen($text) > (int)$this->getSystemConfig("lenght")){
                    $this->bot->deleteMessage($this->update->getMessage()->getChat()->getId(),$this->update->getMessage()->getMessageid());
                    exit();
                }
                $ginfo = $this->getGroup($this->update->getMessage()->getChat()->getId());
                try {
                    if ($ginfo["issearch"] != 0) {
                        if(!$this->update->getMessage()->getFrom()->isBot()){
                            $this->bot->sendMessage($this->update->getMessage()->getChat()->getId(), $content->getContent($this->update->getMessage()->getText()), "HTML", true, $this->update->getMessage()->getMessageid(), $content->getButton($this->update->getMessage()->getText()));
                        }
                    }
                }catch(ErrorException $e){
                    $this->bot->sendMessage($this->update->getMessage()->getChat()->getId(), $content->getContent($this->update->getMessage()->getText()), "HTML", true, $this->update->getMessage()->getMessageid(), $content->getButton($this->update->getMessage()->getText()));
                }
                break;
                //私群
            case "group":
                //判断私群是否开启搜索功能
                if($this->getSystemConfig("prigroups")) {
                    $this->bot->sendMessage($this->update->getMessage()->getChat()->getId(), $content->getContent($this->update->getMessage()->getText()), "HTML", true, $this->update->getMessage()->getMessageid(), $content->getButton($this->update->getMessage()->getText()));
                }
                break;
        }
    }
}