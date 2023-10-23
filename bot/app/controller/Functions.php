<?php


namespace app\controller;

//功能页面数据
class Functions extends Base
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
    public function start($update){
        $key = explode("_",$this->key);
        $res = $this->findfunction($key[0]);
        if($key[0] == "page"){
            $contents = new Contents();
            $text = $update->getCallbackQuery()->getMessage()->getReplyToMessage()->getText();
            $messageid = $update->getCallbackQuery()->getMessage()->getMessageid();
            $chatid = $update->getCallbackQuery()->getMessage()->getChat()->getId();
            $this->bot->editMessageText($chatid, $messageid, $contents->getContent($text,$key[1]), "HTML", true, $contents->getButton($text,$key[1]));
            exit();
        }
        if(!$res["data"]){
            $arr = [
                [
                    ['text' => $this->getSystemConfig("returnhome"), "callback_data" => "start"],
                ]
            ];
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
            return ["text"=>$this->getSystemConfig("nullfunctionspage"),"button"=>$keyboard];
        }
        $funpages = new Funpages($this->key,$this->bot,$this->userid);
        $resdata = $funpages->start();
        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($resdata["button"]);
        return ["text"=>$resdata["text"]."\n".$res["data"]["tisp"],"button"=>$keyboard];
    }
}