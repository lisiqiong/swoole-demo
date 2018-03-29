<?php

//创建websocket服务器对象，监听0.0.0.0.：9502端口
$ws = new swoole_websocket_server("172.18.6.13",9502);
$ws->on('open',function($ws,$request){
            //var_dump($request->fd,$request->get,$request->server);
            echo "客服端id：".$request->fd.'连接上服务器了';
            $ws->push($request->fd,"hello welcome,服务器返回\n");

        });

//监听websocket消息事件
$ws->on('message',function($ws,$frame){
            echo "Message:{$frame->data}\n";    
            $ws->push($frame->fd,"server:{$frame->data}");
        });

//监听websocket连接关闭事件
$ws->on('close',function($ws,$fd){
            echo "client-{$fd} is closed\n";
        });

$ws->start();


























