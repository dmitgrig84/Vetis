<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // ��������� ��������� �������� �����������

if (empty($_POST["dbuser"])||empty($_POST["dbpass"])||empty($_POST["viid"])){
    echo '����������� ������� ������.';     
}
else {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    // ����������� � �� WIN1251 
    require_once("lib/coreDB.php"); 
    $dsn = 'firebird:dbname=192.168.0.11/3050:/base/msd.gdb;charset=win1251;dialect=3;role=rdb$admin'; // �������� ���� ��� �����'; 
    $db = new Msd();
    $db->connect($dsn,$_POST["dbuser"],$_POST["dbpass"]);

    // ����������� � WEB
    require_once("lib/coreWEB.php"); 
    $web = new VetisAPI();

    require_once("lib/msdXMLcreate.php");
    foreach(vetisSendXML($web,$db,$_POST["viid"]) as $value)    
        {
            echo $value;
        }
        
}