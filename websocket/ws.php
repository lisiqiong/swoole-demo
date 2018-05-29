
<?php


/***
 *@desc websocket类
 *
 **/
class ws{
    public $_ws;
    /**
     *
     *
     ***/
    public function __construct($host,$port){
        $this->_ws = new swoole_websocket_server($host,$port);
        $this->_ws->on('open',array($this,'onOpen'));
        $this->_ws->on('message',array($this,'onMessage'));
        $this->_ws->on('close',array($this,'onClose'));
        $this->_ws->start();
    }

    /*
     *@desc 打开
     ***/
    public function onOpen($serv,$request){
        echo "server:handshake success with fd {$request->fd}\n";
    }

    /**
     *@desc 消息处理
     **/
    public function onMessage($server,$frame){
        print_r($frame);
        echo "receive from {$frame->fd}:{$frame->data};opcode,fin{$frame->finish}\n";
        $data = [
            ['title'=>'1雨燕智能ok','time'=>'2018-05-29 09:46:10'],    
            ['title'=>'2雨燕智能ok1','time'=>'2018-05-29 10:26:10'],    
            ['title'=>'33雨燕智能ok2','time'=>'2018-05-29 11:56:10'],    
            ['title'=>'4雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
            ['title'=>'7雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
            ['title'=>'8雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
            ['title'=>'9雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
            ['title'=>'10雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
            ['title'=>'11雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
            ['title'=>'12雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
            ['title'=>'13雨燕智能ok3','time'=>'2018-05-29 12:56:10'],    
        ];
        $jsonStr = json_encode($data);
        $server->push($frame->fd,$jsonStr);
    }


    /**
     *@desc 关闭
     ***/
    public function onClose($serv,$fd){
        echo "client {$fd} closed\n ";
    }


}

$host = "192.168.0.213";
$port = 9502;
$ws = new ws($host,$port);

