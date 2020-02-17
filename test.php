<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // ÓÑÒÀÍÎÂÊÀ ÊÎÍÑÒÀÍÒÛ ÃËÀÂÍÎÃÎ ÊÎÍÒĞÎËËÅĞÀ


    ini_set('display_errors',1);
    error_reporting(E_ALL);
    // ÏÎÄÊËŞ×ÅÍÈÅ Ê ÁÄ
    require_once("lib/coreDB.php"); 
    $dsn = 'firebird:dbname=192.168.0.11/3050:/base/msd.gdb;charset=win1251;dialect=3;role=rdb$admin'; // ÍÀÇÂÀÍÈÅ ÁÀÇÛ ÄËß ÑÀÉÒÀ
    $db = new Msd();
    $db->connect($dsn,'sysdba','userkey');    

    // ÏÎÄÊËŞ×ÅÍÈÅ Ê WEB
    require_once("lib/coreWEB.php"); 
    $web = new VetisAPI();

    require_once("lib/msdXMLcreate.php");
    foreach(vetisSendXML($web,$db,117) as $value)    
        {
            echo $value.PHP_EOL;
        }