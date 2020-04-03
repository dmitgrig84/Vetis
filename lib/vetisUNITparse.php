<?php header('Content-Type: text/html; charset=utf-8'); defined('MSD') OR die('Прямой доступ к странице запрещён!');
//Кодировка UTF-8
function parseUnit($db,$xml,$viid,$parsepoint){         
    $xml->registerXPathNamespace('bs', 'http://api.vetrf.ru/schema/cdm/base');
    $xml->registerXPathNamespace('dt', 'http://api.vetrf.ru/schema/cdm/dictionary/v2');
    $xml->registerXPathNamespace('v2', 'http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2');
     
    $ns = $xml->getNamespaces(true);  //sdf          
    $substr=$xml->xpath($parsepoint);
    
    if (count($substr)==0) //если не нашли точку входа, формат не известен
        throw new Exception('Ошибка: не верный формат поступившего документа, смотрите результат выполнения.');
    else    
        foreach ($substr[0]->children($ns['dt']) as $out_ns){        
            $bstag=$out_ns->children($ns['bs']);
            $dttag=$out_ns->children($ns['dt']);
            
            $cmdstr="execute procedure vetis_unitresult(".$viid.",'";
            $cmdstr.=$bstag->uuid."','";
            $cmdstr.=$bstag->guid."','";
            $cmdstr.=iconv('utf-8','cp1251',$dttag->name)."','";
            $cmdstr.=iconv('utf-8','cp1251',$dttag->fullName)."','";
            $cmdstr.=$dttag->commonUnitGuid."',";                        
            $cmdstr.=$dttag->factor;
            $cmdstr.=")";
            //var_dump($cmdstr);
            $vi_row=$db->selectWithParams($cmdstr,null,null);      
        }
}