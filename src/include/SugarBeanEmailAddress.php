<?php
require_once 'custom/modules/IB_DMContactUpdater/include/DMSuppressionContact.php';

class SugarBeanEmailAddress extends SugarEmailAddress
{
    protected $contact;
    protected $dataMap;
    protected $suppressedReason;

    public function __construct($contact, $dataMap)
    {
       parent::SugarEmailAddress();
       $this->contact = $contact;
       $this->dataMap = $dataMap;
       $this->suppressedReason = array('subscribed', 'unsubscribed');
    }    

    public function updateContact()
    {
        $contact   = $this->createSuppressionContact();
        $addresses = $this->getBeansByEmailAddress($contact->email);
        
        foreach($addresses as $address) {
            for($j=0; $j<count($address->emailAddress->addresses); $j++) {
                $supAddress = $address->emailAddress->addresses[$j];
                if($supAddress['email_address'] != $contact->email) {
                    continue;
                }
                
                // set flag for optin or optout
                $address->emailAddress->addresses[$j]['opt_out'] = '0';
                if(!$dmSupContact->optIn || strtolower($contact->reason) === 'unsubscribed') {
                    $address->emailAddress->addresses[$j]['opt_out'] = '1';
                } 
                
                // set flag for invalid email
                $address->emailAddress->addresses[$j]['invalid_email'] ='0';
                if (!in_array(strtolower($contact->reason), $this->suppressedReason)) {
                    $address->emailAddress->addresses[$j]['invalid_email'] = '1';
                }

                $address->emailAddress->save($supAddress['bean_id'], $supAddress['bean_module']);
                
            }
        }
    }
    
    public function createSuppressionContact()
    {
        $contact = new DMSuppressionContact($this->dataMap);
        $contact->initFromSoap($this->contact);
        
        return $contact;
    }
}