<?php

class MysqliDb{
    //私有的属性
    private static $dbcon=false;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $db;
    private $charset;
    private $link;

    //私有的构造方法
    public function  __construct($config){

        //可选链接超时时间
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->user = $config['user'];
        $this->pass = $config['password'];
        $this->db = $config['database'];
        $this->charset = $config['charset'];

        //连接数据库
        $this->db_connect();
        //设置字符集
        $this->db_charset();
    }

    //连接数据库
    private function db_connect(){
        $this->link=mysqli_connect($this->host,$this->user,$this->pass,$this->db);
        if(!$this->link){
            echo "数据库连接失败<br>";
            echo "错误编码".mysqli_errno($this->link)."<br>";
            echo "错误信息".mysqli_error($this->link)."<br>";
            exit;
        }
    }

    //设置字符集
    private function db_charset(){
        mysqli_set_charset($this->link,$this->charset);
    }

    /**
     *@desc 获取区域信息
     **/
    public function getOperation($sql){
        $query = $this->execute($sql);
        return mysqli_fetch_all($query,1);
    }

    /***
     *@desc 执行sql语句
     **/
    public function execute($sql){
        $res=mysqli_query($this->link,$sql);
        if(!$res){
            return false;
            //echo "sql语句执行失败<br>";
            //echo "错误编码是".mysqli_errno($this->link)."<br>";
            //echo "错误信息是".mysqli_error($this->link)."<br>";
        }
        return $res;
    }
}



