<?php header('Content-Type: text/html; charset=windows-1251'); defined('MSD') OR die('Прямой доступ к странице запрещён!');

class Msd
{


var $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

var $db;
var $query;
var $err;
var $result;
var $data;
var $fetch;

function connect($dsn,$user,$pass) {    
    $this->db = new PDO($dsn, $user, $pass, $this->opt);    
}    

function close() {
$this->db = null;
$this->err = null;
$this->result = null;
$this->data = null;
$this->fetch = null;    
}

function selectWithParams($sqlStatment, $sqlParams, $fetchMode){    
    try {
        if ($this->db->inTransaction()) $this->db->commit();
        $this->db->beginTransaction();   
        $stmt = $this->db->prepare($sqlStatment);
        if (!is_null($sqlParams))
            $stmt->bindValue(1, $sqlParams); //сдесь надо переделать на массив       
        $stmt->execute();
        $result=$stmt->fetchAll($fetchMode);
        $this->db->commit();
        return $result;
    }
    catch (PDOException $e) {
        // Если соединение произошло и транзакция стартовала, откатываем её
        if ($this->db &&  $this->db->inTransaction())
            $this->db->rollback();
        throw $e;
    }         
}



function select(&$query,$sql) {    
    $query=$this->db->query($sql);
}

function updateBlob($sql,$blob) {    
    try {
        if ($this->db->inTransaction())
            $this->db->commit();    
        $this->db->beginTransaction();   
        $stmt = $this->db->prepare($sql);  
        $stmt->bindParam(':param', $blob, PDO::PARAM_LOB);//сдесь надо переделать на массив                
        $stmt->execute();
        $this->db->commit();
    } 
    catch (PDOException $e) {
        // Если соединение произошло и транзакция стартовала, откатываем её
        if ($this->db &&  $this->db->inTransaction())
            $this->db->rollback();
        throw $e;
    } 
}

}
