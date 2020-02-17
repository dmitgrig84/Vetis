<?php
$xmlstr = <<<XML
<?xml version='1.0'?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ws="http://api.vetrf.ru/schema/cdm/ikar/ws-definitions"
    xmlns:base="http://api.vetrf.ru/schema/cdm/base"
    xmlns:ikar="http://api.vetrf.ru/schema/cdm/ikar">
   <soapenv:Header/>
   <soapenv:Body>
      <ws:getRegionListByCountryRequest>
         <base:listOptions>
            <base:count>3</base:count>
            <base:offset>0</base:offset>
         </base:listOptions>
         <!--ikar:countryGuid>f133f1fd-7fa2-da91-d069-24df647497421</ikar:countryGuid-->
      </ws:getRegionListByCountryRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;
?>

<?php header('Content-Type: text/html; charset=windows-1251'); define("MSD", ""); // УСТАНОВКА КОНСТАНТЫ ГЛАВНОГО КОНТРОЛЛЕРА

$xml = new SimpleXMLElement($xmlstr);
//var_dump($xml->asXML());
    
try{
    require_once("lib/coreWEB.php"); 
    $web = new VetisAPI();
    $wsdl='http://api.vetrf.ru/schema/platform/ikar/services/IkarService_v1.4_pilot.wsdl';  
    $web ->connect($wsdl, 'treydlogistik-191226', 'M3nD6kfY9');
    $result = $web->wsdl->__doRequest($xml->asXML(),'https://api2.vetrf.ru:8002/platform/ikar/services/IkarService','',1,0);
    //https://api2.vetrf.ru:8002/platform/ikar/services/IkarService
    //GetRegionListByCountry
    //$result = $web->wsdl->GetRegionListByCountry($xml->asXML());//,'https://api2.vetrf.ru:8002/platform/ikar/services/IkarService','GetRegionListByCountry', SOAP_1_1,0);
    var_dump($result);     
    //var_dump($web->wsdl->__getLastRequestHeaders());
}
 catch (SoapFault $e){
    var_dump($e);
    
 }
