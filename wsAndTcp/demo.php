<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-7-30
 * Time: 下午8:13
 */
class Server
{
    private $serv;

    /**
     * @var PDO
     */
    private $pdo;
    public $ws_table;

    public function __construct()
    {
        $this->ws_table = new swoole_table(1024);
        $this->ws_table->column('id',$this->ws_table::TYPE_INT, 4);
        $this->ws_table->column('token',$this->ws_table::TYPE_STRING, 30);
        $this->ws_table->create();

        $this->serv = new swoole_websocket_server("0.0.0.0", 9501);
        $this->serv->set([
                'worker_num' => 1,
                'dispatch_mode' => 2,
                'daemonize' => 0,
        ]);

        $this->serv->on('open', array($this, 'onOpen'));
        $this->serv->on('message', array($this, 'onMessage'));
        $this->serv->on('close',array($this,'onClose'));
        $this->serv->on('Request', array($this, 'onRequest'));

        $port1 = $this->serv->listen("0.0.0.0", 9503, SWOOLE_SOCK_TCP);
        $port1->set(
                [
                'open_eof_split'=> true,//检测自动分包打开（具体干什么的 我不懂）
                'package_eof' => "\r\n"
                ]
                );
        $port1->on('Connect',array($this,'onTcpConnect'));
        $port1->on('Receive', array($this, 'onTcpReceive'));
        $port1->on('Close',array($this,'onTcpClose'));
        $this->serv->start();
    }

    public function onOpen(swoole_websocket_server $_server,$request){
       echo "fd is ----{$request->fd}".PHP_EOL;
       $fd = $request->fd;
       $this->ws_table->set('ws_client_'.$fd,['id'=>$fd,'token'=>'']);
       $_server->push($fd, "hello, welcome\n");
    }


    //显示是哪个客户端发来的数据
    public function onMessage(swoole_websocket_server $_server, $frame)
    {
        print_r($frame); 
        $this->ws_table->set('ws_client_'.$frame->fd,['token'=>$frame->data]);
        foreach($_server->connections as $fd)
        {

            $info = $_server->connection_info($fd);
            echo "onmessage--fd---{$fd}".PHP_EOL;
            print_r($info);
        }
    }


    public function onClose(swoole_websocket_server $_server,$fd){
        echo "websocket client fd {$fd} is close".PHP_EOL;
        $this->ws_table->del('ws_client_'.$fd);//删除table内存表里面的数据
    }


    //服务端接收到不同端口的数据如何处理
    public function onRequest($request, $response)
    {
        echo "request---".PHP_EOL;
        foreach($this->serv->connections as $fd)
        {
            $info = $this->serv->connection_info($fd);
            switch($info['server_port'])
            {
                case 9501:
                    {
                        echo "this is websocket";
                        // websocket
                        if($info['websocket_status'])
                        {

                        }
                        $response->end("");
                    }

                case 9503:
                    {
                         // TCP
                        echo "this is tcp";
                    }
            }

           // var_dump($info);
        }
    }

    /**
     *@desc tcp服务建立连接
     **/
    public function onTcpConnect(swoole_server $serv,$fd){
        echo "tcp clinet  fd:{$fd}  connect".PHP_EOL;
    }

    public function onTcpReceive( swoole_server $serv, $fd, $from_id, $data ) {
        print_r($data);
        $data_list = explode("\r\n", $data);
        print_r($data_list);
        $msg = '';
        var_dump($data_list);
        switch($data_list[0]){
            case '1':
                $msg = "消息1通道";
                break;
            case '2':
                $msg = "消息2通道";
                break;
            case '3':
                $msg = "消息3通道";
                break;
            default:
                $msg = "默认通道";
        }
        foreach($this->ws_table as $key=>$value){
            print_r($value);
            $this->serv->push($value['id'],$msg);
        }
    }

    public function onTcpClose(swoole_server $serv,$fd){
        echo "on tcp client fd {$fd} is close".PHP_EOL;
    }


}

new Server();



