<?php



/**
 *@desc tcp客户端，连接tcp服务
 **/
$client = new swoole_client(SWOOLE_SOCK_TCP);
$port = 9501;
$ip = "192.168.0.213";
if(!$client->connect($ip,$port)){
    echo "连接失败";
    exit;
}

fwrite(STDOUT,"请输入消息:");
$msg = trim(fgets(STDIN));

//发送消息给tcp服务器
$client->send($msg);


//接收来自server的数据
$result = $client->recv();
echo $result;
