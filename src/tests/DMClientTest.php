<?php

require_once('../include/DMClient.php');

class DMClientTest extends PHPUnit_Framework_TestCase
{
    public function testSOAPClientIsInstantiated()
    {
        //setup
        $wsdl     = 'fixtures/dotMailer.wsdl.xml';
        $username = 'test';
        $password = 'test';

        $client   = new DMClient($wsdl, $username, $password);
        
        //assertions
        $this->assertInstanceOf('SoapClient', $client->soapClient);
        $this->assertEquals($username, $client->username);
        $this->assertEquals($password, $client->password);
    }
    
}