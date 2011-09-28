<?php

require_once __DIR__ . '/DMContact.php';
require_once __DIR__ . '/DMSuppressionContact.php';

/**
 *
 */
class ContactNotFoundException extends Exception
{
}

/**
 *
 */
class InvalidCredentialsException extends Exception
{
}

/**
 *
 */
class FailedUpdateException extends Exception
{
}

/**
 *
 */
class FailedCreateException extends Exception
{
}

/**
 *
 */
class FailedToFetchContactsException extends Exception
{
}

/**
 *
 */
class DMSoapException
{
    public static function factory(Exception $e)
    {
        if (strpos($e, 'ERROR_CONTACT_NOT_FOUND') !== false) {
            throw new ContactNotFoundException;
        } else if (strpos($e, 'ERROR_INVALID_LOGIN') !== false) {
            throw new InvalidCredentialsException;
        } else {
            throw $e;
        }
    }
}

/**
 * A class that simulates the SOAP interactions
 * is is used to create a channel between SugarCRM and DtMailer
 */
class DMClient
{
    /**
     * A variable to save a soap client instance
     */
    private $_soapClient;

    /**
     * Username for the soap client
     */
    private $_username;

    /**
     * Password for the soap client
     */
    private $_password;
    
    /**
     * An instance of DMSyncAudit
     */
    private $audit;

    /**
     * Constructs an instance of this class, 
     * if the a valid parameter passed to it or throws relevant exceptions
     * 
     *
     * @param SoapClient SOAP client
     * @param String     Username for the soap 
     * @param String     password for the soap 
     *
     */
    public function __construct(SoapClient $soapClient, $username, $password)
    {
        if (empty($username)) {
            throw new InvalidArgumentException;
        }

        if (empty($password)) {
            throw new InvalidArgumentException;
        }

        $this->_soapClient = $soapClient;
        $this->_username = $username;
        $this->_password = $password;
        
        $this->audit = DMSyncAudit::getInstance();
    }

    /**
     * Adds user name and password to params and return params array
     *
     * @param Array parameters required to make a call to DotMailer
     * @return Array
     */
    private function _getParams($params = array())
    {
        $params['username'] = $this->_username;
        $params['password'] = $this->_password;
        return $params;
    }

    /**
     * Fetches contacts form DotMailer by email of the contact
     *
     * @param  String   Email address of a contact
     * @return StdClass An object of contact 
     */
    public function getContactByEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException;
        }

        $params = $this->_getParams(array('email' => $email));

        try {
            $result = $this->_soapClient
                ->getContactByEmail($params)
                ->GetContactByEmailResult;
        } catch (SoapFault $e) {
            DMSoapException::factory($e);
        }

        return $result;
    }

    /**
     * Updates existing DotMailer contact detail
     *
     * @param Integer   Id of contact
     * @param DMContact The new contact detail
     * @return Boolean
     */
    public function updateContact($id, DMContact $contact)
    {
        if (empty($id) || !is_int((int) $id)) {
            throw new InvalidArgumentException;
        }

        $params = $this->_getParams();
        $params['contact'] = $contact->toSoapParam();
        $params['contact']['ID'] = $id;

        try {
            $this->_soapClient->UpdateContact($params);
        } catch (SoapFault $e) {
            throw new FailedUpdateException;
        }
        return true;
    }

    /**
     * Adds a new contact in DotMailer
     *
     * @param  DMContact the contact detail
     * @return Boolean
     */
    public function createContact(DMContact $contact)
    {
        $params = $this->_getParams();
        $params['contact'] = $contact->toSoapParam();
        $params['contact']['ID'] = '-1';

        try {
            $this->_soapClient->CreateContact($params);
        } catch (SoapFault $e) {
            throw new FailedCreateException;
        }
        return true;
    }

    /**
     * Decides what to do with the contact,
     * It checks if contact already in DotMailer, 
     *  if it does it calls update function to update contact
     * Or if contact does not exist in DotMailer 
     * it calls create function to add contact
     *
     * @param DMContact Contact detail 
     * @return boolean
     */
    public function syncContact(DMContact $contact)
    {
        try {
            $response  = $this->getContactByEmail($contact->email);
            $dmContact = new DMContact(array());
            $dmContact->initFromSoap($response);
        } catch(ContactNotFoundException $e) {
            // Expected exception, do nothing...
        }

        if (isset($dmContact)) {
            $contact->emailType = $dmContact->emailType;
            $contact->audienceType = $dmContact->audienceType;

            $this->updateContact($dmContact->id, $contact);
            $this->audit->add('contacts', 'updated');
        } else {
            $this->createContact($contact);
            $this->audit->add('contacts', 'created');
        }
        return true;
    }
    
    /**
     * Fetches suppression lists from DotMailer
     *
     * @param  String   The start date from which suppression lists fetched
     * @param  Integer  The number of suppression lists to return
     * @param  Integer  The skip value
     * @return StdClass Contacts as stdclass
     */
    public function getSuppressionList($startDate, $select=500, $skip=0)
    {
        if (empty($startDate)) {
            throw new InvalidArgumentException;
        }
        
        $params = $this->_getParams();
        $params['startDate'] = $startDate;
        $params['select'] = $select;
        $params['skip'] = $skip;
        
        try {
            $contacts = $this->_soapClient->ListSuppressedContacts($params);
        } catch (SoapFault $e) {
            throw new FailedToFetchContactsException;
        }
        return $contacts;
    }
    
    /**
     * Fetches Campaigns from DotMailer
     *
     * @param  String   The start date from which campaigns fetched
     * @param  Integer  The number of campains to return
     * @param  Integer  The skip value
     * @return StdClass Campaigns as stdclass
     */
    public function getCampaigns($startDate, $select=500, $skip=0)
    {
        if (empty($startDate)) {
            throw new InvalidArgumentException;
        }

        $params = $this->_getParams();
        $params['startDate'] = $startDate;
        $params['select']    = $select;
        $params['skip']      = $skip;

        try {
            $campaigns = $this->_soapClient->ListSentCampaignsWithActivitySinceDate($params)
                                           ->ListSentCampaignsWithActivitySinceDateResult
                                           ->APICampaign;
        } catch (SoapFault $e) {
            throw new FailedToFetchContactsException;
        }
        return $campaigns;
    }

    /**
     * Fetches Campaign activities from DotMailer
     *
     * @param  String   The start date from which campaign activities fetched
     * @param  Integer  The number of campaign activities to return
     * @param  Integer  The skip value
     * @return StdClass Campaign activities as stdclass
     */
    public function getCampaignActivitiesSinceDate($startDate, $campaignId, $select=500, $skip=0)
    {
        if (empty($startDate) || empty($campaignId)) {
            throw new InvalidArgumentException;
        }

        $params = $this->_getParams();
        $params['startDate'] = $startDate;
        $params['campaignId']= $campaignId;
        $params['select']    = $select;
        $params['skip']      = $skip;

        try {
            $campaignActivities = $this->_soapClient->ListCampaignActivitiesSinceDate($params)
                                                    ->ListCampaignActivitiesSinceDateResult
                                                    ->APICampaignContactSummary;
        } catch (SoapFault $e) {
            throw new FailedToFetchContactsException;
        }
        return $campaignActivities;
    }
    
}