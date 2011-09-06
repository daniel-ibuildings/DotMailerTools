<?php

require_once __DIR__ . '/DMContact.php';

class ContactNotFoundException extends Exception { }
class InvalidCredentialsException extends Exception { }

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

    public function __construct($soapClient, $username, $password)
    {
        if (!$soapClient instanceof SoapClient) {
            throw new InvalidArgumentException;
        }

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

    private function getParam($params = array()) {
        $params['username'] = $this->username;
        $params['password'] = $this->password;
        return $params;
    }

    public function getContactByEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException;
        }

        $params = $this->getParam(array('email' => $email));

        try {
            $response = $this->soapClient->getContactByEmail($params);
        } catch (SoapFault $e) {
            DMSoapException::factory($e);
        }

        $contact = new DMContact();
        $contact->initFromSoap(array(), $response->GetContactByEmailResult);

        return $contact;
    }
}