<?php

require_once(__DIR__ . '/../include/DMClient.php');

class DMClientTest extends PHPUnit_Framework_TestCase
{
    private $_wsdl;

    public function setUp()
    {
        $this->_wsdl = __DIR__ . '/fixtures/dotMailer.wsdl.xml';
    }

    public function tearDown()
    {
        unset($this->_wsdl);
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
        $client = new DMClient(new SoapClient($this->_wsdl), '', '');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDMClientRequiresPassword()
    {
        $client = new DMClient(new SoapClient($this->_wsdl), 'username', '');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetContactByEmailAcceptsOnlyValidEmailAddresses()
    {
        $client = new DMClient(new SoapClient($this->_wsdl), 'name', 'pwd');
        $client->getContactByEmail('invalidemail');
    }

    /**
     * @expectedException InvalidCredentialsException
     */
    public function testSoapMethodCalledWithInvalidCredentials()
    {
        $exception = new SoapFault('soap:Server', 'ERROR_INVALID_LOGIN');

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'GetContactByWithInvalidCredentials');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->throwException($exception));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->getContactByEmail('doesnt@matter.com');
    }

    /**
     * @expectedException ContactNotFoundException
     */
    public function testGetContactByEmailThrowsAnExceptionforNonExistingContact()
    {
        $exception = new SoapFault('soap:Server', 'ERROR_CONTACT_NOT_FOUND');

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'GetContactByNonExistingEmail');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->throwException($exception));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->getContactByEmail('not@found.org');
    }

    public function testGetContactByEmailReturnsDMContactForExistingContact()
    {
        $response = new StdClass;
        // @codingStandardsIgnoreStart
        $response->GetContactByEmailResult = new StdClass;
        $response->GetContactByEmailResult->OptInType = false;
        // @codingStandardsIgnoreEnd

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'GetContactByValidEmail');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->returnValue($response));

        $client = new DMClient($soapMock, 'username', 'password');
        $contact = $client->getContactByEmail('valid@address.com');

        $this->assertInstanceOf('stdClass', $contact);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUpdateContactAcceptsAValidId()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'UpdateContactRequiresAnId');

        $client = new DMClient($soapMock, 'username', 'password');
        $client->updateContact('', $contact);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testUpdateContactAcceptsParamTypeOfDMContact()
    {
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'UpdateContact');

        $client = new DMClient($soapMock, 'username', 'password');
        $client->updateContact(123, new stdClass);
    }

    public function testUpdateContactAppendsIdToParams()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $params = array();
        $params['username'] = 'username';
        $params['password'] = 'password';
        $params['contact']  = $contact->toSoapParam();
        $params['contact']['ID'] = 123;

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'UpdateContactAppendsIdToParam');
        $soapMock->expects($this->once())
                        ->method('UpdateContact')
                        ->with($this->equalTo($params));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->updateContact(123, $contact);
    }

    public function testUpdateContactSuccessful()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'UpdateContactSuccess');
        $soapMock->expects($this->once())
                        ->method('UpdateContact')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($soapMock, 'username', 'password');

        $this->assertTrue($client->updateContact(123, $contact));
    }

    /**
     * @expectedException FailedUpdateException
     */
    public function testUpdateContactFails()
    {
        $exception = new SoapFault('soap:Server', 'Failed to update contact');

        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'UpdateContactFailed');
        $soapMock->expects($this->any())
                        ->method('UpdateContact')
                        ->will($this->throwException($exception));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->updateContact(123, $contact);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testCreatContactAcceptsParamTypeOfDMContact()
    {
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'CreateContact');

        $client = new DMClient($soapMock, 'username', 'password');
        $client->createContact(new stdClass);
    }

    public function testCreateContactAddsDataToParams()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $params = array();
        $params['username'] = 'username';
        $params['password'] = 'password';
        $params['contact']  = $contact->toSoapParam();
        $params['contact']['ID'] = '-1';

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'CreateContactAddsDataToParams');
        $soapMock->expects($this->once())
                        ->method('CreateContact')
                        ->with($this->equalTo($params));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->createContact($contact);
    }

    public function testCreateContactSuccessful()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'CreateContactSuccess');
        $soapMock->expects($this->once())
                        ->method('CreateContact')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($soapMock, 'username', 'password');

        $this->assertTrue($client->createContact($contact));
    }

    /**
     * @expectedException FailedCreateException
     */
    public function testCreateContactFails()
    {
        $exception = new SoapFault('soap:Server', 'Failed to create contact');

        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->expects($this->any())
                ->method('toSoapParam')
                ->will($this->returnValue(array()));

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'CreateContactFailed');
        $soapMock->expects($this->any())
                        ->method('CreateContact')
                        ->will($this->throwException($exception));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->createContact($contact);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSyncContactAcceptsParamTypeOfDMContact()
    {
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'SyncContact');

        $client = new DMClient($soapMock, 'username', 'password');
        $client->syncContact(new stdClass);
    }

    public function testSyncContactSuccessfulOnUpdate()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->email = 'some@email.org';

        $response = new StdClass;
        // @codingStandardsIgnoreStart
        $response->GetContactByEmailResult = new StdClass;
        $response->GetContactByEmailResult->ID = 123;
        $response->GetContactByEmailResult->OptInType = false;
        // @codingStandardsIgnoreEnd

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'SyncContactSuccessfulOnUpdate');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->returnValue($response));
        $soapMock->expects($this->once())
                        ->method('UpdateContact')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($soapMock, 'username', 'password');

        $this->assertTrue($client->syncContact($contact));
    }

    public function testSyncContactRespectsExistingSettings()
    {
        $contact = new DMContact(array());
        $contact->email = 'some@email.org';

        $params = array();
        $params['username'] = 'username';
        $params['password'] = 'password';
        $params['contact']  = $contact->toSoapParam();
        $params['contact']['ID'] = 123;
        $params['contact']['EmailType'] = 'PlainText';
        $params['contact']['AudienceType'] =  'B2C';

        $response = new StdClass;
        // @codingStandardsIgnoreStart
        $response->GetContactByEmailResult = new StdClass;
        $response->GetContactByEmailResult->ID = 123;
        $response->GetContactByEmailResult->OptInType = false;
        $response->GetContactByEmailResult->EmailType = 'PlainText';
        $response->GetContactByEmailResult->AudienceType = 'B2C';
        // @codingStandardsIgnoreEnd

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'SyncContactRespectsExistingSettings');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->returnValue($response));
        $soapMock->expects($this->once())
                        ->method('UpdateContact')
                        ->with($params)
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($soapMock, 'username', 'password');

        $client->syncContact($contact);
    }

    public function testSyncContactSuccessfulOnCreation()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->email = 'some@email.org';

        $exception = new SoapFault('soap:Server', 'Failed to create contact');

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'SyncContactSuccessfulOnCreation');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->throwException(new ContactNotFoundException));
        $soapMock->expects($this->once())
                        ->method('CreateContact')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($soapMock, 'username', 'password');

        $this->assertTrue($client->syncContact($contact));
    }

    /**
     * @expectedException FailedUpdateException
     */
    public function testSyncContactFailsOnUpdate()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->email = 'some@email.org';

        $response = new StdClass;
        // @codingStandardsIgnoreStart
        $response->GetContactByEmailResult = new StdClass;
        $response->GetContactByEmailResult->ID = 123;
        $response->GetContactByEmailResult->OptInType = false;
        // @codingStandardsIgnoreEnd

        $exception = new SoapFault('soap:Server', 'Failed to update contact');

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'SyncContactFailsOnUpdate');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->returnValue($response));
        $soapMock->expects($this->once())
                        ->method('UpdateContact')
                        ->will($this->throwException($exception));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->syncContact($contact);
    }

    /**
     * @expectedException FailedCreateException
     */
    public function testSyncContactFailsOnCreation()
    {
        $contact = $this->getMock('DMContact', array(), array(array()));
        $contact->email = 'some@email.org';

        $exception = new SoapFault('soap:Server', 'Failed to create contact');

        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'SyncContactFailsOnCreation');
        $soapMock->expects($this->once())
                        ->method('GetContactByEmail')
                        ->will($this->throwException(new ContactNotFoundException));
        $soapMock->expects($this->once())
                        ->method('CreateContact')
                        ->will($this->throwException($exception));

        $client = new DMClient($soapMock, 'username', 'password');
        $client->syncContact($contact);
    }
    /**
     * @expectedException InvalidArgumentException 
     */
    public function testGetSuppressionListAcceptsAValidStartDate()
    {
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'GetSuppressionListRequiresStartDate');
        
        $client = new DMClient($soapMock, 'username', 'password');
        $client->GetSuppressionList('');
    }

    public function testGetSuppressionListSuccessful()
    {
        $startDate = '2011-09-01T12:00:00';

        $dotMailerClient = $this->getMockFromWsdl($this->_wsdl, 'SuppressionList');
        $dotMailerClient->expects($this->once())
                        ->method('ListSuppressedContacts')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($dotMailerClient, 'username', 'password');

        $contacts = $client->getSuppressionList($startDate);

        $this->assertInstanceOf('stdClass',$contacts);
    }

    /**
     * @expectedException FailedToFetchContactsException
     */
    public function testGetSuppressionListFails()
    {
        $startDate = '2011-09-01T12:00:00';

        $exception = new SoapFault('soap:Server', 'Failed to fetch suppression contacts');

        $dotMailerClient = $this->getMockFromWsdl($this->_wsdl, 'SuppressionListFailed');
        $dotMailerClient->expects($this->any())
                        ->method('ListSuppressedContacts')
                        ->will($this->throwException($exception));

        $client   = new DMClient($dotMailerClient, 'username', 'password');
        $contacts = $client->getSuppressionList($startDate);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetCampaignFailsWithNoParam()
    {
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'ListSentCampaignsWithActivitySinceDate');
        
        $client = new DMClient($soapMock, 'username', 'password');
        $client->getCampaigns('');
    }

    public function testGetCampaignSuccessful()
    {
        $startDate = '2011-09-01T12:00:00';
        
        $dotMailerClient = $this->getMockFromWsdl($this->_wsdl, 'ListSentCampaignsWithActivitySinceDateSuccess');
        $dotMailerClient->expects($this->once())
                        ->method('ListSentCampaignsWithActivitySinceDate')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($dotMailerClient, 'username', 'password');

        $campaigns = $client->getCampaigns($startDate);

        $this->assertEquals(0, count($campaignActivities));
    }
    
    /**
     * @expectedException FailedToFetchContactsException
     */
    public function testGetCampaignFails()
    {
        $startDate = '2011-09-01T12:00:00';

        $exception = new SoapFault('soap:Server', 'Failed to fetch campaigns');

        $dotMailerClient = $this->getMockFromWsdl($this->_wsdl, 'ListSentCampaignsWithActivitySinceDateFails');
        $dotMailerClient->expects($this->any())
                        ->method('ListSentCampaignsWithActivitySinceDate')
                        ->will($this->throwException($exception));

        $client   = new DMClient($dotMailerClient, 'username', 'password');
        $campaigns = $client->getCampaigns($startDate);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetCampaignActivitiesSinceDateFailsWithNoStartDate()
    {
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'ListCampaignActivitiesSinceDateNoStartDate');
        
        $client = new DMClient($soapMock, 'username', 'password');
        $client->getCampaignActivitiesSinceDate('', 123);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetCampaignActivitiesSinceDateFailsWithNoCampaignId()
    {
        $startDate = '2011-09-01T12:00:00';
        
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'ListCampaignActivitiesSinceDateNoCampaignId');
        
        $client = new DMClient($soapMock, 'username', 'password');
        $client->getCampaignActivitiesSinceDate($startDate, '');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetCampaignActivitiesSinceDateFailsWithNoParams()
    {
        $soapMock = $this->getMockFromWsdl($this->_wsdl, 'ListCampaignActivitiesSinceDateNoParams');
        
        $client = new DMClient($soapMock, 'username', 'password');
        $client->getCampaignActivitiesSinceDate('', '');
    }
    
    public function testGetCampaignActivitiesSinceDateSuccessful()
    {
        $startDate = '2011-09-01T12:00:00';
        
        $dotMailerClient = $this->getMockFromWsdl($this->_wsdl, 'ListCampaignActivitiesSinceDate');
        $dotMailerClient->expects($this->once())
                        ->method('ListCampaignActivitiesSinceDate')
                        ->will($this->returnValue(new stdClass));

        $client = new DMClient($dotMailerClient, 'username', 'password');

        $campaignActivities = $client->getCampaignActivitiesSinceDate($startDate, 123);

        $this->assertEquals(0, count($campaignActivities));
    }
    
    /**
     * @expectedException FailedToFetchContactsException
     */
    public function testGetCampaignActivitiesSinceDateFails()
    {
        $startDate = '2011-09-01T12:00:00';

        $exception = new SoapFault('soap:Server', 'Failed to fetch campaigns activities');

        $dotMailerClient = $this->getMockFromWsdl($this->_wsdl, 'ListCampaignActivitiesSinceDateFailed');
        $dotMailerClient->expects($this->any())
                        ->method('ListCampaignActivitiesSinceDate')
                        ->will($this->throwException($exception));

        $client   = new DMClient($dotMailerClient, 'username', 'password');
        $campaignActivities = $client->getCampaignActivitiesSinceDate($startDate, 123);
    }
    
}
