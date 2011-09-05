<?php

class DMClient 
{
    public $soapClient;
    public $username;
    public $password;
    
    public function __construct($wsdlUri, $username, $password)
    {
        $this->soapClient = new SoapClient($wsdlUri);
        $this->username = $username;
        $this->password = $password;
    }
}