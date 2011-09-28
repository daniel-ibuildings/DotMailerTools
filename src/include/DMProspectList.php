<?php
require_once __DIR__ . '/SugarBeanCampaignLog.php';
require_once 'modules/ProspectLists/ProspectList.php';

/**
 * Class represents the target lists of a campaign
 * The class accepts a Campaign object and uses this object to 
 * relate the list and contacts/leads added to this list 
 */
class DMProspectList extends ProspectList
{

    /**
     * Constructs an instance of DMProspectList
     *
     * @param Campaign The campaign object where this prospect relate to
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        if (empty($campaign) || !($campaign instanceof Campaign)) {
            throw new InvalidArgumentException;
        }
        
        parent::ProspectList();
        $this->campaign   = $campaign;
        $this->name       = $campaign->name .'-'. $campaign->end_date;
        $this->related_id = $campaign->id;
        $this->list_type  = 'default';
        $this->prospects  = $campaign->prospects;
        
        $this->audit = DMSyncAudit::getInstance();
    }

    /**
     * Creates a prospect list and link it to relation tables
     * 
     * @return void
     */
    public function saveAndLinkRelations()
    {
        $this->id = parent::save();
        
        if (isset($this->id)) {
            // link it to campaign
            $this->set_relationship('prospect_list_campaigns', array(
                 'campaign_id'=>$this->related_id, 
                 'prospect_list_id'=>$this->id 
            ));
            
            // link it to all prospects
            for ($i=0; $i<count($this->prospects); $i++) {
                $moduleId   = $this->prospects[$i]['id'];
                $className  = $this->prospects[$i]['module'];
                
                $this->set_relationship('prospect_lists_prospects', array(
                     'related_id'=>$moduleId, 
                     'related_type'=> $className . 's',
                     'prospect_list_id'=>$this->id 
                ));
                
                // there are some activities add them to log
                if(is_array($this->prospects[$i]['activities'])) {
                    $sugarCampaignLog = new SugarBeanCampaignLog($this->campaign);
                    $sugarCampaignLog->createActivityCampaignLogs($this->prospects[$i]);
                }
            }
        }
    }
}