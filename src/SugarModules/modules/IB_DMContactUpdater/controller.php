<?php

require_once 'custom/modules/IB_DMContactUpdater/include/DMClient.php';
require_once 'custom/modules/IB_DMContactUpdater/include/DMContact.php';

class IB_DMContactUpdaterController extends SugarController
{
    public function action_sync()
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
        $dataMap['accountName']  = array('soap' => 'ACCOUNTNAME',  'sugar' => 'account_name');

        // fetch all sugar contacts, loop and update it in mailer

        $contact = new DMContact($dataMap);
        $contact->initFromSugarBean($bean);

        $client = new DMClient(new SoapClient($wsdl), $username, $password);

        try {
            $client->syncContact($contact);
        } catch (Exception $e) {
            echo '<pre>';var_dump($e);echo '</pre>';
        }
    }
    
    public function action_create()
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
        $contact->email = "danielkiddoTEST@ibuildings.com";
        
        $client = new DMClient(new SoapClient($wsdl), $username, $password);
        
        try {
            $client->createContact($contact);
        } catch (Exception $e) {
            echo '<pre>';var_dump($e);echo '</pre>';
        }
    }
    
    
    public function action_suppression_list()
    {
        $startDate = '2011-09-01T12:00:00';
        $suppressedActions = array('subscribed', 'unsubscribed');
        
        // Init DMContact
        $dataMap = array();
        $dataMap['email']     = array('soap' => 'Email',     'sugar' => 'email1');
        $dataMap['firstName'] = array('soap' => 'FIRSTNAME', 'sugar' => 'first_name');
        $dataMap['fullName']  = array('soap' => 'FULLNAME',  'sugar' => 'name');
        $dataMap['lastName']  = array('soap' => 'LASTNAME',  'sugar' => 'last_name');

        // Init DMClient
        $wsdl     = 'http://apiconnector.com/api.asmx?WSDL';
        $username = 'apiuser-36ca1349fd66@apiconnector.com';
        $password = '0OB1!0|1NRc407Ii';
        
        $client = new DMClient(new SoapClient($wsdl), $username, $password);

        try {
            $contacts = $client->suppressionList($startDate)
                               ->ListSuppressedContactsResult
                               ->APIContactSuppressionSummary;
        } catch (SoapFault $e) {
            echo '<pre>';var_dump($e);echo '</pre>';
        }

        foreach($contacts as $key => $contact) {
            $dmSupContact  = new DMSuppressionContact($dataMap);
            $dmSupContact->initFromSoap($contact);

            $sugarEmailAddress = new SugarEmailAddress();
            $addresses = $sugarEmailAddress->getBeansByEmailAddress($dmSupContact->email);
            
            for($i = 0; $i < count($addresses); $i++) {
                for($j = 0; $j < count($addresses[$i]->emailAddress->addresses); $j++) {

                    if($addresses[$i]->emailAddress->addresses[$j]['email_address'] != $dmSupContact->email) {
                        continue;
                    }
                    
                    // set flag for optin or optout
                    $addresses[$i]->emailAddress->addresses[$j]['opt_out'] = '0';
                    if(!$dmSupContact->optIn || strtolower($dmSupContact->reason) === 'unsubscribed') {
                        $addresses[$i]->emailAddress->addresses[$j]['opt_out'] = '1';
                    } 
                    
                    // set flag for invalid email
                    $addresses[$i]->emailAddress->addresses[$j]['invalid_email'] ='0';
                    if (!in_array(strtolower($dmSupContact->reason), $suppressedActions)) {
                        $addresses[$i]->emailAddress->addresses[$j]['invalid_email'] = '1';
                    }

                    $addresses[$i]->emailAddress->save(
                        $addresses[$i]->emailAddress->addresses[$j]['bean_id'],
                        $addresses[$i]->emailAddress->addresses[$j]['bean_module']
                    );
                }
            }
        }
    }
}