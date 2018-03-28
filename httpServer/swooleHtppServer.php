<?php

$http = new swoole_http_server("172.18.6.13",9501);
$http->on('request',function($request,$response){
            $header = $request->header['host'];
            //$request->header['Content-Type'] = "text/html;charset=utf8";
            $cookie = $request->cookie['username'];
            $get = $request->get['name'];
            $html = "<hl>Hello Swoole</h1>-----<br/>header:".$header.'---cookie:'.$cookie.'---:get参数name的值为:'.$get;
            $response->end($html);
        });
$http->start();



















