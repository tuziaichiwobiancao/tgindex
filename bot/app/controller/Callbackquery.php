<?php


namespace app\controller;


class Callbackquery extends Base
{
    private $update;
    private $bot;
    public function __construct($update,$bot)
    {
        $this->update = $update;
        $this->bot = $bot;
    }
    //开始处理
    public function start(){
        $contents = new Contents();
        if($this->update->getCallbackQuery()->getMessage()->getChat()->getType() == "supergroup") {
            $data = $this->update->getCallbackQuery()->getData();
            $page = explode("_", $data)[1];
            $text = $this->update->getCallbackQuery()->getMessage()->getReplyToMessage()->getText();
            if($this->update->getCallbackQuery()->getFrom()->getId() != $this->update->getCallbackQuery()->getMessage()->getReplyToMessage()->getFrom()->getId()){
                //不是自己的数据；
                $this->bot->answerCallbackQuery($this->update->getCallbackQuery()->getId(), $this->getSystemConfig("otherdata"), true);
                exit();
            }
            $messageid = $this->update->getCallbackQuery()->getMessage()->getMessageid();
            $chatid = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();
            $this->bot->editMessageText($chatid, $messageid, $contents->getContent($text,$page), "HTML", true, $contents->getButton($text,$page));
        }
        if($this->update->getCallbackQuery()->getMessage()->getChat()->getType() == "private"){
            $data = $this->update->getCallbackQuery()->getData();
            $resData = $this->getButtonInfo($data);
            //如果没有该页面信息去查找功能页面
            if($resData["code"] == 0){
                $functions = new Functions($data,$this->bot,$this->update->getCallbackQuery()->getFrom()->getId());
                $res = $functions->start($this->update);
                $this->bot->editMessageText($this->update->getCallbackQuery()->getMessage()->getChat()->getId(), $this->update->getCallbackQuery()->getMessage()->getMessageid(), $res["text"], "MarkDown", true, $res["button"]); //MarkDown
            }else{
                $button = json_decode($resData["data"]["button"],true);
                if($button) {
                    //不为空实例化按钮数据并发送消息
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($button);
                    $this->bot->editMessageText($this->update->getCallbackQuery()->getMessage()->getChat()->getId(), $this->update->getCallbackQuery()->getMessage()->getMessageid(), $resData["data"]["title"], "HTML", true, $keyboard);
                }else{
                    //否则发送不带按钮的消息
                    $this->bot->editMessageText($this->update->getCallbackQuery()->getMessage()->getChat()->getId(), $this->update->getCallbackQuery()->getMessage()->getMessageid(), $resData["data"]["title"], "HTML", true);
                }
            }
        }
    }
}