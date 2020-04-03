<?php defined('MSD') OR die('Прямой доступ к странице запрещён!');
//Кодировка UTF-8
function parseIdentifier($db,$xml,$viid,$parsepoint){         
    $xml->registerXPathNamespace('ws', 'http://api.vetrf.ru/schema/cdm/application/ws-definitions');
    $ns = $xml->getNamespaces(true);            
    $substr=$xml->xpath($parsepoint);
    
    if (count($substr)==0) //если не нашли точку входа, формат не известен
        throw new Exception('Ошибка: не верный формат поступившего документа, смотрите результат выполнения.');
    else{
        $cmdstr="execute procedure vetis_identifierresult(".$viid.",'";
        $cmdstr.=$substr[0]->application->applicationId."')";
        //var_dump($cmdstr);        
        $vi_row=$db->selectWithParams($cmdstr,null,null);      
        return 1;       
    }
}