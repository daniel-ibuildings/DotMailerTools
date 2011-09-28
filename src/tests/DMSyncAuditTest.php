<?php

require_once(__DIR__ . '/../include/DMSyncAudit.php');

class DMSynAuditTest extends PHPUnit_FrameWork_TestCase
{
    public $audit;
    
    public function setup()
    {
        $this->audit = DMSyncAudit::getInstance();
    }
    
    public function testGetInstanceReturnsSingleInstance()
    {
        $audit2 = DMSyncAudit::getInstance();
        
        $this->assertEquals($this->audit, $audit2);
    }
    
    public function testAddIncrementsByOne()
    {
        $this->audit->add('contacts', 'total');
        
        $result = $this->audit->getAudits('contacts');
        
        $this->assertEquals(1, $result['total']);
    }
    
    public function testGetAuditReturnsSelectedAction()
    {
        $result = $this->audit->getAudits('contacts');
        
        $this->assertEquals(3, count($result));
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['created']);
    }
}