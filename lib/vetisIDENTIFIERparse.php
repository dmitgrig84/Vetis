<?php  defined('MSD') OR die('Прямой доступ к странице запрещён!');

function parseIdentifier($db,$xml,$viid,$parsepoint){         
    $xml->registerXPathNamespace('ws', 'http://api.vetrf.ru/schema/cdm/application/ws-definitions');
    $ns = $xml->getNamespaces(true);            
    $substr=$xml->xpath($parsepoint);
    
    if (count($substr)==0)
        throw new Exception('Ошибка: не верный формат поступившего документа, смотрите результат выполнения.');
    else{
        $cmdstr="execute procedure vetis_identifierresult(".$viid.",'";
        $cmdstr.=$substr[0]->application->applicationId."')";
        //var_dump($cmdstr);        
        $vi_row=$db->selectWithParams($cmdstr,null,null);      
        }
}