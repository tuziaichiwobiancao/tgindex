<form method="post" action="">
    机器人token:<input type="text" name="token" value="" /><br/>
    域名(只需要填写域名部分):<input type="text" name="url" value=""><br/>
    <input type="submit" />提交
</form>
<?php
if($_POST){
    $token = $_POST["token"];
    $url = $_POST["url"];
    echo file_get_contents("https://api.telegram.org/bot{$token}/deletewebhook");
    echo file_get_contents("https://api.telegram.org/bot{$token}/setwebhook?url=https://{$url}/?token={$token}");
}