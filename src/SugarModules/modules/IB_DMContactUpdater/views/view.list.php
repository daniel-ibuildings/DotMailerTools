<?php

require_once('include/MVC/View/views/view.list.php');

class IB_DMContactUpdaterViewList extends ViewList
{
    public function __construct()
    {
        parent::SugarView();
    }
    
    public function display()
    {
        echo "<h1>Sync Actions</h1>";
        parent::display();
    }
    
    public function preDisplay()
    {
        parent::preDisplay();
        $this->lv->targetList = true;
    }
}