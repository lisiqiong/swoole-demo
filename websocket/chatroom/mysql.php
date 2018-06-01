<?php

require("./config.php");

class mysql{
    public $_db;

    /**
     *@desc 初始化swoole mysql
     ***/
    public function __construct($dbConfig){
        $this->_db = new swoole_mysql;
        $this->connect($this->_db,$dbConfig); 
    }

    /**
     **@desc 连接mysql数据库
     ***/
    public function connect($db,$dbConfig){
        $db->connect($dbConfig,function($db,$r){
                if ($r === false) {
                    var_dump($db->connect_errno, $db->connect_error);
                    die;
                }
                //$sql = 'show tables';
                /*$sql = "select id,username,name,sex,address,mobile from fight_member ";
                $db->query($sql, function(swoole_mysql $db, $r) {
                        if ($r === false)
                        {
                            var_dump($db->error, $db->errno);
                        }
                        elseif ($r === true )
                        {
                           // var_dump($db->affected_rows, $db->insert_id);
                            //print_r($db->affected_rows,$db->insert_id);
                        }
                        print_r($r);
                        //var_dump($r);
                        $db->close();
                });*/
                return $db;
        });
        //return $db;
    }
    


}

$dbConfig = $config['mysql']; 
$mysql = new mysql($dbConfig);
$sql = "select id,username,name,sex,address,mobile from fight_member ";
print_r($mysql);
/*$mysql->query($sql,function(swoole_mysql $mysql,$r){
     print_r($r);
});*/
