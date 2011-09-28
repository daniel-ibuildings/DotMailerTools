<?php

/**
 * Logs campaign activities
 */
class SugarBeanCampaignLog extends CampaignLog
{
    /**
     * Campaign object
     */
    public $campaign;

    /**
     * Constructs a instance of this class, assign campaign property
     *
     * @param  DMCampaign an object of a campaign
     * @return void
     */
    public function __construct($campaign)
    {
        parent::CampaignLog();
        $this->campaign  = $campaign;
    }

    /**
     * Checks if record already exists. If no data, creates a prospect campaign log data
     *
     * @param Array Collection of prospect data
     * @return void
     */
    public function createProspectCampaignLogs($prospects)
    {
        if(!is_array($prospects)) {
            return;
        }
        
        foreach ($prospects as $prospect) {
            $relBean = $this->createRelationBean($prospect);
            if(!$this->logExists($relBean)) {
                $type = strtolower($prospect['module']).'s';
                $this->createCampaignLog($relBean, $type);
            }
        }
    }

    /**
     * Creates an activity campaign log data
     *
     * @param Array Collection of prospect data
     * @return void
     */
    public function createActivityCampaignLogs($prospect)
    {
        if(!is_array($prospect['activities'])) {
            return;
        }
        
        $relBean = $this->createRelationBean($prospect);
        foreach ($prospect['activities'] as $type => $hits) {
            if($hits > 0) {
                $this->createCampaignLog($relBean, $type, $hits);
            }
        }
    }

    /**
     * Performs duplicate check
     *
     * @param Mixed   Lead/contact objects
     * @param String  Activity type
     * @param Integer Number of hits
     * @return void
     */
    public function createCampaignLog($relBean, $type, $hits=0)
    {
        global $timedate;

        $this->campaign_id = $this->campaign->id;
        $this->target_tracker_key = create_guid();
        $this->target_id     = $relBean->id;
        $this->target_type   = $relBean->module_dir;
        $this->activity_date = $timedate->now();
        $this->activity_type = $type;
        $campaignLog->hits   = $hits;
        $this->save();
    }

    /**
     * Performs duplicate check
     *
     * @param Mixed Lead or contact objects
     */
    public function logExists($relBean)
    {
        $query  = " SELECT id 
                    FROM campaign_log 
                    WHERE campaign_id = '$this->campaign->id' 
                    AND target_id = '$relBean->id'";

        $result = $this->db->query($query);
        $row    = $this->db->fetchByAssoc($result);

        return empty($row);
    }

    /**
     * Removes a campaign log from table
     *
     * @param Mixed Lead or contact objects
     */
    public function removeLog($relBean)
    {
        $query  = " DELETE FROM campaign_log 
                    WHERE campaign_id = '$this->campaign->id' 
                    AND target_id = '$relBean->id'";

        $result = $this->db->query($query);
        $row    = $this->db->fetchByAssoc($result);
    }

    /**
     * Creates an object of Lead/Contact bean object
     *
     * @param  Array Collection of prospect data
     * @return Mixed Lead/Contact bean object
     */
    public function createRelationBean($prospect)
    {
        $className = $prospect['module'];
        $relBean   = new $className();
        $relBean->retrieve($prospect['id']);

        return $relBean;
    }
}