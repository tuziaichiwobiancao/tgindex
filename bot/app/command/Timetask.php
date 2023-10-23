<?php
namespace app\command;

use app\controller\Base;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Timetask extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('timetask')
            ->setDescription('the timetask command');
    }

    protected function execute(Input $input, Output $output)
    {
        while(true) {
            $string = "";
            //获取系统配置中顶部内容
            $string = $string.getSystemConfig("content")."\n\n";
            //判断定时广告是否为空
            $advk = getAdvInfo(9, 20);
            if(!$advk){
                //如果为空获取配置中默认的广告
                $string = $string.getSystemConfig("deftimetaskadv");
            }else {
                for ($k = 0; $k < 20; $k++) {
                    $adv = getAdvInfo(9, 20);
                    $number = $k + 1;
                    $string = $string . $number . "." . "<a href='{$adv["url"]}'>{$adv["content"]}</a>\n";
                }
            }
            $qun = getNoCj();
            for($i = 0;$i < $qun["last_page"];$i++) {
                $qunlist = getNoCj($i + 1)["data"];
                $c = count($qunlist);
                for($j = 0;$j<$c;$j++){
                    //echo $qunlist[$j]["tggroup_id"];exit();
                    $bot = new Client(getSystemConfig("timetasktoken"));
                    try {
                        $bot->sendMessage($qunlist[$j]["tggroup_id"], $string, "HTML", true);
                        echo $qunlist[$j]["tggroup_id"]."发送成功\n";
                    }catch(Exception $e){
                        echo $qunlist[$j]["tggroup_id"]."发送失败,失败原因:".$e->getMessage()."\n";
                    }
                }
            }
            echo "该轮任务结束,线程沉睡".getSystemConfig("timeadv")."后继续执行";
            sleep(getSystemConfig("timeadv"));
        }
    }
}
