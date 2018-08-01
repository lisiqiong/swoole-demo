<?php
require('./config.php');
require("./mysqli.class.php");
/***
 *@desc websocket类
 *
 **/
class ws{
    public $_ws;
    public $_conf;
    /**
     *
     *
     ***/
    public function __construct($config){
        $this->_conf = $config;
        $this->_ws = new swoole_websocket_server($config['websocket']['host'],$config['websocket']['port']);
        $this->_ws->set([
                'daemonize'=>1,
                'log_file'=>'/data/swoole-demo/websocket/log/debug.log',
        ]);
        $this->_ws->on('open',array($this,'onOpen'));
        $this->_ws->on('message',array($this,'onMessage'));
        $this->_ws->on('close',array($this,'onClose'));
        $this->_ws->start();
    }

    /*
     *@desc 打开
     ***/
    public function onOpen($serv,$request){
        echo "成功连接websocket服务 {$request->fd}\n";
    }

    /**
     *@desc 消息处理
     **/
    public function onMessage($server,$frame){
        print_r($frame);
        $conf = $this->_conf['mysql'];
        $db = new MysqliDb($conf);
        $sql = "select * from fight_crop order by id desc limit 5 ";
        $data = $db->getOperation($sql);
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
new ws($config);

