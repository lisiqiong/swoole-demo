<?php

$options = getopt("c:f:");

if(empty($options)){
    echo "命令行中输入调用的api类和方法名称".PHP_EOL;
    exit();
}
print_r($options);
//客户端
class client{
    private $service;
    public function __call($name,$param){
        //var_dump($name,$param);
        //远程调用要使用的方法
        if('service'==$name){
            $this->service=$param[0];
            return $this;
        }
        $cli = new swoole_client(SWOOLE_SOCK_TCP);
        $cli->connect('192.168.0.213', 8888);
        $json_data=json_encode(
                [
                'service'=>$this->service,
                'action'=>$name,
                'param'=>$param
                ]
                );
        $cli->send($json_data);
        $result=$cli->recv();//接收服务端返回的消息
        $cli->close();
        return $result;
    }
}
$cli = new client();
$c = $options['c'];
$f = $options['f'];
//$rpcResult = $cli->service('TestService')->show('思琼');
$rpcResult = $cli->service($c)->$f('思琼');
echo $rpcResult.PHP_EOL;
print_r(json_decode($rpcResult));

