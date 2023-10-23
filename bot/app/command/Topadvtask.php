<?php
declare (strict_types = 1);

namespace app\command;

use app\controller\Base;
use TelegramBot\Api\Client;
use TelegramBot\Api\HttpException;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\exception\ErrorException;

class Topadvtask extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('topadvtask')
            ->setDescription('the topadvtask command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('topadvtask');
        $base = new Base();
        $bot = new Client(getSystemConfig("timetasktoken"));
        $string = "";
        while(true) {
            $string = $string.getSystemConfig("content")."\n\n";
            $adv = getAdvInfo(10, 20);
            if ($adv == null) {
                $string = $string . getSystemConfig("topadvdefcon");
            } else {
                for ($k = 0; $k < 20; $k++) {
                    $adv = getAdvInfo(10, 20);
                    $number = $k + 1;
                    $string = $string . $number . "." . "<a href='{$adv["url"]}'>{$adv["content"]}</a>\n";
                }
            }
            $grouplist = $base->gettopadv();
            for ($i = 0; $i < $grouplist["last_page"]; $i++) {
                $grouplist = $base->gettopadv($i + 1);
                $c = count($grouplist["data"]);
                for ($j = 0; $j < $c; $j++) {
                    try {
                        if($grouplist["data"][$j]["iscj"] == 1){
                            //如果是采集来的群组，结束当前循环
                            continue;
                        }
                        if($grouplist["data"][$j]["istop"] == 0){
                            //用户关闭置顶广告，结束当前循环
                            continue;
                        }
                        if($grouplist["data"][$i]["group_status"] == 0){
                            //群组状态不正常结束循环
                            continue;
                        }
                        $res = $bot->sendMessage($grouplist["data"][$j]["tggroup_id"], $string, "HTML", true, null);
                        $bot->pinChatMessage($grouplist["data"][$j]["tggroup_id"], $res->getMessageid());
                        echo $grouplist["data"][$j]["tggroup_id"] . "消息发送及置顶消息成功" . PHP_EOL;
                    } catch (HttpException $e) {
                        echo $grouplist["data"][$j]["tggroup_id"] . $e->getMessage() . PHP_EOL;
                    } catch (ErrorException $e) {
                        echo $grouplist["data"][$j]["tggroup_id"] . $e->getMessage() . PHP_EOL;
                    }
                }
            }
            sleep((int)getSystemConfig("topadvdefcontime"));
        }
    }
}
