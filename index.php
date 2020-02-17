<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // сярюмнбйю йнмярюмрш цкюбмнцн йнмрпнккепю

// ондйкчвемхе й ад
require_once("lib/coreDB.php"); 
$db = new Msd();
$db->connect();

// ондйкчвемхе й WEB
require_once("lib/coreWEB.php"); 
$web = new VetisAPI();

require_once("lib/msdXML.php");
foreach(vetisSendXML($web,$db,1) as $value)
  {
     echo "$value <br />";
  }


    



