<?php

require_once('include/MVC/View/SugarView.php');

class IB_DMContactUpdaterViewHome extends SugarView
{
    public function __construct()
    {
        parent::SugarView();
    }
    
    public function display()
    {
        parent::display();
        echo "<h1>Sync Actions</h1>";
    }
    
    public function preDisplay()
    {
        parent::preDisplay();
        $this->lv->targetList = true;
    }
}