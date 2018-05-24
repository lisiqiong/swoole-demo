<?php

class Server
{
    private $_serv = null;

    private $_e = null;

    public function __construct($host,$port)
    {
        $this->_serv = new swoole_server($host,$port);
        $this->_serv->set(array(
                    'worker_num' => 8,
                    'daemonize' => false,
                    'max_request' => 10000,
                    'dispatch_mode' => 2,
                    'debug_mode'=> 1,
                    ));

        $this->_serv->on('Start', array($this, 'onStart'));
        $this->_serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->_serv->on('Connect', array($this, 'onConnect'));
        $this->_serv->on('Receive', array($this, 'onReceive'));
        $this->_serv->on('Close', array($this, 'onClose'));
        $this->_serv->start();
    }

    public function onStart($serv)
    {
        echo "start\n";
        cli_set_process_title('reload_master');
    }

    public function onWorkerStart($serv, $worker_id)
    {

        spl_autoload_register(function($class){

                $classPath = str_replace('\\', '/', $class) .'.php';
                if (file_exists($classPath)) {
                    include $classPath;
                }

        });

        $this->_e = new \plugin\Event();

    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";

    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        $serv->reload();//当接收到客户端消息时更新代码，下次再执行work代码时变（onWorkerStart），本次执行还是不变
        $this->_e->say();
        echo "Get Message From Client {$fd}:{$data}\n";
    }


    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }



}
$host = '192.168.0.213';
$port = 9501;
$server = new Server($host,$port);

