<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // ��������� ��������� �������� �����������

// ����������� � ��
require_once("lib/coreDB.php"); 
$db = new Msd();
$db->connect();

// ����������� � WEB
require_once("lib/coreWEB.php"); 
$web = new VetisAPI();

require_once("lib/msdXML.php");
foreach(vetisSendXML($web,$db,1) as $value)
  {
     echo "$value <br />";
  }


    



