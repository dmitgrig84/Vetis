<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // УСТАНОВКА КОНСТАНТЫ ГЛАВНОГО КОНТРОЛЛЕРА
    /*Данный скрипт должен запускаться каждый день в 19:00(отправка запроса) и 19:10(получение результата)*/
    
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    
    // Подключение к БД
    require_once("lib/coreDB.php"); 
    $dsn = 'firebird:dbname=192.168.0.11/3050:/base/msd.gdb;charset=win1251;dialect=3;role=rdb$admin'; // НАЗВАНИЕ БАЗЫ ДЛЯ САЙТА
    $db = new Msd();
    $db->connect($dsn,'sysdba','userkey');
    
    require_once("lib/coreWEB.php"); // Подключение к WEB сервису
    $web = new VetisAPI();    
    require_once("lib/msdXMLcreate.php"); //Основная обработка запроса/ответа    
    $flagtime=strtotime(date("d.m.Y 18:00:00"));
    //Создание запроса и его отправка//Получение и парсинг результата
    foreach ($db->selectWithParams("select id from vetisconnect",null,null) as $connect){ //обрабатываем все необходимые поднадзорные обьекты
        
        foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connect['ID'].",16,'vetDocumentType=OUTGOING;begindate=".date("d.m.Y").";enddate=".date("d.m.Y")."')",null,null) as $viid){
            //16 - тип VETISSOAPACTION Получение списка созданных/измененных ВСД за период //смотрим за день                                           
            foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
                error_log(date("d.m.Y H:m:s")." ".$value."\r\n",3,'cron.log');
        }
            
        if ((strtotime(date(date("d.m.Y h:i:s")))>$flagtime)&&(strtotime($viid['WHENINSERT'])<$flagtime)) {//если был раньше создан запрос но не получен ответ, то мы должны послать запрос в текущем веремени
            foreach ($db->selectWithParams("select * from buytrans_vetisrequest(".$connect['ID'].",16,'vetDocumentType=OUTGOING;begindate=".date("d.m.Y").";enddate=".date("d.m.Y")."')",null,null) as $viid){
                //16 - тип VETISSOAPACTION Получение списка созданных/измененных ВСД за период //смотрим за день                                           
                foreach(vetisSendXML($web,$db,$viid['VIID']) as $value)
                    error_log(date("d.m.Y H:m:s")." ".$value."\r\n",3,'cron.log');
            }
        }
    }



    

    
    