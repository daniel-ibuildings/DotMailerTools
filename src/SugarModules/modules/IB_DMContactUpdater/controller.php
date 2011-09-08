<?php

require_once 'custom/modules/IB_DMContactUpdater/include/DMClient.php';
require_once 'custom/modules/IB_DMContactUpdater/include/DMContact.php';

class IB_DMContactUpdaterController extends SugarController
{
    public function action_test()
    {
        // Init DMClient
        $wsdl = 'http://apiconnector.com/api.asmx?WSDL';
        $username = 'apiuser-36ca1349fd66@apiconnector.com';
        $password = '0OB1!0|1NRc407Ii';

        // Init sugar contact
        $id = 'bf9bc1c6-dc81-1115-1fe0-4e54d12e8f15';
        $bean = new Contact();
        $bean->retrieve($id);
        $bean->fill_in_additional_list_fields();

        // Init DMContact
        $dataMap = array();
        $dataMap['email']     = array('soap' => 'Email',     'sugar' => 'email1');
        $dataMap['firstName'] = array('soap' => 'FIRSTNAME', 'sugar' => 'first_name');
        $dataMap['fullName']  = array('soap' => 'FULLNAME',  'sugar' => 'name');
        $dataMap['lastName']  = array('soap' => 'LASTNAME',  'sugar' => 'last_name');

        $contact = new DMContact($dataMap);
        $contact->initFromSugarBean($bean);

        $client = new DMClient(new SoapClient($wsdl), $username, $password);

        try {
            $client->syncContact($contact);
        } catch (Exception $e) {
            var_dump($e);
        }
    }
}