<?php defined('MSD') OR die('Прямой доступ к странице запрещён!');

function saveStock($db,$tagStockEntry,$viid,$ns){
    $cmdstr="execute procedure vetis_stockresult(".$viid.",'";
    $cmdstr.=$tagStockEntry->children($ns['bs'])->uuid."','";
    $cmdstr.=$tagStockEntry->children($ns['bs'])->guid."','";
    $cmdstr.=$tagStockEntry->children($ns['bs'])->status."',";
    $cmdstr.=($tagStockEntry->children($ns['bs'])->previous)?"'".$tagStockEntry->children($ns['bs'])->previous."','":"null,'";
    $cmdstr.=$tagStockEntry->children($ns['vd'])->entryNumber."',";
    $cmdstr.=$tagStockEntry->children($ns['vd'])->batch->volume.",'";            
    $cmdstr.=$tagStockEntry->children($ns['vd'])->vetDocument->children($ns['bs'])->uuid."')";            
    //throw new Exception($cmdstr);
    $vi_row=$db->selectWithParams($cmdstr,null,null);  
}   

function saveVSDstatus($db,$tagVetDocument,$viid,$ns){
    $cmdstr="execute procedure vetis_vsdstatusresult(".$viid.",'";
    $cmdstr.=$tagVetDocument->children($ns['bs'])->uuid."','";
    $cmdstr.=$tagVetDocument->children($ns['vd'])->vetDStatus."',";
    $cmdstr.=($tagVetDocument->attributes()->qualifier)?"'".(string)$tagVetDocument->attributes()->qualifier."'":"null";            
    $cmdstr.=")";
    //throw new Exception($cmdstr);
    $db->selectWithParams($cmdstr,null,null);  
}

function parseStock($db,$xml,$viid,$parsepoint){                   
    if (parseSAR($db,$xml,$viid)){        
        $xml->registerXPathNamespace('bs', 'http://api.vetrf.ru/schema/cdm/base');
        $xml->registerXPathNamespace('dt', 'http://api.vetrf.ru/schema/cdm/dictionary/v2');
        $xml->registerXPathNamespace('vd', 'http://api.vetrf.ru/schema/cdm/mercury/vet-document/v2');
        $xml->registerXPathNamespace('merc', 'http://api.vetrf.ru/schema/cdm/mercury/g2b/applications/v2');            
        
        $ns = $xml->getNamespaces(true);            
        $substr=$xml->xpath($parsepoint);
    
        if (count($substr)==0) //если не нашли точку входа, формат не известен
            throw new Exception('Ошибка: Не верный формат ответа ВЕТИС. Обратитесь к разработчику модуля.');            
        else{            
            foreach ($substr[0]->children($ns['merc']) as $tagEntry){
                if ($tagEntry->getName()=='stockEntry')
                    saveStock($db,$tagEntry,$viid,$ns);
                if ($tagEntry->getName()=='vetDocument')
                    saveVSDstatus($db,$tagEntry,$viid,$ns);
            }
        }
    }
}

function parseStockList($db,$xml,$viid,$parsepoint){                   
    if (parseSAR($db,$xml,$viid)){        
        $xml->registerXPathNamespace('bs', 'http://api.vetrf.ru/schema/cdm/base');
        $xml->registerXPathNamespace('dt', 'http://api.vetrf.ru/schema/cdm/dictionary/v2');
        $xml->registerXPathNamespace('vd', 'http://api.vetrf.ru/schema/cdm/mercury/vet-document/v2');
        $xml->registerXPathNamespace('merc', 'http://api.vetrf.ru/schema/cdm/mercury/g2b/applications/v2');            
        
        $ns = $xml->getNamespaces(true);            
        $substr=$xml->xpath($parsepoint);
    
        if (parseEmptyList($db,$substr,$viid))//проверяем на пустой список
            foreach ($substr[0]->children($ns['vd']) as $tagStockEntry)
                saveStock($db,$tagStockEntry,$viid,$ns);
            
    }
}
