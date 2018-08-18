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
    public $table;
    public $http_server;
    /**
     *
     *
     ***/
    public function __construct($config){
        //建立内存表存储fd数据
        $table = new \Swoole\Table(20);
        $table->column('id',$table::TYPE_INT,4);
        $table->create();
        $this->table = $table;
        $this->_conf = $config;
        $this->http_server = new swoole_http_server($config['websocket']['host'],8888);
        $this->http_server->set([
                'enable_static_handler'=>true,
                'document_root' => '/data/swoole-demo/httpServer',
        ]);


        //$this->_ws = new swoole_websocket_server($config['websocket']['host'],$config['websocket']['port']);
        $this->_ws = $this->http_server->addListener($config['websocket']['host'],$config['websocket']['port'],SWOOLE_SOCK_TCP);
        
        //关闭websocket模式
        $this->_ws->set([
                'daemonize'=>1,
                'log_file'=>'/data/swoole-demo/websocket/log/debug.log',
        ]);
        $this->http_server->on('request',[$this,'onrequest']);
        $this->_ws->on('open',array($this,'onOpen'));
        $this->_ws->on('message',array($this,'onMessage'));
        $this->_ws->on('close',array($this,'onClose'));
        $this->_ws->start();
    }
    
    public function onrequest($request,$response){
        $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
    }


    /*
     *@desc 打开
     ***/
    public function onOpen($serv,$request){
        //创建swoole table 记录fd
        $this->table->set('client-fd',['id'=>$request->fd]);
        $fddata = $this->table->get('client-fd');
        print_r($fddata);
        echo "成功连接websocket服务 {$request->fd}\n";
    }

    /**
     *@desc 消息处理
     **/
    public function onMessage($server,$frame){
        //打印fd
        $fddata = $this->table->get('client-fd');
        print_r($fddata);
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

