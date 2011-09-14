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
        echo "THis is a test";
        parent::display();
    }
}