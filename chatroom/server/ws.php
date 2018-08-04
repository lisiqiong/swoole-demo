<?php
require('../conf/config.php');
require("../lib/mysqli.class.php");
require("../lib/dispatcher.php");
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
                'log_file'=>'/data/swoole-demo/chatroom/log/debug.log',
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
        $welcomemsg = "Welcome {$request->fd} joined this chat room.";
        foreach($serv->connections as $key => $fd) {
            $serv->push($fd, $welcomemsg);
        }
    }

    /**
     *@desc 消息处理
     **/
    public function onMessage($server,$frame){
        $dispatcher = new Dispatcher($frame);
        $chatdata = $dispatcher->parseChatData();
        $isprivatechat = $dispatcher->isPrivateChat();
        $fromid = $dispatcher->getSenderId();
        if($isprivatechat) {
            $toid = $dispatcher->getReceiverId();
            $msg = "【{$fromid}】对【{$toid}】说：{$chatdata['chatmsg']}";
            $dispatcher->sendPrivateChat($server, $toid, $msg); 
        }else{
            $msg = "【{$fromid}】对大家说：{$chatdata['chatmsg']}";
            $dispatcher->sendPublicChat($server, $msg);
        }

    }


    /**
     *@desc 关闭
     ***/
    public function onClose($serv,$fd){
        echo "client {$fd} closed\n ";
        $goodbyemsg = "Client {$fd} leave this chat room.";
        foreach($serv->connections as $key => $clientfd) {
            $serv->push($clientfd, $goodbyemsg);
        }
    }


}
new ws($config);

