<?php defined('MSD') OR die('Прямой доступ к странице запрещён!');

function parseContractor($db,$xml,$viid,$parsepoint){         
    if (parseHB($xml)){
        $xml->registerXPathNamespace('bs', 'http://api.vetrf.ru/schema/cdm/base');
        $xml->registerXPathNamespace('dt', 'http://api.vetrf.ru/schema/cdm/dictionary/v2');
        $xml->registerXPathNamespace('v2', 'http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2');
        $ns = $xml->getNamespaces(true);            
        $substr=$xml->xpath($parsepoint);
    
        if (count($substr)==0) //если не нашли точку входа, формат не известен
            throw new Exception('Ошибка: не верный формат поступившего документа, смотрите результат выполнения.');        
        else    
            foreach ($substr[0]->children($ns['dt']) as $out_ns){        
            $bstag=$out_ns->children($ns['bs']);
            $dttag=$out_ns->children($ns['dt']);
            
            $cmdstr="execute procedure vetis_contractorresult(".$viid.",'";
            $cmdstr.=$bstag->{'uuid'}."','";
            $cmdstr.=$bstag->guid."',";
            $cmdstr.=$dttag->type.",'";
            $cmdstr.=iconv('utf-8','cp1251',$dttag->name)."','";
            $cmdstr.=iconv('utf-8','cp1251',$dttag->fullName)."','";
            $cmdstr.=$dttag->inn."','";
            $cmdstr.=$dttag->kpp."','";
            $cmdstr.=$dttag->fio."')";
            //var_dump($cmdstr);            
            $vi_row=$db->selectWithParams($cmdstr,null,null);             
            }
    }
}