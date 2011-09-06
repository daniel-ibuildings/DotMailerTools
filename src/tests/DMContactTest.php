<?php

class DMContactTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dataMap = array();
        $this->dataMap['email']     = array('soap' => 'Email',     'sugar' => 'email1');
        $this->dataMap['firstName'] = array('soap' => 'FIRSTNAME', 'sugar' => 'first_name');
        $this->dataMap['fullName']  = array('soap' => 'FULLNAME',  'sugar' => 'name');
        $this->dataMap['lastName']  = array('soap' => 'LASTNAME',  'sugar' => 'last_name');

        $this->contact = new DMContact();
    }

    public function tearDown()
    {
        unset($this->dataMap);
        unset($this->contact);
    }

    public function testContactInitializesItselfFromSoapResponse()
    {
        $response = unserialize(file_get_contents(__DIR__ . '/fixtures/dotMailerContact'));

        $this->contact->initFromSoap($this->dataMap, $response->GetContactByEmailResult);

        $this->assertEquals('275789931', $this->contact->id);
        $this->assertEquals('im.sugar.section@example.tw', $this->contact->email);
        $this->assertEquals('Janelle', $this->contact->firstName);
        $this->assertEquals('Janelle Swann', $this->contact->fullName);
        $this->assertEquals('Swann', $this->contact->lastName);
        $this->assertTrue($this->contact->optIn);
    }

    public function testContactInitializesItselfFromSugarBean()
    {
        $bean = (object) unserialize(file_get_contents(__DIR__ . '/fixtures/sugarContact'));

        $this->contact->initFromSugarBean($this->dataMap, $bean);

        $this->assertEquals('bf9bc1c6-dc81-1115-1fe0-4e54d12e8f15', $this->contact->id);
        $this->assertEquals('im.sugar.section@example.tw', $this->contact->email);
        $this->assertEquals('Janelle', $this->contact->firstName);
        $this->assertEquals('Janelle Swann', $this->contact->fullName);
        $this->assertEquals('Swann', $this->contact->lastName);
        $this->assertTrue($this->contact->optIn);
    }

    public function testComparingUnmodifiedContactFromDifferentSourcesReturnsTrue()
    {
        $response = unserialize(file_get_contents(__DIR__ . '/fixtures/dotMailerContact'));
        $this->contact->initFromSoap($this->dataMap, $response->GetContactByEmailResult);

        $bean = (object) unserialize(file_get_contents(__DIR__ . '/fixtures/sugarContact'));
        $anotherContact = new DMContact();
        $anotherContact->initFromSugarBean($this->dataMap, $bean);

        $this->assertTrue($this->contact->compare($anotherContact));
    }

    public function testComparingModifiedContactFromDifferentSourcesReturnsFalse()
    {
        $response = unserialize(file_get_contents(__DIR__ . '/fixtures/dotMailerContact'));
        $this->contact->initFromSoap($this->dataMap, $response->GetContactByEmailResult);
        $this->contact->optIn = false;

        $bean = (object) unserialize(file_get_contents(__DIR__ . '/fixtures/sugarContact'));
        $anotherContact = new DMContact();
        $anotherContact->initFromSugarBean($this->dataMap, $bean);

        $this->assertFalse($this->contact->compare($anotherContact));
    }

    public function testContactReturnsItselfAsASoapParamater()
    {
        $bean = (object) unserialize(file_get_contents(__DIR__ . '/fixtures/sugarContact'));

        $this->contact->initFromSugarBean($this->dataMap, $bean);

        $soapParam = $this->contact->getAsSoapParam($this->dataMap);

        $this->assertTrue(is_array($soapParam['DataFields']));

        var_dump($soapParam);
    }
}
