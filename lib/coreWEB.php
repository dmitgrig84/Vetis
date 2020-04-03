<?php header('Content-Type: text/html; charset=utf-8'); defined('MSD') OR die('Прямой доступ к странице запрещён!');
//Кодировка UTF-8
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
