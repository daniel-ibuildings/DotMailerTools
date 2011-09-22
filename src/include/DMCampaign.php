<?php
echo __DIR__;

//require_once(__DIR__.'/src/modules/Campaigns/Campaign.php');
/**
 *
 */
class DMCampaign extends Campaign
{
    public $prospects;

    /**
     *
     */
    public function __construct($name)
    {
        parent::Campaign();
        $this->name   = $name;
        $this->status = 'Complete';
        $this->campaign_type = 'Email';
        $this->prospects = array();
    }

    /**
     *
     */
    public function save()
    {
        $result = $this->isAlreadyAtSugar();
        // already at sugar ignore it
        if($result === true) {
            return ;
        } 
        
        // End date is different, update end date
        if($result instanceOf Campaign) {
            $result->end_date = $this->end_date;
            $this->id = $result->save();
        } else{
            $this->id = parent::save();
        }
        // create target list
        $this->attachProspects();
    }
    
    /**
     *
     */
    public function attachProspects()
    {
        if(empty($this->id)) {
            return;
        }
        
        // create a traget list and link it with prospects
        $prospectList = new DMProspectList($this);
        $prospectList->saveAndLinkRelations();
    }
    
    /**
     *
     */
    public function isAlreadyAtSugar()
    {
        $campaign  = new Campaign();;
        $campaigns = $campaign->get_full_list();
        
        foreach($campaigns as $campaign) {
            if($campaign->name == $this->name ) {
                if($campaign->end_date !== $this->end_date) {
                    return $campaign;
                } 
                return true;
            }
        }
        return false;
    }
}