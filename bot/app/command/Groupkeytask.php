<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Groupkeytask extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('groupkeytask')
            ->setDescription('the groupkeytask command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('groupkeytask');
        while(true){
            $group = getNohenl();
            if($group == null){
                continue;
            }
            $nick = $group["group_nick"];
            $group_id = $group["group_id"];
            $keyWordList = json_decode(file_get_contents("http://114.67.84.223/get.php?source={$nick}&param1=0&param2=1&json=1"),true);
            $c = count($keyWordList);
            $istrue = false;
            for($i = 0; $i < $c;$i++){
                //查询关键词,如果没有就写入关键词,如果有就查询关键词的id
                $sort = $keyWordList[$i]["p"] * 10;
                $sorg = (int)$sort;
                $keyword_id = json_decode(file_get_contents(API_URL."/keyword/write.html?key={$keyWordList[$i]["t"]}"),true)["data"];
                $res = json_decode(file_get_contents(API_URL."/weight/write.html?group_id={$group_id}&keyword_id={$keyword_id}&sort={$sorg}"),true);
                if($res["code"] == 1){
                    $istrue = true;
                }
            }
            if($istrue){
                $ginfo = json_decode(file_get_contents(API_URL."/group/henlok.html?group_id=".$group_id),true)["data"];
                echo $group["group_nick"]."处理成功".PHP_EOL;
            }else{
                //处理失败继续将改群组放回群组池
                echo $group["group_nick"]."处理失败".PHP_EOL;
            }
        }
    }
}
