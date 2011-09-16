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
 *
 */
class DMClient
{
    private $_soapClient;
    private $_username;
    private $_password;

    /**
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
    }

    /**
     *
     */
    private function getParams($params = array())
    {
        $params['username'] = $this->_username;
        $params['password'] = $this->_password;
        return $params;
    }

    /**
     *
     */
    public function getContactByEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException;
        }

        $params = $this->getParams(array('email' => $email));

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
     *
     */
    public function updateContact($id, DMContact $contact)
    {
        if (empty($id) || !is_int((int) $id)) {
            throw new InvalidArgumentException;
        }

        $params = $this->getParams();
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
     *
     */
    public function createContact(DMContact $contact)
    {
        $params = $this->getParams();
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
     *
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
        } else {
            $this->createContact($contact);
        }

        return true;
    }
    
    /**
     *
     */
    public function getSuppressionList($startDate, $select=100, $skip=0)
    {
        if (empty($startDate)) {
            throw new InvalidArgumentException;
        }
        
        $params = $this->getParams();
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
}