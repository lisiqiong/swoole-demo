<?php

echo "process start-time:".date("Y-m-d H:i:s").PHP_EOL.
$workers = [];//用来存储进程
$curls = [
    "http://www.baidu.com",
    "http://www.qq.com",
    "http://www.taobao.com",
    "http://www.sina.com",
];

$count = count($curls);

for($i=0;$i<$count;$i++)
{
    //子进程
    $process = new swoole_process(function(swoole_process $worker) use($i,$curls) {
            //curl
            $content = curlData($curls[$i]);
            //echo $content.PHP_EOL;
            $worker->write($content.PHP_EOL);
            },true);//设置为true将输出写入到管道
    $pid = $process->start();
    $workers[$pid] = $process;
}

foreach($workers as $process){
    echo $process->read();
}


/***
 *@desc 模拟curl
 **/
function curlData($url){
     sleep(1);
    return $url."success".PHP_EOL;
}
echo "process end-time:".date("Y-m-d H:i:s").PHP_EOL;


