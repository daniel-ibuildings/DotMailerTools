<?php

require_once 'custom/modules/IB_DMContactUpdater/include/DMSuppressionContact.php';

/**
 * Using the suppression list from DotMailer, 
 * this class updates the properties of sugar contacts
 */
class SugarBeanEmailAddress extends SugarEmailAddress
{
    /**
     * An instance of contact 
     */
    protected $contact;

    /**
     * An array of data fields
     */
    protected $dataMap;

    /**
     * Default values for suppression reason
     */
    protected $suppressionReason;

    /**
     * Synchronisation activity tracker
     */
    protected $audit;

    /**
     * Constructs an instance of the class, and sets its properties
     *
     * @param  Contact an instance of Contact
     * @param  Array   collection of data fields
     * @return void
     */
    public function __construct($contact, $dataMap)
    {
       parent::SugarEmailAddress();
       $this->contact = $contact;
       $this->dataMap = $dataMap;
       $this->suppressionReason = array('subscribed', 'unsubscribed');
       
       // is used for tracking purpose
       $this->audit = DMSyncAudit::getInstance();
    }

    /**
     * Updates Sugar contact detail, mainly optin/optout and invalid properties
     *
     * @param  none
     * @return void
     */
    public function updateContact()
    {
        $contact   = $this->createSuppressionContact();
        $addresses = $this->getBeansByEmailAddress($contact->email);

        // email does not exist in sugarcrm
        if(count($addresses) == 0) {
            $this->audit->add('suppressions', 'notInSugar');
        }

        // for each address get the emailaddresses and update properties
        foreach($addresses as $address) {
            for($j=0; $j<count($address->emailAddress->addresses); $j++) {
                $supAddress = $address->emailAddress->addresses[$j];
                if($supAddress['email_address'] != $contact->email) {
                    continue;
                }

                // set flag for optin or optout
                $address->emailAddress->addresses[$j]['opt_out'] = '0';
                if(!$contact->optIn || strtolower($contact->reason) === 'unsubscribed') {
                    $address->emailAddress->addresses[$j]['opt_out'] = '1';
                    $this->audit->add('suppressions', 'optedOut');
                } 

                // set flag for invalid email
                $address->emailAddress->addresses[$j]['invalid_email'] ='0';
                if (!in_array(strtolower($contact->reason), $this->suppressionReason)) {
                    $address->emailAddress->addresses[$j]['invalid_email'] = '1';
                    $this->audit->add('suppressions', 'invalid');
                }

                $address->emailAddress->save($supAddress['bean_id'], $supAddress['bean_module']);
            }
        }
    }

    /**
     *  Creates an instance of DMSuppressionContact and initialises its properties
     *
     * @param  none
     * @return DMSuppressionContact
     */
    public function createSuppressionContact()
    {
        $contact = new DMSuppressionContact($this->dataMap);
        $contact->initFromSoap($this->contact);

        return $contact;
    }
}