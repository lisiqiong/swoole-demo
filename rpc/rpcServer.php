<?php

spl_autoload_register(function($classname){
        require_once("./{$classname}.php");
        });
$serv=new swoole_server('192.168.0.213',8888);
$serv->set(array('worker_num'=>2));


$serv->on('connect',function($serv,$fd){
        echo "client:fd:{$fd}连接上了哦".PHP_EOL;
        });

$serv->on("receive",function($serv,$fd,$from_id,$data){
        //解析客户端协议
        $info=json_decode($data,true);
        $classname=$info['service'];
        $action=$info['action'];
        $param=$info['param'];

        //调用一个类
        $classobj = new $classname;
        //$result=$classobj->$action($param);
        if(method_exists($classobj,$action)){
            $result = call_user_func_array(array($classobj, $action), $param);
            $rsdata = ['code'=>0,'msg'=>'请求成功','data'=>$result];

        }else{
            $rsdata = ['data'=>-1,'msg'=>'该api不合法'];
        }
        $serv->send($fd,json_encode($rsdata));
        });

$serv->on('close', function ($serv, $fd) {
        echo "Client: Close".PHP_EOL;
        });

$serv->start();

