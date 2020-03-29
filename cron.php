<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // ��������� ��������� �������� �����������
/*������ ������ ������ ����������� ������ ���� � 19:00(�������� �������) � 19:10(��������� ����������)*/
    
ini_set('display_errors',1);
ini_set('date.timezone','Etc/GMT-3');
error_reporting(E_ALL);
$logfile=__DIR__."/cron.log";
    
// ����������� � ��
require_once("lib/coreDB.php");
$dsn = 'firebird:dbname=192.168.0.11/3050:/base/msd.gdb;charset=win1251;dialect=3;role=rdb$admin'; // �������� ���� ��� �����
$db = new Msd();
$db->connect($dsn,'sysdba','userkey');

require_once("lib/coreWEB.php"); // ����������� � WEB �������
$web = new VetisAPI();    

require_once("lib/msdXMLcreate.php"); //�������� ��������� �������/������    
$flagtime=strtotime(date("d.m.Y 18:00:00")); //������� ������� ����������
//�������� ������� � ��� ��������//��������� � ������� ����������
try{
    foreach ($db->selectWithParams("select id from vetisconnect",null,null) as $connect){ //������������ ��� ����������� ������������ �������                     
        requestVSD($db,$connect['ID'],$web,$logfile,$wheninsert);//��������� ��� 
            
        if ((strtotime(date(date("d.m.Y h:i:s")))>$flagtime)&&(strtotime($wheninsert)<$flagtime)) {
            //���� ��� ������ ������ ������ �� �� ������� �����, �� �� ������ ������� ������ � ������� ��������
            requestVSD($db,$connect['ID'],$web,$logfile,$wheninsert);
        }
        
        sendSale($db,$connect['ID'],$web,$logfile); //���������� ���������
    }
}
catch (Exception $e) {        
    error_log(date("d.m.Y H:i:s")." ".$e->getMessage()."\r\n",3,$logfile);        
}
    
function requestVSD($db,$connectid,$web,$logfile,&$wheninsert){//��������� ���� ��� ���������� �� ������
    foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connectid.",16,'begindate=".date("d.m.Y").";enddate=".date("d.m.Y")."')",null,null) as $viid){
        //16 - ��� VETISSOAPACTION ��������� ������ ���������/���������� ��� �� ������ //������� �� ����
        foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
            error_log(date("d.m.Y H:i:s")." ".$value."\r\n",3,$logfile);
        $wheninsert=$viid['WHENINSERT'];
    }        
}
    
function sendSale($db,$connectid,$web,$logfile){
    foreach ($db->selectWithParams("select bv.saleid from buytrans_vetissaleview(cast('today' as timestamp)-7,cast('today' as timestamp),".$connectid.") bv where bv.vetisstatusid in (1,6)",null,null) as $sale){
        //11 - ��� VETISSOAPACTION ���������� ��������� ���������
        foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connectid.",11,'saleid=".$sale['SALEID']."')",null,null) as $viid){
            foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
                error_log(date("d.m.Y H:i:s")." ".$value."\r\n",3,$logfile);
        }
        sleep(1);            
    }
}