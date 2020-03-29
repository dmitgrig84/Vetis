<?php defined('MSD') OR die('������ ������ � �������� ��������!');

function parseDistribution($db,$xml,$viid,$parsepoint){
    if (parseHB($xml)){
        $xml->registerXPathNamespace('bs', 'http://api.vetrf.ru/schema/cdm/base');
        $xml->registerXPathNamespace('dt', 'http://api.vetrf.ru/schema/cdm/dictionary/v2');
        $xml->registerXPathNamespace('v2', 'http://api.vetrf.ru/schema/cdm/registry/ws-definitions/v2');
        $ns = $xml->getNamespaces(true);            
        $substr=$xml->xpath($parsepoint);
    
        if (count($substr)==0) //���� �� ����� ����� �����, ������ �� ��������
            throw new Exception('������: �� ������ ������ ������������ ���������, �������� ��������� ����������.');
        else{    
            foreach ($substr[0]->children($ns['dt']) as $out_ns){        
            if ($out_ns->children($ns['bs'])){
                $bstag=$out_ns->children($ns['bs']);
                $dttag=$out_ns->children($ns['dt']);
            }
            else{
                $bstag=$out_ns->children($ns['dt'])->enterprise->children($ns['bs']);
                $dttag=$out_ns->children($ns['dt'])->enterprise->children($ns['dt']);
            }
            
            $cmdstr="execute procedure vetis_distributionresult(".$viid.",'";
            $cmdstr.=$bstag->{'uuid'}."','";
            $cmdstr.=$bstag->guid."','";
            $cmdstr.=iconv('utf-8','cp1251',$dttag->name)."','";
            $cmdstr.=$dttag->numberList->enterpriseNumber."','";            
            $cmdstr.=iconv('utf-8','cp1251',$dttag->address->addressView)."','";            
            $cmdstr.=$dttag->registryStatus."',";
            if ($dttag->owner) $cmdstr.="'".$dttag->owner->children($ns['bs'])->guid."'";
            else $cmdstr.="null";
            $cmdstr.=")";
            //var_dump($cmdstr);
            $vi_row=$db->selectWithParams($cmdstr,null,null);             
            }
        return $substr[0]->children($ns['dt'])->count();    
        }
    }
}