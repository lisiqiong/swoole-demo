<?php


/**
 *@desc swoole服务端构建全局类
 ***/
class server{
    public $_serv;

    public function __construct($host,$port){
        $this->_serv = new swoole_server($host,$port); 
        $this->_serv->set([
                'worker_num'=>6,
                'max_request'=>1000,
        ]);

        //$this->_serv->on('Start', array($this, 'onStart'));
        //$this->_serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->_serv->on('Connect', array($this, 'onConnect'));
        $this->_serv->on('Receive', array($this, 'onReceive'));
        $this->_serv->on('Close', array($this, 'onClose'));
        $this->_serv->start();
    }


    /***
     *@desc 连接tcp服务端
     **/
    public function onConnect($serv,$fd,$reactor_id){
        echo "client id:{$fd} is connect succes!\n";    
    }


    /**
     *@desc 收到客户传递的消息 
     **/
    public function onReceive($serv,$fd,$reactor_id,$data){
        echo "客服传递的数据是：".$data."\n";
        $serv->send($fd,'发送消息给客户端');
    }


    /**
     *@desc 关闭
     **/
    public function onClose($serv,$fd){
        echo "Client id:{$fd} is close\n"; 
    }

}
$host = "192.168.0.213";
$port = 9501;
new server($host,$port);
