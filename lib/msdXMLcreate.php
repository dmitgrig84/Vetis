<?php header('Content-Type: text/html; charset=windows-1251'); defined('MSD') OR die('Прямой доступ к странице запрещён!');

function msdXMLDetail($db,$xmlNode,$parentId,$xmlSchema,$sqlParams,$sqlResult){   
    $i=1;    
    foreach ($xmlSchema as $arr) {        
        if (($arr["PARENTID"]<>$arr["ID"])&&($arr["PARENTID"]==$parentId)){                                                                      
            if (is_null($arr['SEARCH'])) {
                if (strpos($arr['CODE'],"v")){                    
                    if ($sqlResult[0][$i] <> ""){                        
                        $xmlNode->writeElement($arr['NAME'],iconv('cp1251', 'utf-8',$sqlResult[0][$i]));                        
                    }
                } else
                    if (strpos($arr['CODE'],"a")){
                        $xmlNode->writeAttribute($arr['NAME'],$sqlResult[0][$i]);                                          
                    } else{
                        $xmlNode->startElement($arr['NAME']);                                                        
                        msdXMLDetail($db, $xmlNode, $arr["ID"],$xmlSchema, $sqlParams, $sqlResult);
                        $xmlNode->endElement();                                                                    
                    }
                
            } else{
                    $sqlQuery = $db->selectWithParams($arr['SEARCH'],$sqlParams,PDO::FETCH_NUM); 
                    foreach ($sqlQuery as $query) {
                        if (strpos($arr['CODE'],"v")){
                            if ($sqlResult[0][$i] <> ""){
                                
                                $xmlNode->writeElement($arr['NAME'],iconv('cp1251', 'utf-8',$sqlQuery[0][$i]));                                
                            } 
                        } else{
                             if (strpos($arr['CODE'],"a")){
                                $xmlNode->writeAttribute($arr['NAME'],$sqlQuery[0][$i]);
                             } else {
                                $xmlNode->startElement($arr['NAME']);                                
                                msdXMLDetail($db, $xmlNode, $arr["ID"], $xmlSchema, $sqlQuery[0][0], $sqlQuery);
                                $xmlNode->endElement();                                                                            
                             }                                                                                   
                        }
                    }                    
            }
        $i++;
        }
    }
}
    
         
function msdXMLCreate($db,$parentId,$xmlSchema,$sqlParams){   
    foreach ($xmlSchema as $arr) {
        if (($arr["ID"]==$arr["PARENTID"])&&($arr["PARENTID"]==$parentId)){            
            $xml=new XMLWriter();
            $xml->openMemory();
            $xml->startDocument('1.0','UTF-8');
            $xml->startElement($arr["NAME"]);            
            
            if (is_null($arr["SEARCH"])){
                msdXMLDetail($db,$xml,$arr["ID"],$xmlSchema,$sqlParams,null);
            }
            else{
                msdXMLDetail($db,$xml,$arr["ID"],$xmlSchema,$sqlParams,$db->selectWithParams($arr["SEARCH"],$sqlParams,PDO::FETCH_NUM)); 
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
                    if (parseXML($db,$result,$row['PARSETABLE'],$vetisidentifierid,$row['PARSEPOINT'],$error_parse)){
                        array_push($return_result," Успешно: $resultstr");
                    }
                    else {
                        array_push($return_result,$error_parse);
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



    


