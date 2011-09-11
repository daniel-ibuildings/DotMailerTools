<?php

require_once(__DIR__ . '/../include/DMSuppressionContact.php');

class DMSuppressionContactTest extends PHPUnit_FrameWork_TestCase
{
    public function setUp()
    {
        $dataMap = array();
        $dataMap['email']     = array('soap' => 'Email',     'sugar' => 'email1');
        $dataMap['firstName'] = array('soap' => 'FIRSTNAME', 'sugar' => 'first_name');
        $dataMap['fullName']  = array('soap' => 'FULLNAME',  'sugar' => 'name');
        $dataMap['lastName']  = array('soap' => 'LASTNAME',  'sugar' => 'last_name');

        $this->supContact = new DMSuppressionContact($dataMap);
    }
    
    public function testInitFromSoapSetsSuppressionContacts()
    {
        $supContact = unserialize(file_get_contents(__DIR__ . '/fixtures/suppressionContact'));

        $this->supContact->initFromSoap($supContact);
        
        $this->assertEquals('im.sugar.section@example.tw', $this->supContact->email);
        $this->assertEquals('UnSubscribed', $this->supContact->reason);
        $this->assertTrue($this->supContact->optIn);
    }
}