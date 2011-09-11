<?php

class DMSuppressionContact extends DMContact
{
    public $dateRemoved;
    public $reason;
    
    public function __construct($dataMap)
    {
        parent::__construct($dataMap);
    }
    
    public function initFromSoap($result)
    {
        parent::initFromSoap($result->SuppressedContact);
        $this->dateRemoved = $result->DateRemoved;
        $this->reason = $result->Reason;
    }
}