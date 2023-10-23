<?php


namespace app\controller;


class Contents extends Base
{
    //搜索页面文字部分内容
    public function getContent($text,$page = 1){
        //随机获取一条广告
        $res = $this->getAdvInfo(4,1);
        //获取系统配置中顶部内容
        $topadv = $this->getSystemConfig("toptext")."    ";
        //如果随机广告为空
        if($res == null){
            //获取系统配置中的顶部广告内容
            $startadv = $this->getSystemConfig("deftopadv");
        }else{
            //获取的广告内容转成html
            $startadv = "<a href='{$res['url']}'>{$res['content']}</a>";
        }
        $topadv .= $startadv."\n";
        //分割顶部其他广告
        $d = explode("\r\n",$this->getSystemConfig("advother"));
        //获取顶部其他广告总条数
        $c = count($d);
        for($i = 0;$i<$c;$i++){
            //便利广告
            $topadv .= str_replace("{adv}",$this->getAdvInfo(4,1,true),$d[$i])."\n";
        }
        //获取系统配置中间的内容
        $topadv .= "\n".$this->getSystemConfig("content")."\n\n";
        //获取搜索的结果页面
        $data = $this->getGroupList($text,$page)["data"];
        //获取总条数
        $c = count($data["data"]);
        if($c == 0){
            $topadv .= $this->getSystemConfig("nogroup");
        }else {
            //便利结果
            for ($i = 0; $i < $c; $i++) {
                $k = $i + (($page - 1) * 20) + 1;
                if ($data["data"][$i]["group_type"] == 1) {
                    $avtic = "👥";
                } else {
                    $avtic = "📢";
                }
                if ($data["data"][$i]["group_count"] >= 1000) {
                    $s = $data["data"][$i]["group_count"] / 1000;
                    $s = (int)$s . "K";
                } else {
                    $s = $data["data"][$i]["group_count"];
                }
                $topadv .= $k . "." . $avtic . "<a href='https://t.me/{$data["data"][$i]["group_username"]}'>{$data["data"][$i]["group_nick"]} - {$s}</a>\n";
            }
        }
        //获取系统配置中底部内容
        $topadv .= "\n\n".$this->getSystemConfig("deffootadv");
        //返回拼接好的字符串
        return $topadv;
    }
    //搜索页面按钮部分内容
    public function getButton($text,$page = 1){
        //获取总的数据
        $data = $this->getGroupList($text,$page)["data"];
        //如果当前页面是第一页
        if($page == 1){
            $sname = $this->getSystemConfig("findshop");
            $stype = "url";
            $surl = $this->getSystemConfig("findshopurl");
        }else{
            //上一页
            $sname = $this->getSystemConfig("uppage");
            $stype = "callback_data";
            $p = $page - 1;
            $surl = "page_{$p}";
        }
        //获取一条广告
        $adv = $this->getAdvInfo(7,1);
        if($page >= $data["last_page"]){   //
            $nname = $adv["content"];
            $ntype = "url";
            $nurl = $adv["url"];
        }else{
            //下一页
            $nname = $this->getSystemConfig("nextpage");
            $ntype = "callback_data";
            $p = $page + 1;
            $nurl = "page_{$p}";

        }
        //获取三条广告
        $adv1 = $this->getAdvInfo(7,1);
        $adv2 = $this->getAdvInfo(7,1);
        $adv3 = $this->getAdvInfo(7,1);
        //dump($adv1,$adv2,$adv3);exit();
        $arr = [
            [
                ['text' => $sname, $stype => $surl],
                ['text' => $this->getSystemConfig("wyzq"), 'url' => $this->getSystemConfig("wyzqurl")],
                ['text' => $nname, $ntype => $nurl],
            ],
            [
                ['text' => $adv1["content"], 'url' => $adv1["url"]],
                ['text' => $adv2["content"], 'url' => $adv2["url"]],
            ],
            [
                ['text' => $adv3["content"], 'url' => $adv3["url"]],
            ],
        ];
        //dump($arr);exit();
        $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($arr);
        //返回按钮对象
        return $keyboard;
    }
}