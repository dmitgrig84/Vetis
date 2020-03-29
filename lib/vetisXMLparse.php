 <?php defined('MSD') OR die('������ ������ � �������� ��������!');
 
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
                throw new Exception('�� ������ ������� ��� ��������');        
        }
        $db->selectWithParams("execute procedure vetis_viidresult(".$viid.")",null,null);  
        $parse_result=$parse_result." ����������: ".$countrow." �������.";
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
    if (count($substr)==0) //���� �� ����� ����� �����, ������ �� ��������
        throw new Exception('������: �� ������ ������ ������ �����. ���������� � ������������ ������.');
    else{
        //throw new Exception($substr[0]->children($ns['apl'])->status);        
        switch ($substr[0]->status){
            case 'IN_PROCESS':
                throw new Exception('������ �������������� �� ������� �����, ��������� ����� ���� �����.');        
            case 'REJECTED':
                $vi_row=$db->selectWithParams("execute procedure vetis_viidresult(".$viid.")",null,null);                
                throw new Exception("������: ".iconv('utf-8','cp1251',$substr[0]->children($ns['apl'])->errors->error));
            case 'COMPLETED':                
                return true;
            default :
                throw new Exception('�� ��������� ������ ������ �����.'); 
        }
    }
}

function parseHB($xml){         
    $xml->registerXPathNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');    
    
    $ns = $xml->getNamespaces(true);
    $substr=$xml->xpath('//soapenv:Fault');       
    if (count($substr)==0) //���� �� ����� ����� �����, ������ ��� �� ������
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