<html>
    <form action="" method="post">
        机器人token:<input type="text" name="token" value="<?php echo $_POST["token"]?>"/><br/>
        用户id:<input type="number" name="userid" value="value="<?php echo $_POST["userid"]?>""><font color="red">注:用户id必须存在于数据库中，在后台->用户管理->TG用户id</font><br/>
        用户名:<textarea name="user"></textarea><font color="red">注:只需要提交用户名部分,比如用户名是https://t.me/test,只需要填写test以回车分割,由于网络限制,建议单次提交10到20个</font><br/>
        <button type="submit">提交</button>
    </form>
</html>
<?php
if($_POST){
    echo "正在收录中<br/>";
    $token = $_POST["token"];
    $userid = $_POST["userid"];
    $user = $_POST["user"];
    $arr = explode("\r\n",$user);
    $c = count($arr);
    for($i = 0;$i<$c;$i++){
        $qunuser = explode("t.me/",$arr[$i]);
        echo $arr[$i].file_get_contents("https://".$_SERVER['HTTP_HOST']."/index.php/Shoulu/index.html?token={$token}&qunuser={$qunuser[1]}&userid={$userid}")."<br/>";
    }
}