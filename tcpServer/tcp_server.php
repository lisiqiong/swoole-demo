<?php


$serv = new swoole_server("192.168.0.213", 9501); 

$serv->set([
    'worker_num'=>4,
    'max_request'=>1000,
]);


//监听连接进入事件
$serv->on('connect', function ($serv, $fd,$reactor_id) {  
            echo "Client: Connect.\n";
     });

/*
 *监听数据接收事件
 *$fd:客户端连接的唯一标示
 *$reactor_id:线程id
 *$data:客户端的数据
 */
$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
        //print_r($data);
        //$test = bindec($data);
        echo "客户端id:".$fd.",线程id：".$reactor_id.",发送的消息为:  ".$data."\n"; 
        //$stats = $serv->stats();
        //print_r($stats);
        //获取客户端信息
        $fdinfo = $serv->getClientinfo($fd);
        print_r($fdinfo);
        $serv->send($fd,"您发送的消息数据为:".$data.',这边已经收到'.',客户端标示id为:'.$fd.',线程id为:'.$reactor_id);        
});


//监听连接关闭事件
$serv->on('close', function ($serv, $fd) {
            echo "Client: Close.\n";
});

//启动服务器
$serv->start(); 

