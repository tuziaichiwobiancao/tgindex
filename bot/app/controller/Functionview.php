<?php
namespace app\controller;
use Aws\S3\S3Client;
use think\facade\Filesystem;
use think\View;
use think\Request;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\Update;
class Functionview extends Base
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    //自定义欢迎页面
    public function welcome($group = null){
        if($this->request->isPost()){
            $entertext = $this->request->post("entertext");
            $entertype = $this->request->post("entertype");
            $button = $this->request->post("button");
            $files = $this->request->file("fileurl");
            $fileName = Filesystem::putFile( 'uploads', $files);
            // 将文件上传的路径返回
            //$fileurl = $this->uploads3($_SERVER['DOCUMENT_ROOT']."/".$fileName);
            $fileurl = "https://".$_SERVER['HTTP_HOST']."/".$fileName;
            $res = $this->setwelcome($group,$entertype,$entertext,$fileurl,$button);
            echo "<script>alert('".$res["msg"]."')</script>";
        }
        $res = $this->getQunInfo($group);
        \think\facade\View::assign("res",$res["data"]);
        \think\facade\View::assign("gid",$group);
        return \think\facade\View::fetch();
    }
    
    //自定义定时页面
    public function actiontime($group = null){
        if($this->request->isPost()){
            $entertext = $this->request->post("entertext");
            $entertype = $this->request->post("entertype");
            $button = $this->request->post("button");
            $files = $this->request->file("fileurl");
            $fileName = Filesystem::putFile( 'uploads', $files);
            $fileurl = "https://".$_SERVER['HTTP_HOST']."/".$fileName;
            $ress = $this->settime($group,$entertype,$entertext,$fileurl,$button);
            echo "<script>alert('".$ress["msg"]."')</script>";
        }
        $res = $this->gettime($group);
        \think\facade\View::assign("res",$res["data"]);
        \think\facade\View::assign("gid",$group);
        return \think\facade\View::fetch();
    }
    
    //自定义定时页面
    public function advtop($group = null){
        if($this->request->isPost()){
            $entertext = $this->request->post("entertext");
            $entertype = $this->request->post("entertype");
            $button = $this->request->post("button");
            $files = $this->request->file("fileurl");
            $fileName = Filesystem::putFile( 'uploads', $files);
            // 将文件上传的路径返回
            //$fileurl = $this->uploads3($_SERVER['DOCUMENT_ROOT']."/".$fileName);
            $fileurl = "https://".$_SERVER['HTTP_HOST']."/".$fileName;
            $res = $this->settopadv($group,$entertype,$entertext,$fileurl,$button);
            echo "<script>alert('".$res["msg"]."')</script>";
        }
        $res = $this->gettopadvi($group);
        \think\facade\View::assign("res",$res["data"]);
        \think\facade\View::assign("gid",$group);
        return \think\facade\View::fetch();
    }
    
    //自定义关键词屏蔽
    public function keypb($group = null){
        
    }
    
    
    //自定义关键词屏蔽
    public function sendxq($userid = null){
        $r = $this->getSupplyCount($userid);
        $count = $r["msg"];
        \think\facade\View::assign("c",$count);
        if($this->request->isPost()){
            $minfo = $this->getMemberInfo($userid);
            if($minfo["money"] - $this->getSystemConfig("supply") < 0){
                echo "<script>alert('余额不足，无法发布,请先到机器人端进行充值')</script>";
                return \think\facade\View::fetch();
                exit();
            }
            $title = $this->request->post("title");
            $content = $this->request->post("content");
            $type = $this->request->post("type");
            $money = $this->request->post("money");
            try{
                $files = $this->request->file("image");
                $fileName = Filesystem::putFile( 'uploads', $files);
                $fileurl = "https://".$_SERVER['HTTP_HOST']."/".$fileName;
            }catch(\think\Exception $e){
                $fileurl = "";
            }
            $str = "GX-".date("YmdHis",time()).rand(100000,999999);
            $bot = new Client(getSystemConfig("timetasktoken"));
            if($type == 0){
                $tx = "供应";
            }else{
                $tx = "需求";
            }
            $array = json_decode($this->getSystemConfig("gxbutton"),true);
            $k = $count + 1;
            $string = "标题 : ".$title."\n编号 : ".$str."\n"."价格 : ".$money." USDT\n类型 : ".$tx."\n内容 : ".$content."\n".$this->getSystemConfig("gxtext");
            if($fileurl == ""){
                if($array){
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
                    $data = $bot->sendMessage($this->getSystemConfig("gxgroupid"), $string, "HTML", true,null,$keyboard);
                }else{
                    $data = $bot->sendMessage($this->getSystemConfig("gxgroupid"), $string, "HTML", true,null);
                }
            }else{
                if($array){
                    $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
                    $data = $bot->sendPhoto($this->getSystemConfig("gxgroupid"),$fileurl,$string,null,$keyboard);
                }else{
                    $data = $bot->sendPhoto($this->getSystemConfig("gxgroupid"),$fileurl,$string,null);
                }
            }
            $messid = $data->getMessageId();
            $res = $this->SupplyAdd($userid,$title,$content,$type,$money,$fileurl,$messid,$str);
        }
        return \think\facade\View::fetch();
    }
    
}