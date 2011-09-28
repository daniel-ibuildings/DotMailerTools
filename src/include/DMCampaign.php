<?php

require_once 'custom/modules/IB_DMContactUpdater/include/SugarBeanCampaignLog.php';
require_once 'custom/modules/IB_DMContactUpdater/include/DMSyncAudit.php';

/**
 * This class represents a Campaign that needs to be created of dot mailer.
 * 
 */
class DMCampaign extends Campaign
{
    public $prospects;

    /**
     * Constructs an instance of DMCampaign, 
     * and set values for the selected properties
     *
     * @param String The name of the campaign
     * @return void
     */
    public function __construct($name)
    {
        parent::Campaign();
        $this->name   = $name;
        $this->status = 'Complete';
        $this->campaign_type = 'Email';
        $this->prospects = array();
        $this->audit = DMSyncAudit::getInstance();
    }

    /**
     * This function performs one of the following
     * - The campaign already at sugar with same name and end date - ignore saving
     * - The campaign exists in sugar but has different end date 
     *      - update end date
     *      - create new prospect list
     * - The campaign is new 
     *      - create a new record at sugar
     *      - create new prospect list
     *      - Add a record on campaign log
     *
     * @return void
     */
    public function save()
    {
        $result = $this->isAlreadyAtSugar();
        // already at sugar ignore it
        if ($result === true) {
            $this->audit->add('campaigns', 'alreadyAtSugar');
            return ;
        } 
        
        // End date is different, update end date
        if ($result instanceOf Campaign) {
            $result->end_date = $this->end_date;
            $this->id = $result->save();
            $this->audit->add('campaigns', 'updated');
            
        } else {
            $this->assigned_user_id = 1;
            $this->id = parent::save();
            
            // create a campaign log 
            $sugarCampaignLog = new SugarBeanCampaignLog($this);
            $sugarCampaignLog->createProspectCampaignLogs($this->prospects);
            
            $this->audit->add('campaigns', 'created');
        }
        // create target list
        $this->attachProspects();
    }

    /**
     * Create and relate campaign to the prospect list
     * 
     * @return void
     */
    public function attachProspects()
    {
        if (empty($this->id)) {
            return;
        }
        
        // create a traget list and link it with prospects
        $prospectList = new DMProspectList($this);
        $prospectList->saveAndLinkRelations();
    }
    
    /**
     * This function validates campaign status in sugar
     * It checks if campaign needs update or creation or ignore creation
     * 
     * @return mixed (boolean or string)
     */
    public function isAlreadyAtSugar()
    {
        $campaign  = new Campaign();;
        $campaigns = $campaign->get_full_list();
        
        foreach ($campaigns as $campaign) {
            if ($campaign->name == $this->name ) {
                if ($campaign->end_date !== $this->end_date) {
                    return $campaign;
                } 
                return true;
            }
        }
        return false;
    }
}