<?php


namespace app\controller;


class Editedmessage extends Base
{
    private $update;
    public function __construct($update,$bot)
    {
        $this->update = $update;
        //dump($this->update);
        //删除消息并将人T出去
        $bot->deleteMessage($this->update->getChat()->getId(),$this->update->getMessageid());
        $bot->kickChatMember($this->update->getChat()->getId(), $this->update->getFrom()->getId());
        //$this->deleteUserMessage($bot,$this->update->getChat()->getId(),$this->update->getFrom()->getId(),$this->update->getMessageid(),true);
    }
}