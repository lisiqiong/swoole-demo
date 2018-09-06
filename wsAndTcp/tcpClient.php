
<?php

//$client = new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);
$client = new swoole_client(SWOOLE_SOCK_TCP);

//连接到服务器
$host = "192.168.0.213";
$port = 9502;
if (!$client->connect($host, $port))
{
        die("connect failed.");
}


//$sendData = ['data'=>'test'];
$sendData  = 'test send data';
//向服务器发送数据
if (!$client->send($sendData))
{
        die("send failed.");
}


//从服务器接收数据
//关闭连接
//$client->close();

