<?php
 
namespace app\common;
include 'predis.php';
//include 'Task.php';
/**
 *    socket面向对象的编译
 */
class Ws
{
    CONST HOST='0.0.0.0';
    CONST PORT='9501';
    CONST PROCESS_NAME='worker_process';
    public $ws=null;
    public $getmsg=null;
    public $server=null;

    public function __construct()
    {   
        $this->ws=new \swoole_websocket_server(self::HOST,self::PORT);
        $this->ws->set([
                //启动task必须要设置其数量
                'worker_num' => 4,
                'task_worker_num' => 2,
        ]);
        //监听新端口
        $this->server=$this->ws->listen(self::HOST, 9502, SWOOLE_SOCK_TCP);
        //关闭websocket模式
        $this->server->set([
                'open_websocket_protocol' => false,
        ]);

        $this->ws->on("start", [$this, 'onStart']);
        $this->ws->on("workerstart",[$this,'onworkerstart']);
        $this->ws->on('open',[$this,'onopen']);
        $this->server->on("receive", [$this, 'onReceive']);
        $this->ws->on('task',[$this,'onTask']);
        $this->ws->on('finish',[$this,'onFinish']);
        $this->ws->on('message',[$this,'onmessage']);
        $this->ws->on('close',[$this,'onclose']);
        $this->server->on("close", [$this, 'onclose']);
        $this->ws->start();
    }

    //监听数据接收事件
    public function onReceive($serv, $fd, $from_id, $data)
    {
        $shuju=json_decode($data,true);
        print_r($shuju).PHP_EOL;
        $key = Predis::getInstance()->get('fd');
        echo "key---{$key}";
        $this->ws->push($key, $data);
        if (empty($shuju['data'])) {
            //$this->ws->push(Predis::getInstance()->get('fd'), $data);
            $this->ws->push($fd,$data);
        }else{
            //执行异步任务
            $this->ws->task($shuju);
            // $this->ws->push(Predis::getInstance()->get('fd'), '异步任务开始了');
        }
    }

    /**
     * 设置进程名，为后续平滑重启进程
     * @param $server
     */
    public function onStart($server) {
        swoole_set_process_name("live_master");
    }      
    
    public function onworkerstart($server,$wid){
        swoole_set_process_name(SELF::PROCESS_NAME.'_'.$wid);
        echo "workerstart .....".PHP_EOL;
    }


    /**
      监听开启事件的回调
     */
    public function onopen($server, $request)
    {
        echo "客户端fd：{$request->fd} 开启 ".PHP_EOL;
        Predis::getInstance()->set('fd',$request->fd);
    }

    /**
      监听接收事件的回调
     */
    public function onmessage($server, $frame)
    {
        $server->push($frame->fd, "{$frame->data}");
    }
    /**
      监听关闭事件的回调
     */
    public function onclose($ser, $fd)
    {
        echo "关闭的fd:{$fd}".PHP_EOL;
    }

    /**
     *   $serv           服务
     *   $task_id        任务ID，由swoole扩展内自动生成，用于区分不同的任务
     *   $src_worker_id  $task_id和$src_worker_id组合起来才是全局唯一的，不同的worker进程投递的任务ID可能会有相同
     *   $data           是任务的内容
     */
    public function onTask($serv,$task_id,$src_worker_id,$data)
    {
        echo "进入了异步任务";
        //引入任务
      //  $obj = new Task;
      //  $method = $data['data'];
      //  foreach ($data['arr'] as $v) {
      //      $flag = $obj->$method($v, $serv);
      //  }
      //  return $flag; // 告诉worker
    }

    /**
     *   $task_id        是任务的ID
     *   $data           是任务处理的结果内容
     */
    public function onFinish($serv,$task_id,$data)
    {
        print_r($data).'/n';
    }

}

new Ws();

