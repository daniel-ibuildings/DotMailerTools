<?php

require_once(__DIR__ . '/../include/DMClient.php');

class DMClientTest extends PHPUnit_Framework_TestCase
{
    private $wsdl;

    public function setUp()
    {
        $this->wsdl = __DIR__ . '/fixtures/dotMailer.wsdl.xml';
    }

    public function tearDown()
    {
        unset($this->wsdl);
    }

    /**
     * @expectedException PHPUnit_Framework_Error 
     */
    public function testDMClientRequiresASoapClientInstance()
    {
        $client = new DMClient(new StdClass, 'username', 'password');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDMClientRequiresUsername()
    {
        $client = new DMClient(new SoapClient($this->wsdl), '', '');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDMClientRequiresPassword()
    {
        $client = new DMClient(new SoapClient($this->wsdl), 'username', '');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetContactByEmailAcceptsOnlyValidEmailAddresses()
    {
        $client = new DMClient(new SoapClient($this->wsdl), 'username', 'password');
        $client->getContactByEmail('invalidemail');
    }

    /**
     * @expectedException InvalidCredentialsException
     */
    public function testSoapMethodCalledWithInvalidCredentials()
    {
        $exception = new SoapFault('soap:Server', 'ERROR_INVALID_LOGIN');

        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'GetContactByWithInvalidCredentials');
        $dotMailerClient->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->throwException($exception));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->getContactByEmail('doesnt@matter.com');
    }

    /**
     * @expectedException ContactNotFoundException
     */
    public function testGetContactByEmailThrowsAnExceptionforNonExistingContact()
    {
        $exception = new SoapFault('soap:Server', 'ERROR_CONTACT_NOT_FOUND');

        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'GetContactByNonExistingEmail');
        $dotMailerClient->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->throwException($exception));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->getContactByEmail('not@found.org');
    }

    public function testGetContactByEmailReturnsDMContactForExistingContact()
    {
        $response = new StdClass;
        $response->GetContactByEmailResult = new StdClass;
        $response->GetContactByEmailResult->OptInType = false;

        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'GetContactByValidEmail');
        $dotMailerClient->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->returnValue($response));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        $contact = $client->getContactByEmail('valid@address.com');

        $this->assertInstanceOf('stdClass', $contact);
    }

    /**
     * @expectedException InvalidArgumentException 
     */
    public function testUpdateContactAcceptsAValidId()
    {
        $contact = $this->getMock('DMContact', null, array(array()));
        
        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'UpdateContactRequiresAnId');
        
        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->updateContact('', $contact);
    }

    /**
     * @expectedException PHPUnit_Framework_Error 
     */
    public function testUpdateContactAcceptsParamTypeOfDMContact()
    {
        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'UpdateContact');
        
        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->updateContact(123, new stdClass);
    }

    public function testUpdateContactAppendsIdToParams()
    {
        $contact = $this->getMock('DMContact', null, array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));
        
        $params = array();
        $params['username'] = 'username';
        $params['password'] = 'password';
        $params['contact']  = $contact->toSoapParam(array());
        $params['contact']['ID'] = 123;
        
        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'UpdateContactAppendsIdToParam');
        $dotMailerClient->expects($this->once())
                        ->method('UpdateContact')
                        ->with($this->equalTo($params));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->updateContact(123, $contact);
    }

    public function testUpdateContactSuccessful()
    {
        $contact = $this->getMock('DMContact', null, array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));
        
        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'UpdateContactSuccess');
        $dotMailerClient->expects($this->once())
                        ->method('UpdateContact')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        
        $this->assertTrue($client->updateContact(123, $contact));
    }

    /**
     * @expectedException FailedUpdateException
     */
    public function testUpdateContactFails()
    {
        $exception = new SoapFault('soap:Server', 'Failed to update contact');
        
        $contact = $this->getMock('DMContact', null, array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'UpdateContactFailed');
        $dotMailerClient->expects($this->any())
                        ->method('UpdateContact')
                        ->will($this->throwException($exception));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->updateContact(123, $contact);
    }

    /**
     * @expectedException PHPUnit_Framework_Error 
     */
    public function testCreatContactAcceptsParamTypeOfDMContact()
    {
        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'CreateContact');
        
        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->createContact(new stdClass);
    }

    public function testCreateContactSuccessful()
    {
        $contact = $this->getMock('DMContact', null, array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));
        
        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'CreateContactSuccess');
        $dotMailerClient->expects($this->once())
                        ->method('CreateContact')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        
        $this->assertTrue($client->createContact($contact));
    }

    /**
     * @expectedException FailedCreateException
     */
    public function testCreateContactFails()
    {
        $exception = new SoapFault('soap:Server', 'Failed to create contact');
        
        $contact = $this->getMock('DMContact', null, array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $dotMailerClient = $this->getMockFromWsdl($this->wsdl, 'CreateContactFailed');
        $dotMailerClient->expects($this->any())
                        ->method('CreateContact')
                        ->will($this->throwException($exception));

        $client = new DMClient($dotMailerClient, 'username', 'password');
        $client->createContact($contact);
    }
}
