 <?php header('Content-Type: text/html; charset=utf-8'); defined('MSD') OR die('Прямой доступ к странице запрещён!');
//Кодировка UTF-8 
function parseXML($db,$XML,$parsetable,$viid,$parsepoint,&$parse_result){
    $countrow=0;
    try{
        switch ($parsetable){
            case 'VETISCONTRACTOR':
                require_once('vetisCONTRACTORparse.php');                                
                $countrow=parseContractor($db,$XML,$viid,$parsepoint);        
                break;
            case 'VETISDISTRIBUTION':
                require_once('vetisDISTRIBUTIONparse.php');                                
                $countrow=parseDistribution($db,$XML,$viid,$parsepoint);        
                break;            
            case 'VETISPRODUCT':
                require_once('vetisPRODUCTparse.php');                
                parseProduct($db,$XML,$viid,$parsepoint);        
                break;             
            case 'VETISIDENTIFIER':
                require_once('vetisIDENTIFIERparse.php');                
                $countrow=parseIdentifier($db,$XML,$viid,$parsepoint);        
                break;                        
            case 'VETISVSD':
                require_once('vetisVSDparse.php');
                $countrow=parseVSD($db,$XML,$viid,$parsepoint);        
                break;             
            case 'VETISUNIT':
                require_once('vetisUNITparse.php');
                parseUnit($db,$XML,$viid,$parsepoint);        
                break;             
            case 'VETISSTOCK':
                require_once('vetisSTOCKparse.php');
                $countrow=parseStock($db,$XML,$viid,$parsepoint);        
                break;                                
            case 'VETISSTOCKLIST':
                require_once('vetisSTOCKparse.php');
                $countrow=parseStockList($db,$XML,$viid,$parsepoint);        
                break;                                            
            default :
                throw new Exception('Не задана таблица для парсинга');        
        }
        $db->selectWithParams("execute procedure vetis_viidresult(".$viid.")",null,null);  
        $parse_result=$parse_result." Обработано: ".$countrow." записей.";
        return true;
    }   
    catch (Exception $e) {        
        $parse_result = $e->getMessage();
        return false;        
    }
}

function parseSAR($db,$xml,$viid){         
    $xml->registerXPathNamespace('apl', 'http://api.vetrf.ru/schema/cdm/application');
    $ns = $xml->getNamespaces(true);
    $substr=$xml->xpath('//apl:application');       
    if (count($substr)==0) //если не нашли точку входа, формат не известен
        throw new Exception('Ошибка: Не верный формат ответа ВЕТИС. Обратитесь к разработчику модуля.');
    else{
        //throw new Exception($substr[0]->children($ns['apl'])->status);        
        switch ($substr[0]->status){
            case 'IN_PROCESS':
                throw new Exception('Запрос обрабатывается на сервере ВЕТИС, запросите ответ чуть позже.');        
            case 'REJECTED':
                $vi_row=$db->selectWithParams("execute procedure vetis_viidresult(".$viid.")",null,null);                
                throw new Exception("Ошибка: ".$substr[0]->children($ns['apl'])->errors->error);
            case 'COMPLETED':                
                return true;
            default :
                throw new Exception('Не известный статус ответа ВЕТИС.'); 
        }
    }
}

function parseHB($xml){         
    $xml->registerXPathNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');    
    
    $ns = $xml->getNamespaces(true);
    $substr=$xml->xpath('//soapenv:Fault');       
    if (count($substr)==0) //если не нашли точку входа, значит это не ошибка
        return true;
    else{
        if ($substr[0]->detail){
            throw new Exception($substr[0]->detail->asXML()); 
        }
        else { 
            throw new Exception($substr[0]->asXML()); 
        }
    }
}