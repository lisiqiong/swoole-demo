<?php
$http = new swoole_http_server("0.0.0.0", 8888);
$http->set([
        'enable_static_handler'=>true,
        'document_root' => '/data/swoole-demo/httpServer'
        //设置终端执行保存的静态路径
]);
$http->on('request', function ($request, $response) {
        $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
        });
$http->start();
?>
