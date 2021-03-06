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
     *$test = bin2hex($data);
     **/
    public function onReceive($serv,$fd,$reactor_id,$data){
        $data = bin2hex($data); 
        $content = "接收的客户端数据为:{$data}";
        $this->writeLog($content);

        $inputArr = $this->analysisHexStr($data);    
        $contentCodeStr = implode('',$inputArr['content_code']);
        $headerCodeStr = implode('',$inputArr['header_code']);
        $crcMix = $this->getCrc8($contentCodeStr);
        $crcAllMix = $this->getCrc8($headerCodeStr.$contentCodeStr.$crcMix);
        if($crcMix == $inputArr['crc_mix'][0] && $crcAllMix == $inputArr['crc_all_mix'][0]  ){
            echo "crc第一次校验结果为:".$crcMix.',第二次校验结果为:'.$crcAllMix."\n";
        }else{
            echo "校验失败\n";
        }

        $sendStr = "01 4d 05 00 5e 55 00 d8";
        $crcCode = $this->getCrc8('014d05005e5500d8');
        $sendInfo = $sendStr." ".$crcCode;
        echo "sendInfo--".$sendInfo;
        $res = $this->show($sendInfo);  
        echo "$res---".$res;
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
      @desc crc8校验
      $str = '01 4d 05 00 5e 50 2d 33 33 31 2f 33 33 32 00 00 00 00 00 00 00 ff ff ff ff ff ff ff ff 33 2e 32 2e 30 00 00 00 33 2e 32 2e 35 00 00 00 38 36 38 33 34 35 30 33 31 34 32 36 35 39 36 30 30 30 30 30 30 30 30 30 30 30 33 2e 32 2e 32 00 00 00 48 5a 38 33 4b 43 00 00 64 00 64 00 64 00 aa aa 00 d8 00 f5 3c';
     * @param $hexStr：六十进制字符串
     * 
     */
    function getCrc8($hexStr){
        $strBin = pack('H*',$hexStr);
        $crcTable = [
            0x00, 0x5e, 0xbc, 0xe2, 0x61, 0x3f, 0xdd, 0x83, 
            0xc2, 0x9c, 0x7e, 0x20, 0xa3, 0xfd, 0x1f, 0x41, 
            0x9d, 0xc3, 0x21, 0x7f, 0xfc, 0xa2, 0x40, 0x1e, 
            0x5f, 0x01, 0xe3, 0xbd, 0x3e, 0x60, 0x82, 0xdc, 
            0x23, 0x7d, 0x9f, 0xc1, 0x42, 0x1c, 0xfe, 0xa0, 
            0xe1, 0xbf, 0x5d, 0x03, 0x80, 0xde, 0x3c, 0x62, 
            0xbe, 0xe0, 0x02, 0x5c, 0xdf, 0x81, 0x63, 0x3d, 
            0x7c, 0x22, 0xc0, 0x9e, 0x1d, 0x43, 0xa1, 0xff, 
            0x46, 0x18, 0xfa, 0xa4, 0x27, 0x79, 0x9b, 0xc5, 
            0x84, 0xda, 0x38, 0x66, 0xe5, 0xbb, 0x59, 0x07, 
            0xdb, 0x85, 0x67, 0x39, 0xba, 0xe4, 0x06, 0x58, 
            0x19, 0x47, 0xa5, 0xfb, 0x78, 0x26, 0xc4, 0x9a, 
            0x65, 0x3b, 0xd9, 0x87, 0x04, 0x5a, 0xb8, 0xe6, 
            0xa7, 0xf9, 0x1b, 0x45, 0xc6, 0x98, 0x7a, 0x24, 
            0xf8, 0xa6, 0x44, 0x1a, 0x99, 0xc7, 0x25, 0x7b, 
            0x3a, 0x64, 0x86, 0xd8, 0x5b, 0x05, 0xe7, 0xb9, 
            0x8c, 0xd2, 0x30, 0x6e, 0xed, 0xb3, 0x51, 0x0f, 
            0x4e, 0x10, 0xf2, 0xac, 0x2f, 0x71, 0x93, 0xcd, 
            0x11, 0x4f, 0xad, 0xf3, 0x70, 0x2e, 0xcc, 0x92, 
            0xd3, 0x8d, 0x6f, 0x31, 0xb2, 0xec, 0x0e, 0x50, 
            0xaf, 0xf1, 0x13, 0x4d, 0xce, 0x90, 0x72, 0x2c, 
            0x6d, 0x33, 0xd1, 0x8f, 0x0c, 0x52, 0xb0, 0xee, 
            0x32, 0x6c, 0x8e, 0xd0, 0x53, 0x0d, 0xef, 0xb1, 
            0xf0, 0xae, 0x4c, 0x12, 0x91, 0xcf, 0x2d, 0x73, 
            0xca, 0x94, 0x76, 0x28, 0xab, 0xf5, 0x17, 0x49, 
            0x08, 0x56, 0xb4, 0xea, 0x69, 0x37, 0xd5, 0x8b, 
            0x57, 0x09, 0xeb, 0xb5, 0x36, 0x68, 0x8a, 0xd4, 
            0x95, 0xcb, 0x29, 0x77, 0xf4, 0xaa, 0x48, 0x16, 
            0xe9, 0xb7, 0x55, 0x0b, 0x88, 0xd6, 0x34, 0x6a, 
            0x2b, 0x75, 0x97, 0xc9, 0x4a, 0x14, 0xf6, 0xa8, 
            0x74, 0x2a, 0xc8, 0x96, 0x15, 0x4b, 0xa9, 0xf7, 
            0xb6, 0xe8, 0x0a, 0x54, 0xd7, 0x89, 0x6b, 0x35 
                ];

        $length = strlen($strBin);
        $crc = 0;
        $pos = 0;
        while($length>0){
            $crc = $crcTable[$crc ^ ord($strBin[$pos])];
            $pos++;
            $length--;
        }
        //return $crc;
        return dechex($crc);
    }


    function analysisHexStr($hexStr){
        $data = ['header_code'=>[],'content_code'=>[],'crc_mix'=>'','crc_all_mix'=>[]];
        $infoArr = explode(' ',$hexStr);
        $count = count($infoArr);
        $data['header_code'] = array_slice($infoArr,0,5);
        $data['content_code'] = array_slice($infoArr,5,$count-7);
        $data['crc_mix'] = array_slice($infoArr,-2,1);
        $data['crc_all_mix'] = array_slice($infoArr,-1);
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




