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
        $content = "客户端id：{$fd} 连接成功";
        $this->writeLog($content);
    }


    /**
     *@desc 收到客户传递的消息 
     **/
    public function onReceive($serv,$fd,$reactor_id,$data){
        $content = "接收的客户端数据为:{$data}";
        $this->writeLog($content);
        $test = bin2hex($data);
        //echo "客服传递的数据是：".$test."\n";
        
        //$show = base_convert('abcd',16,2);
        //$arr = ['a','b','c','d'];

        $sendStr = '01 4D 05 00 01 55 01 00 A5';
        $res = $this->show($sendStr);
        $serv->send($fd,$res);
    }

    /***
     *@desc 写入日志记录信息
     **/
    public function writeLog($content){
        $fileName= date('Ymd').'-ubox-api.log';
        $file_content = "[".date('Y-m-d H:i:s',time())."]  ";
        $file_content .= $content."\n";
        swoole_async_writefile($fileName, $file_content, function($fileName) {
             //echo "wirte ok.\n";
        }, FILE_APPEND);
    }

    /**
     **@desc 1.十六进制转化为十进制
     ********2.返回ascii码指定的耽搁字符串
     ***/
    public function show($sendStr){
        $sendStrArray = str_split(str_replace(' ', '', $sendStr), 2);
        //print_r($sendStrArray);
        $data = '';
        for ($j = 0; $j < count($sendStrArray); $j++) {  
            $data .= chr(hexdec($sendStrArray[$j]));
        }
        return $data;
    }

    /**
     *@desc 关闭
     **/
    public function onClose($serv,$fd){
        $content = "client id:{$fd} is close!";
        $this->writeLog($content);
    }

}
$host = "192.168.0.213";
$port = 9501;
new server($host,$port);
