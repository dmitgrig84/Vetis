<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // УСТАНОВКА КОНСТАНТЫ ГЛАВНОГО КОНТРОЛЛЕРА
/*Данный скрипт должен запускаться каждый день в 19:00(отправка запроса) и 19:10(получение результата)*/
    
ini_set('display_errors',1);
ini_set('date.timezone','Etc/GMT-3');
error_reporting(E_ALL);
$logfile=__DIR__."/cron.log";
    
// Подключение к БД
require_once("lib/coreDB.php");
$dsn = 'firebird:dbname=192.168.0.11/3050:/base/msd.gdb;charset=win1251;dialect=3;role=rdb$admin'; // НАЗВАНИЕ БАЗЫ ДЛЯ САЙТА
$db = new Msd();
$db->connect($dsn,'sysdba','userkey');

require_once("lib/coreWEB.php"); // Подключение к WEB сервису
$web = new VetisAPI();    

require_once("lib/msdXMLcreate.php"); //Основная обработка запроса/ответа    
$flagtime=strtotime(date("d.m.Y 18:00:00")); //отсечка времени выполнения
//Создание запроса и его отправка//Получение и парсинг результата
try{
    foreach ($db->selectWithParams("select id from vetisconnect",null,null) as $connect){ //обрабатываем все необходимые поднадзорные обьекты                     
        requestVSD($db,$connect['ID'],$web,$logfile,$wheninsert);//Получение ВСД 
            
        if ((strtotime(date(date("d.m.Y h:i:s")))>$flagtime)&&(strtotime($wheninsert)<$flagtime)) {
            //если был раньше создан запрос но не получен ответ, то мы должны послать запрос в текущем веремени
            requestVSD($db,$connect['ID'],$web,$logfile,$wheninsert);
        }
        
        sendSale($db,$connect['ID'],$web,$logfile); //Отправляем накладные
    }
}
catch (Exception $e) {        
    error_log(date("d.m.Y H:i:s")." ".$e->getMessage()."\r\n",3,$logfile);        
}
    
function requestVSD($db,$connectid,$web,$logfile,&$wheninsert){//получение всех ВСД измененных за период
    foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connectid.",16,'begindate=".date("d.m.Y").";enddate=".date("d.m.Y")."')",null,null) as $viid){
        //16 - тип VETISSOAPACTION Получение списка созданных/измененных ВСД за период //смотрим за день
        foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
            error_log(date("d.m.Y H:i:s")." ".$value."\r\n",3,$logfile);
        $wheninsert=$viid['WHENINSERT'];
    }        
}
    
function sendSale($db,$connectid,$web,$logfile){
    foreach ($db->selectWithParams("select bv.saleid from buytrans_vetissaleview(cast('today' as timestamp)-7,cast('today' as timestamp),".$connectid.") bv where bv.vetisstatusid in (1,6)",null,null) as $sale){
        //11 - тип VETISSOAPACTION Отправляем доступные накладные
        foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connectid.",11,'saleid=".$sale['SALEID']."')",null,null) as $viid){
            foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
                error_log(date("d.m.Y H:i:s")." ".$value."\r\n",3,$logfile);
        }
        sleep(1);            
    }
}