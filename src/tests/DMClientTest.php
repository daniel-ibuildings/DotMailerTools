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
     * @expectedException InvalidArgumentException
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
        $client = new DMClient(new StdClass, '', '');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDMClientRequiresPassword()
    {
        $client = new DMClient(new StdClass, 'username', '');
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

        $this->assertInstanceOf('DMContact', $contact);
    }
}
