<?php header('Content-Type: text/html; charset=windows-1251'); defined('MSD') OR die('Прямой доступ к странице запрещён!');

class VetisAPI{

var $wsdl;
var $result;

//$wsdlUrl = $url . '?WSDL';
//$oSoapClient = new SoapClient($wsdlUrl, $params);

function connect($wsdl,$login,$pass) {
    $this->wsdl = new SOAPClient($wsdl,[                                                                               
                                        'login' => $login,
                                        'password' => $pass,
                                        'exceptions' => true,
                                        'trace' => true
                                        ]
                                );
}

function request($query,$endpoint,$request,$soapversion){
    $xml= $this->wsdl->__doRequest($query,$endpoint,$request,$soapversion,0);
    if (is_null($xml))
        return null;
    else 
        return new SimpleXMLElement($xml);
}

}
