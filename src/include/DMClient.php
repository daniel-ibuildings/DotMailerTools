<?php

require_once __DIR__ . '/DMContact.php';

class ContactNotFoundException extends Exception { }
class InvalidCredentialsException extends Exception { }
class FailedUpdateException extends Exception { }
class FailedCreateException extends Exception { }

class DMSoapException {
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

class DMClient
{
    private $soapClient;
    private $username;
    private $password;

    public function __construct(SoapClient $soapClient, $username, $password)
    {
        if (empty($username)) {
            throw new InvalidArgumentException;
        }

        if (empty($password)) {
            throw new InvalidArgumentException;
        }

        $this->soapClient = $soapClient;
        $this->username = $username;
        $this->password = $password;
    }

    private function getParams($params = array()) {
        $params['username'] = $this->username;
        $params['password'] = $this->password;
        return $params;
    }

    public function getContactByEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException;
        }

        $params = $this->getParams(array('email' => $email));

        try {
            $response = $this->soapClient->getContactByEmail($params);
        } catch (SoapFault $e) {
            DMSoapException::factory($e);
        }

        return $response->GetContactByEmailResult;
    }
    
    public function updateContact($id, DMContact $contact)
    {
        if (empty($id) || !is_int((int) $id)) {
            throw new InvalidArgumentException;
        }
        
        $params = $this->getParams();
        $params['contact'] = $contact->toSoapParam();
        $params['contact']['ID'] = $id;
        
        try {
            $this->soapClient->UpdateContact($params);
        } catch (SoapFault $e) {
            throw new FailedUpdateException;
        }

        return true;
    }
    
    public function createContact(DMContact $contact)
    {
        try {
            $this->soapClient->CreateContact($params);
        } catch (SoapFault $e) {
            throw new FailedCreateException;
        }
        return true;
    }
}