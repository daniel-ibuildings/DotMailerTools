<?php

require_once 'custom/modules/IB_DMContactUpdater/include/DMClient.php';
require_once 'custom/modules/IB_DMContactUpdater/include/DMContact.php';
require_once 'custom/modules/IB_DMContactUpdater/include/DMCampaign.php';
require_once 'custom/modules/IB_DMContactUpdater/include/DMProspectList.php';
require_once 'custom/modules/IB_DMContactUpdater/include/SugarBeanContacts.php';
require_once 'custom/modules/IB_DMContactUpdater/include/SugarBeanEmailAddress.php';

class IB_DMContactUpdaterController extends SugarController
{
    public function setup()
    {
        parent::setup();
        
        $this->wsdl     = 'http://apiconnector.com/api.asmx?WSDL';
        $this->username = 'apiuser-36ca1349fd66@apiconnector.com';
        $this->password = '0OB1!0|1NRc407Ii';
        $this->client   = new DMClient(
            new SoapClient($this->wsdl), 
            $this->username, 
            $this->password
        );
        
        // Init DMContact
        $this->dataMap = array();
        $this->dataMap['email']     = array('soap' => 'Email',     'sugar' => 'email1');
        $this->dataMap['firstName'] = array('soap' => 'FIRSTNAME', 'sugar' => 'first_name');
        $this->dataMap['fullName']  = array('soap' => 'FULLNAME',  'sugar' => 'name');
        $this->dataMap['lastName']  = array('soap' => 'LASTNAME',  'sugar' => 'last_name');
        $this->dataMap['accountName']  = array('soap' => 'ACCOUNTNAME',  'sugar' => 'account_name');
        
        $date = new DateTime();
        $date->sub(new DateInterval('P30D'));
        
        $this->startDate   =  $date->format('Y-m-d').'T12:00:00';
        $this->redirectUrl = 'index.php?module=IB_DMContactUpdater&action=index&return_module=IB_DMContactUpdater&return_action=index&success=1';
        
    }
    
    public function action_listview()
    {
        $this->view = 'home';
    }
    
    public function action_sync_contact()
    {        
        $beanContacts = new SugarBeanContacts();
        $beans = $beanContacts->getBeanContacts();
        // process only first ten for now
        foreach($beans as $key => $bean) {
            if($key < 10) {
                $contact = new DMContact($this->dataMap);
                $contact->initFromSugarBean($bean);

                try {
                    $this->client->syncContact($contact);
                } catch (SoapFault $e) {
                    echo '<pre>';var_dump($e);echo '</pre>';
                }
            }
        }
        $this->redirect_url = $this->redirectUrl;
    }

    public function action_sync_suppression()
    {
        try {
            $contacts = $this->client->getSuppressionList($this->startDate)
                                     ->ListSuppressedContactsResult
                                     ->APIContactSuppressionSummary;
        } catch (SoapFault $e) {
            echo '<pre>';var_dump($e);echo '</pre>';
        }

        foreach($contacts as $key => $contact) {
            $sugarEmailAddress = new SugarBeanEmailAddress($contact, $this->dataMap);
            $sugarEmailAddress->updateContact();
        }
        $this->redirect_url = $this->redirectUrl;
    }
    
    public function action_sync_campaigns()
    {
        $campaigns = $this->client->getCampaigns($this->startDate);
        foreach($campaigns as $key => $campaign) {
            $dmCampaign = new DMCampaign($campaign->Name);

            $campaignActivities = $this->client->getCampaignActivitiesSinceDate($this->startDate, $campaign->Id);
            foreach($campaignActivities as $camActivity) {
                $dmCampaign->start_date = $dmCampaign->end_date = substr($camActivity->DateSent, 0, 10);
                
                $addresses = new SugarEmailAddress();
                $addresses = $addresses->getBeansByEmailAddress($camActivity->Email);
                foreach($addresses as $address ) {
                    $dmCampaign->prospects[] = array(
                        'id'     => $address->id,
                        'module' => get_class($address) . 's'
                    );
                }
            }
            
            // add campaign if an email exists in Sugar
            if(count($dmCampaign->prospects) > 0) {
                $campaignId = $dmCampaign->save();
            }
        }
        $this->redirect_url = $this->redirectUrl;
    }
}