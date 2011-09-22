<?php

require_once(__DIR__ . '/../include/DMProspectList.php');

class DMProspectListTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testDMProspectListRequiresCampaignInstance()
    {
        $client = new DMProspectList(new StdClass);
    }
    
}