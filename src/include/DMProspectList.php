<?php

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
        parent::ProspectList();
        
        $this->name       = $campaign->name .'-'. $campaign->end_date;
        $this->related_id = $campaign->id;
        $this->list_type  = 'default';
        $this->prospects  = $campaign->prospects;
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
                $this->set_relationship('prospect_lists_prospects', array(
                     'related_id'=>$this->prospects[$i]['id'], 
                     'related_type'=> $this->prospects[$i]['module'], 
                     'prospect_list_id'=>$this->id 
                ));
            }
        }
    }
}