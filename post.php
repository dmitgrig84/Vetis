<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // ”—“¿ÕŒ¬ ¿  ŒÕ—“¿Õ“€ √À¿¬ÕŒ√Œ  ŒÕ“–ŒÀÀ≈–¿

if (empty($_POST["dbuser"])||empty($_POST["dbpass"])||empty($_POST["viid"])){
    echo 'ŒÚÒÛÚÒÚ‚Û˛Ú ‚ıÓ‰Ì˚Â ‰‡ÌÌ˚Â.';     
}
else {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    // œŒƒ Àﬁ◊≈Õ»≈   ¡ƒ WIN1251 
    require_once("lib/coreDB.php"); 
    $dsn = 'firebird:dbname=192.168.0.11/3050:/base/msd.gdb;charset=win1251;dialect=3;role=rdb$admin'; // Õ¿«¬¿Õ»≈ ¡¿«€ ƒÀﬂ —¿…“¿'; 
    $db = new Msd();
    $db->connect($dsn,$_POST["dbuser"],$_POST["dbpass"]);

    // œŒƒ Àﬁ◊≈Õ»≈   WEB
    require_once("lib/coreWEB.php"); 
    $web = new VetisAPI();

    require_once("lib/msdXMLcreate.php");
    foreach(vetisSendXML($web,$db,$_POST["viid"]) as $value)    
        {
            echo $value;
        }
        
}