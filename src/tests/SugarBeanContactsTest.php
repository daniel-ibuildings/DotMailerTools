<?php

require_once(__DIR__ . '/../include/SugarBeanContacts.php');

class SugarBeanContactsTest extends PHPUnit_FrameWork_TestCase
{
    public $sugarContact;

    public function setup()
    {
        $this->sugarContact = new SugarBeanContact();
    }
    
    /**
    * @expectedException InvalidArgumentException
     */
    public function testGetBeanContactRequiresBeanId()
    {
        $this->sugarContact->getBeanContact();
    }
    
    public function getBeanContactReturnsSugarBeanObject()
    {
        $contact = $this->getMock('Contact', array(), array(array()));
        $sugContact = $this->getMock('SugarBeanContacts', array(), array(array()));

        $result = $this->sugarContact->getBeanContact(1234);

        $this->assertInstanceOf('SugarBeanContacts', $result);
    }
}