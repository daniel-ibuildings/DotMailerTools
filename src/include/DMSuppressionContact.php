<?php

require_once 'DMContact.php';

/**
 * Represents a contact in suppression list
 */
class DMSuppressionContact extends DMContact
{
    /**
     * The date removed from contact list
     */
    public $dateRemoved;
    
    /**
     * Reason why this contact is suppressed
     */
    public $reason;
    
    /**
     * Constructs and instance of suppression contact
     *
     * @param Array Collection of contact fields
     * @return void
     */
    public function __construct($dataMap)
    {
        parent::__construct($dataMap);
    }
    
    /**
     * Sets properties of the object
     *
     * @param  StdClass suppression contact object
     * @return void
     */
    public function initFromSoap($result)
    {
        parent::initFromSoap($result->SuppressedContact);
        $this->dateRemoved = $result->DateRemoved;
        $this->reason = $result->Reason;
    }
}