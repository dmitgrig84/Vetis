<?php  defined('MSD') OR die('Прямой доступ к странице запрещён!');

function parseVSD($db,$xml,$viid,$parsepoint){
    if (parseSAR($db,$xml,$viid)){        
        $xml->registerXPathNamespace('bs', 'http://api.vetrf.ru/schema/cdm/base');
        $xml->registerXPathNamespace('dt', 'http://api.vetrf.ru/schema/cdm/dictionary/v2');
        $xml->registerXPathNamespace('vd', 'http://api.vetrf.ru/schema/cdm/mercury/vet-document/v2');
        $xml->registerXPathNamespace('gb', 'http://api.vetrf.ru/schema/cdm/mercury/g2b/applications/v2');        
    
        $ns = $xml->getNamespaces(true);            
        $substr=$xml->xpath($parsepoint);
            
        if (parseEmptyList($db,$substr,$viid))        
            foreach ($substr[0]->children($ns['vd']) as $out_ns){        
            $bstag=$out_ns->children($ns['bs']);
            $vdtag=$out_ns->children($ns['vd']);
            $cctag=$vdtag->certifiedConsignment;
            $batchtag=$cctag->batch;
            $transportNumber=$cctag->transportInfo->transportNumber->children($ns['vd']);
            $dopfd=$batchtag->dateOfProduction->firstDate->children($ns['dt']);
            $exdfd=$batchtag->expiryDate->firstDate->children($ns['dt']);
            
            $cmdstr="execute procedure vetis_vsdresult(".$viid.",'";
            $cmdstr.=$bstag->uuid."','";
            $cmdstr.=$vdtag->issueDate."','";
            $cmdstr.=$vdtag->vetDForm."','";
            $cmdstr.=$vdtag->vetDType."','";
            $cmdstr.=$vdtag->vetDStatus."','";
            $cmdstr.=$vdtag->lastUpdateDate."','";
            $cmdstr.=$cctag->consignor->children($ns['dt'])->businessEntity->children($ns['bs'])->guid."','";            
            $cmdstr.=$cctag->consignor->children($ns['dt'])->enterprise->children($ns['bs'])->guid."','";
            $cmdstr.=$cctag->consignee->children($ns['dt'])->businessEntity->children($ns['bs'])->guid."','";                        
            $cmdstr.=$cctag->consignee->children($ns['dt'])->enterprise->children($ns['bs'])->guid."',";            
            $cmdstr.=$cctag->transportInfo->transportType.",'";
            $cmdstr.=iconv('utf-8','cp1251',$transportNumber[0])."','";            
            $cmdstr.=$cctag->transportStorageType."','";
            $cmdstr.=$batchtag->productItem->children($ns['bs'])->guid."','";
            $cmdstr.=$batchtag->volume."','";
            $cmdstr.=$batchtag->unit->children($ns['bs'])->guid."','";
            
            $cmdstr.=$dopfd->year."-".$dopfd->month."-".$dopfd->day;
            $cmdstr.=($dopfd->hour)?" ".$dopfd->hour.":00','":"','";
            
            $cmdstr.=$exdfd->year."-".$exdfd->month."-".$exdfd->day;
            $cmdstr.=($exdfd->hour)?" ".$exdfd->hour.":00','":"','";
            
            $cmdstr.=$batchtag->perishable."',";
            $cmdstr.=($batchtag->origin->productItem)?"'".$batchtag->origin->productItem->children($ns['bs'])->guid."','":"null,'";
            
            $cmdstr.=$batchtag->origin->country->children($ns['bs'])->guid."','";
            $cmdstr.=$batchtag->origin->producer->children($ns['dt'])->enterprise->children($ns['bs'])->guid."','";
            $cmdstr.=$batchtag->origin->producer->children($ns['dt'])->role."','";
            $cmdstr.=$vdtag->authentication->purpose->children($ns['bs'])->uuid."','";
            $cmdstr.=$vdtag->authentication->purpose->children($ns['bs'])->guid."','";            
            $cmdstr.=$vdtag->authentication->cargoInspected."','";                        
            $cmdstr.=$vdtag->authentication->cargoExpertized."','";                        
            $cmdstr.=iconv('utf-8','cp1251',$vdtag->authentication->locationProsperity)."',";
            $cmdstr.=$vdtag->referencedDocument->type.",'";
            $cmdstr.=$vdtag->referencedDocument->issueNumber."','";
            $cmdstr.=$vdtag->referencedDocument->issueDate."',";
            $cmdstr.=$vdtag->referencedDocument->relationshipType;                
            $cmdstr.=")";
            //var_dump($cmdstr);
            $vi_row=$db->selectWithParams($cmdstr,null,null);      
            }
        
    }
}