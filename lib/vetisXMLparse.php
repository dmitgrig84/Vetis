 <?php defined('MSD') OR die('Прямой доступ к странице запрещён!');
function parseXML($db,$XML,$parsetable,$viid,$parsepoint,&$error){
    try{
        switch ($parsetable){
            case 'VETISCONTRACTOR':
                include 'vetisCONTRACTORparse.php';                                
                parseContractor($db,$XML,$viid,$parsepoint);        
                break;
            case 'VETISDISTRIBUTION':
                include 'vetisDISTRIBUTIONparse.php';                                
                parseDistribution($db,$XML,$viid,$parsepoint);        
                break;            
            case 'VETISPRODUCT':
                include 'vetisPRODUCTparse.php';                
                parseProduct($db,$XML,$viid,$parsepoint);        
                break;             
            case 'VETISIDENTIFIER':
                include 'vetisIDENTIFIERparse.php';                
                parseIdentifier($db,$XML,$viid,$parsepoint);        
                break;                        
            case 'VETISVSD':
                include 'vetisVSDparse.php';
                parseVSD($db,$XML,$viid,$parsepoint);        
                break;             
            case 'VETISUNIT':
                include 'vetisUNITparse.php';
                parseUnit($db,$XML,$viid,$parsepoint);        
                break;             
            case 'VETISSTOCK':
                include 'vetisSTOCKparse.php';
                parseStock($db,$XML,$viid,$parsepoint);        
                break;                                
            default :
                throw new Exception('Не задана таблица для парсинга');        
        }     
        return true;
    }   
    catch (Exception $e) {        
        $error = $e->getMessage();
        return false;        
    }
}

function parseSAR($db,$xml,$viid){         
    $xml->registerXPathNamespace('apl', 'http://api.vetrf.ru/schema/cdm/application');
    $ns = $xml->getNamespaces(true);
    $substr=$xml->xpath('//apl:application');       
    if (count($substr)==0){
        throw new Exception('Ошибка: Не верный формат ответа ВЕТИС. Обратитесь к разработчику модуля.');
    }
    else{
        //throw new Exception($substr[0]->children($ns['apl'])->status);        
        switch ($substr[0]->status){
            case 'IN_PROCESS':
                throw new Exception('Запрос обрабатывается на сервере ВЕТИС, запросите ответ чуть позже.');        
            case 'REJECTED':
                $vi_row=$db->selectWithParams("execute procedure vetis_viidresult(".$viid.")",null,null);                
                throw new Exception("Ошибка: ".iconv('utf-8','cp1251',$substr[0]->children($ns['apl'])->errors->error));
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
    $substr=$xml->xpath('soapenv:Fault');       
    if (count($substr)==0){
        return true;
    }
    else{
        if ($substr[0]->detail){
            throw new Exception($substr[0]->detail->asXML()); 
        }
        else { 
            throw new Exception($substr[0]->asXML()); 
        }
    }
}