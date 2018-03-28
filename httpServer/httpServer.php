<?php


//1.构建server对象
$serv = new swoole_server("172.18.6.13",9501);

//2.设置运行时的参数
$serv->set(array(
            "worker_num"=>8,
            "daemonize"=>0,
            "max_request"=>10000,
            "dispatch_mode"=>2,
            "debug_mode"=>1,
            ));

function response($serv,$fd,$respData)
{
    //响应行
    $response = array(
            'HTTP/1.1 200',
            );
    //响应头
    $headers = array(
            'Server'=>'SwooleServer',
            'Content-Type'=>"text/html;charset=utf8",
            'Content-Length'=>strlen($respData),
            );
    foreach($headers as $key=>$val){
        $response[] = $key.':'.$val;
    }
    //空行
    $response[] = '';
    //响应体
    $response[] = $respData;
    $send_data = join("\r\n",$response);
    $serv->send($fd,$send_data);
}





//3.注册事件回调函数
$serv->on("Receive",function($serv,$fd,$from_id,$data){
        $respData = '<h1>hello swoole</h1>';
        response($serv,$fd,$respData);//封装并发送http响应报文
        });

//启动服务器
$serv->start();



