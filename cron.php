<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // ��������� ��������� �������� �����������
    /*������ ������ ������ ����������� ������ ���� � 19:00(�������� �������) � 19:10(��������� ����������)*/
    
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    
    // ����������� � ��
    require_once("lib/coreDB.php"); 
    $dsn = 'firebird:dbname=192.168.0.11/3050:/base/msd.gdb;charset=win1251;dialect=3;role=rdb$admin'; // �������� ���� ��� �����
    $db = new Msd();
    $db->connect($dsn,'sysdba','userkey');
    
    require_once("lib/coreWEB.php"); // ����������� � WEB �������
    $web = new VetisAPI();    
    require_once("lib/msdXMLcreate.php"); //�������� ��������� �������/������    
    $flagtime=strtotime(date("d.m.Y 18:00:00"));
    //�������� ������� � ��� ��������//��������� � ������� ����������
    foreach ($db->selectWithParams("select id from vetisconnect",null,null) as $connect){ //������������ ��� ����������� ������������ �������
        
        foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connect['ID'].",16,'vetDocumentType=OUTGOING;begindate=".date("d.m.Y").";enddate=".date("d.m.Y")."')",null,null) as $viid){
            //16 - ��� VETISSOAPACTION ��������� ������ ���������/���������� ��� �� ������ //������� �� ����                                           
            foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
                error_log(date("d.m.Y H:m:s")." ".$value."\r\n",3,'cron.log');
        }
            
        if ((strtotime(date(date("d.m.Y h:i:s")))>$flagtime)&&(strtotime($viid['WHENINSERT'])<$flagtime)) {//���� ��� ������ ������ ������ �� �� ������� �����, �� �� ������ ������� ������ � ������� ��������
            foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connect['ID'].",16,'vetDocumentType=OUTGOING;begindate=".date("d.m.Y").";enddate=".date("d.m.Y")."')",null,null) as $viid){
                //16 - ��� VETISSOAPACTION ��������� ������ ���������/���������� ��� �� ������ //������� �� ����                                           
                foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
                    error_log(date("d.m.Y H:m:s")." ".$value."\r\n",3,'cron.log');
            }
        }
    }



    

    
    