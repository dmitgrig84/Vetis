<?php header('Content-Type: text/html; charset=windows-1251'); defined('MSD') OR die('Прямой доступ к странице запрещён!');

function msdXMLDetail($db,$xmlNode,$parentId,$xmlSchema,$sqlParams,$sqlResult,$rowResult){   
    //$db -- подключение к БД
    //$xmlNode -- собираемый XML текущий тег
    //$parentId -- родитель ID записи из таблицы xmlschema
    //$xmlSchema -- набор тегов таблицы xmlschama
    //$sqlParams -- параметр по которому отбираюся записи
    //$sqlResult -- набор записей запроса
    //$rowResult -- номер текущей записи
    $i=1;
    foreach ($xmlSchema as $arr) {
        if (($arr["PARENTID"]<>$arr["ID"])&&($arr["PARENTID"]==$parentId)){//набор тегов таблицы xmlschama которые относятся к определенной ветке
            if (is_null($arr['SEARCH'])) { //если у записи нет запроса
                if (strpos($arr['CODE'],"v")){ //если это тег со значением
                    if ($sqlResult[$rowResult][$i] <> ""){ //если не пусто(почему не проверил на is_null не знаю)
                        $xmlNode->writeElement($arr['NAME'],iconv('cp1251', 'utf-8',$sqlResult[$rowResult][$i]));//добавляем к собираемому XML
                    }
                } else{
                    if (strpos($arr['CODE'],"a")){//если это тег с атрибутом
                        $xmlNode->writeAttribute($arr['NAME'],$sqlResult[$rowResult][$i]);                                          
                    } else{
                        $xmlNode->startElement($arr['NAME']);                                                        
                        msdXMLDetail($db, $xmlNode, $arr["ID"],$xmlSchema, $sqlParams, $sqlResult,0);
                        $xmlNode->endElement();                                                                    
                    }
                }
            } else{
                $sqlQuery = $db->selectWithParams($arr['SEARCH'],$sqlParams,PDO::FETCH_NUM);
                $j=0;
                foreach ($sqlQuery as $query) {                    
                    if (strpos($arr['CODE'],"v")){
                        if ($query[$i] <> ""){                                
                            $xmlNode->writeElement($arr['NAME'],iconv('cp1251', 'utf-8',$query[$i]));                                
                        } 
                    } else{
                        if (strpos($arr['CODE'],"a")){
                            $xmlNode->writeAttribute($arr['NAME'],$query[$i]);
                        } else {
                            $xmlNode->startElement($arr['NAME']);                                
                            msdXMLDetail($db, $xmlNode, $arr["ID"], $xmlSchema, $query[0], $sqlQuery,$j);
                            $xmlNode->endElement();                                                                            
                        }                                                                                   
                    }
                    $j++;
                }
            }
        $i++;
        }
    }
}
    
         
function msdXMLCreate($db,$parentId,$xmlSchema/**/,$sqlParams){
    //$db -- подключение к БД
    //$parentId -- текущее значение корня
    //$xmlSchema -- набор тегов таблицы xmlschama
    //$sqlParams -- параметр по которому отбираюся записи
    foreach ($xmlSchema as $arr) {
        if (($arr["ID"]==$arr["PARENTID"])&&($arr["PARENTID"]==$parentId)){            
            $xml=new XMLWriter();
            $xml->openMemory();
            $xml->startDocument('1.0','UTF-8');
            $xml->startElement($arr["NAME"]);            
            
            if (is_null($arr["SEARCH"])){
                msdXMLDetail($db,$xml,$arr["ID"],$xmlSchema,$sqlParams,null,0);
            }
            else{
                msdXMLDetail($db,$xml,$arr["ID"],$xmlSchema,$sqlParams,$db->selectWithParams($arr["SEARCH"],$sqlParams,PDO::FETCH_NUM),0); 
            }
            $xml->endElement();
            $xml->endDocument();
            
            return $xml->outputMemory(false);            
        }            
    }
}      

function vetisSendXML($web,$db,$viid){        
    try{         
        $return_result=array();
        $vi_row=$db->selectWithParams("select * from vetis_processingreplyid($viid)",null,null);       
    
        foreach ($vi_row as $row) {                    
            $xmlschemaid=$row['XMLSCHEMAID'];
            $vetisidentifierid=$row['VETISIDENTIFIERID'];
            //$vetisconnectwsdlid=$row['VETISCONNECTWSDLID'];
            $resultstr=$row['RESULTSTR'];
            
            try{
                $xs_sql="select * from xmlschema x where x.rootid= $xmlschemaid order by x.code";                     
                $xml= new SimpleXMLElement(msdXMLCreate($db,$xmlschemaid,$db->selectWithParams($xs_sql,null,null),$vetisidentifierid));                   

                $up_sql_source="execute procedure vetis_processingresult($vetisidentifierid,:param,1)";
                $db->updateBlob($up_sql_source,$xml->saveXML());        

                $web->connect($row['WSDL'],$row['LOGIN'],$row['PASS']);                
                $result=$web->request($xml->asXML(),$row['ENDPOINT'],$row['SOAPACTION'],SOAP_1_1);
                if (!is_null($result)){
                    $up_sql_result="execute procedure vetis_processingresult($vetisidentifierid,:param,5)";
                    $db->updateBlob($up_sql_result,$result->saveXML());        
                    
                    include 'vetisXMLparse.php';
                    if (parseXML($db,$result,$row['PARSETABLE'],$vetisidentifierid,$row['PARSEPOINT'],$parse_result)){
                        array_push($return_result," Успешно: ".$resultstr." ".$parse_result);
                    }
                    else {
                        array_push($return_result,$parse_result);
                    }
                } else{
                    array_push($return_result," Ошибка: нет ответа от сервера.");                    
                }
            }
            catch (Exception $fault) {
                $up_sql_result="execute procedure vetis_processingresult($vetisidentifierid,:param,-1)";            
                $faultMessage=iconv('utf-8','cp1251',$fault->getMessage());
                $db->updateBlob($up_sql_result,$faultMessage);            
                array_push($return_result,"Ошибка: $resultstr $faultMessage");
            }
        }
        if (empty($return_result)){
            array_push($return_result,"Нет не обработанных записей."); 
        }                
    }
    catch (Exception $e) {    
        $eMessage=$e->getMessage();
        array_push($return_result,"Ошибка: $eMessage");        
    }     
    return $return_result;
}



    


