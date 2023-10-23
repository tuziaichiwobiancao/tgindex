<?php
namespace app\controller;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\Update;
use think\exception\ErrorException;

class Index extends Base
{
    public function index($token = null){
        //exit();
        if($token == null || $token == ""){
            ejson("token不能为空");
        }
        try {
            $mess = $this->saveMessage();      //保存消息记录
            if($mess["code"] == 0){
                echo 1;exit();
            }
            //实例化消息
            $bot = new Client($token);
            //开始
            $bot->command('start', function ($message) use ($bot) {
                $r = explode(" ",$message->getText());
                $arr = [
                    "tgid" => $message->getChat()->getId(),             //记录会员id
                    "username" => $message->getChat()->getUsername() == null?"":$message->getChat()->getUsername(),    //记录会员用户名
                    "firstname" => $message->getChat()->getFirstName() == null?"":$message->getChat()->getFirstName(), //记录会员姓
                    "lastname" => $message->getChat()->getLastName() == null?"":$message->getChat()->getLastName(),    //记录会员名
                    "headimg" => $message->getChat()->getPhoto() == null?"":$message->getChat()->getPhoto(),           //记录会员头像文件id
                    //"up_tg_id" => "",                                                                                  //记录会员上级会员id
                ];
                if(array_key_exists(1,$r)){
                    $arr["up_tg_id"] = $r[1];
                }else{
                    $arr["up_tg_id"] = "";
                }
                //记录会员信息
                if($this->saveMember($arr)["code"] != 5){
                    hook('invitehook', ['mid'=>$r[1],"number"=>$this->getSystemConfig("newmember"),"msg"=>"邀请新用户".$r[1]."奖励".$this->getSystemConfig("newmember")."积分"]);
                }
                //请求start页面内容
                $submitInfo = $this->getButtonInfo("start");
                //按钮json转换成数组
                $button = json_decode($submitInfo["data"]["button"],true);
                //判断接口是否成功
                if($submitInfo["code"] == 0){
                    //不成功发送错误信息给用户
                    $bot->sendMessage($arr["tgid"],$submitInfo["msg"]);
                }else{
                    //判断按钮是否为空
                    if($button) {
                        //不为空实例化按钮数据并发送消息
                        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($button);
                        $bot->sendMessage($arr["tgid"], $submitInfo["data"]["title"], "HTML", true,null,$keyboard);
                    }else{
                        //否则发送不带按钮的消息
                        $bot->sendMessage($arr["tgid"], $submitInfo["data"]["title"], "HTML", true);
                    }
                }
            });
            //接收到的消息
            $bot->on(function (Update $update) use ($bot,$token) {
                //编辑消息
                if($update->getEditedMessage()){
                    new Editedmessage($update->getEditedMessage(),$bot);
                }
                //正常消息
                if($update->getMessage()){
                    $message = new Message($update,$bot);
                    $message->start($token);
                }
                //内联按钮
                if($update->getCallbackQuery()){
                    $CallbackQuery = new Callbackquery($update,$bot);
                    $CallbackQuery->start();
                }
            }, function () {
                return true;
            });
            $bot->run();
        }catch (Exception $e){
            echo $e->getMessage().$e->getFile();
        }catch (ErrorException $e){
            echo $e->getMessage().$e->getFile().$e->getLine();
        }
    }


    public function test(){
        echo hook('invitehook', ['mid'=>1,"number"=>1,"msg"=>"邀请新用户奖励1积分"]);
    }
    //充值成功通知
    public function notify($uid,$money,$orderid){
        $bot = new Client(getSystemConfig("timetasktoken"));
        $bot->sendMessage($uid,"您的订单号:{$orderid}\n到账金额:{$money} USDT\n充值成功,成功到账到余额,请注意查看");
    }
    
    public function sendtx($ids,$uid){
        $txinfo = $this->queryCash($ids);
        $bot = new Client(getSystemConfig("timetasktoken"));
        $bot->sendMessage($uid,"您的提现已经处理了\n提现金额:".$txinfo["actualmoney"]." USDT\n到账金额:".$txinfo["money"]." USDT\n到账TRC20地址 : ".$txinfo["utoken"]."\n提示消息 : ".$txinfo["msg"]);
    }

}