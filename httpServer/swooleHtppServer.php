<?php

$http = new swoole_http_server("172.18.6.13",9501);
$http->on('request',function($request,$response){
            //$header = $request->header['host'];
            //$request->header['Content-Type'] = "text/html;charset=utf8";
            //$cookie = $request->cookie['username'];
            $name = $request->get['name'];
            //$html = "<hl>Hello Swoole</h1>-----<br/>header:".$header.'---cookie:'.$cookie.'---:get参数name的值为:'.$get;
            //print_r($request->get);
            $response->header('Content-Type','text/html;Charset=utf8');
            $response->cookie("username","swoole_name",125);
            $html = "<h1>hello swoole,</h1>-----请求参数name:=".$name;
            $response->end($html);
        });
$http->start();



















