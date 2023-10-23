<?php


namespace app\controller;


use TelegramBot\Api\Client;
use TelegramBot\Api\HttpException;

class Shoulu extends Base
{
    //单个收录群
    public function index($token = null,$qunuser,$userid,$iscj = 1){
        if(!$token){
            ejson("token不能为空");
        }
        try {
            if($iscj !=1 ) {
                $bot = new Client($token);
                $qunnickdata = $bot->getChat("@" . $qunuser);
                $qunnick = $qunnickdata->getTitle();    //群名称
                $qunid = $qunnickdata->getId();         //群id
                $description = $qunnickdata->getDescription();         //群介绍
                $photo = $qunnickdata->getPhoto() ? $qunnickdata->getPhoto()->getBigFileId() : "";
                $quntype = $qunnickdata->getType();     //群属性,supergroup超级群组
                if ($this->getSystemConfig("isadmin") == 1) {
                    $isadmin = false;
                    if ($iscj == 0) {
                        $admin = $bot->getChatAdministrators("@" . $qunuser);
                        foreach ($admin as $value) {
                            if ($value->getUser()->getUsername() == $this->getSystemConfig("botusername")) {
                                $isadmin = true;
                            }
                        }
                        if ($isadmin == false) {
                            return $this->getSystemConfig("noadminmsg");
                        }
                    }
                }
                if ($quntype == "supergroup") {
                    $type = 1;
                } else {
                    $type = 0;
                }
                $quncount = $bot->getChatMembersCount("@" . $qunuser);
            }else{
                $ress = $this->gettginfo($qunuser);
                $qunnick = $ress["title"];
                $type = 1;
                $quncount = $ress["member"];
                $description = $ress["content"];
                $photo = "";
                $qunid = rand(1,9999999999);
            }
            $arr = [
                "userid" => $userid,
                "username" => $qunuser,
                "nick" => $qunnick,
                "groupid" => $qunid,
                "type" => $type,
                "count" => $quncount,
                "content" => $description,
                "img" => $photo,
            ];
            $res = $this->writegroup($arr,$iscj);
            return $qunnick.$res["msg"];
        }catch(HttpException $e){
            return $e->getMessage().$e->getLine();
        }
    }

    //删除缓存
    public function delecache(){
        if(\think\facade\Cache::clear()){
            echo "缓存清除成功";
        }else{
            echo "缓存清除失败";
        }
    }

    public function gettginfo($username){
            $html = file_get_contents("https://t.me/".$username);
            $pattern = '/<span dir=\"auto\">(.*)<\/span>/U';        //<span dir="auto">测试超级索引</span>
            preg_match_all($pattern, $html, $title);
            $pattern = '/<div class=\"tgme_page_description\" dir=\"auto\">(.*)<\/div>/U';        //<span dir="auto">测试超级索引</span>
            preg_match_all($pattern, $html, $content);
            $pattern = '/<div class=\"tgme_page_extra\">(.*)<\/div>/U';        //<span dir="auto">测试超级索引</span>
            preg_match_all($pattern, $html, $member);
            $members = (int)$member[1][0];
            $arr = ["title" => $title[1][0],"member" => $members,"content" => $content[1][0]];
            return $arr;
    }

}