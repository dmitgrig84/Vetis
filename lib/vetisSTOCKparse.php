<?php defined('MSD') OR die('Прямой доступ к странице запрещён!');

function parseStock($db,$xml,$viid,$parsepoint){                   
    if (parseSAR($db,$xml,$viid)){        
        $xml->registerXPathNamespace('bs', 'http://api.vetrf.ru/schema/cdm/base');
        $xml->registerXPathNamespace('dt', 'http://api.vetrf.ru/schema/cdm/dictionary/v2');
        $xml->registerXPathNamespace('vd', 'http://api.vetrf.ru/schema/cdm/mercury/vet-document/v2');
        $xml->registerXPathNamespace('merc', 'http://api.vetrf.ru/schema/cdm/mercury/g2b/applications/v2');            
        
        $ns = $xml->getNamespaces(true);            
        $substr=$xml->xpath($parsepoint);
    
    if (count($substr)==0){        
        throw new Exception('Ошибка: Не верный формат ответа ВЕТИС. Обратитесь к разработчику модуля.');    
    }
    else{
        $tagStockEntry=$substr[0]->children($ns['merc'])->stockEntry;
        $tagVetDocument=$substr[0]->children($ns['merc'])->vetDocument;
        
        $cmdstr="execute procedure vetis_stockresult(".$viid.",'";
            $cmdstr.=$tagStockEntry->children($ns['bs'])->uuid."','";
            $cmdstr.=$tagStockEntry->children($ns['bs'])->guid."','";
            $cmdstr.=$tagStockEntry->children($ns['bs'])->status."',";
            if ($tagStockEntry->children($ns['bs'])->previous) {$cmdstr.="'".$tagStockEntry->children($ns['bs'])->previous."','";} else {$cmdstr.="null,'";}
            $cmdstr.=$tagStockEntry->children($ns['vd'])->entryNumber."',";
            $cmdstr.=$tagStockEntry->children($ns['vd'])->batch->volume.",'";            
            $cmdstr.=$tagStockEntry->children($ns['vd'])->vetDocument->children($ns['bs'])->uuid."','";            
            $cmdstr.=$tagVetDocument->children($ns['bs'])->uuid."','";
            $cmdstr.=$tagVetDocument->children($ns['vd'])->vetDStatus."','";
            $cmdstr.=(string)$tagVetDocument->attributes()->qualifier."')";            
        //throw new Exception($cmdstr);
        $vi_row=$db->selectWithParams($cmdstr,null,null);  
        }
    }
}