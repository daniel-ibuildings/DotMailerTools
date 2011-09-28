<?php

/**
 * A singleton class to track down the synchronisation activities
 */
class DMSyncAudit
{
    /**
     * A static variable to save instance of this class
     */
    public static $instance;

    /**
     * A static variable to save synchronisation activities
     */
    public static $audits = array(
        'contacts' => array(
            'total'   => 0,
            'updated' => 0,
            'created' => 0
        ),
        'suppressions' => array(
            'total'    => 0,
            'optedOut' => 0,
            'invalid'  => 0,
            'notInSugar' => 0
        ),
        'campaigns' => array(
            'total'   => 0,
            'created' => 0,
            'updated' => 0,
            'alreadyAtSugar' => 0
        )
    );
    
    /**
     * Constructor of the class
     */
    private function __construct()
    {
    }
    
    /**
     * Creates an instance of this class and save it to a static variable
     *
     * @param none
     * @return DmSyncAudit an instance of this class
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new DMSyncAudit();
        }
        return self::$instance;
    }
    
    /**
     * Increments the number of related sync actions
     *
     * @param String Sync action
     * @param String the array key
     * @return void
     */
    public function add($module, $key)
    {
        self::$audits[$module][$key] ++;
    }
    
    /**
     * returns an array of activities for the given sync actions
     *
     * @param String Name of the sync action
     * @return Array
     */
    public function getAudits($module)
    {
        return self::$audits[$module];
    }
}