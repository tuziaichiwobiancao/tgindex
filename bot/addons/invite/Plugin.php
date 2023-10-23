<?php


namespace addons\invite;    // 注意命名空间规范
use app\controller\Base;
use think\Addons;
use think\App;

/**
 * 会员邀请插件
 * @author byron sampson
 */
class Plugin extends Addons    // 需继承think\Addons类
{
    // 该插件的基础信息
    public $info = [
        'name' => 'invite',    // 插件标识
        'title' => '邀请插件',    // 插件名称
        'description' => 'Telegram邀请插件',    // 插件简介
        'status' => 1,    // 状态
        'author' => 'byron sampson',
        'version' => '0.1'
    ];


    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }
    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }
    /**
     * 实现的invitehook钩子方法   邀请方法的实现
     * @return mixed
     */
    public function invitehook($param)
    {
        $url = API_URL."/invite/index.html";
        $arr = [
            "mid" => $param["mid"],
            "number" => $param["number"],
            "msg" => $param["msg"],
        ];
        dump(curl_request($url,$arr));
    }
}