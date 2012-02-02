<?php

require_once('include/MVC/View/SugarView.php');

class IB_DMContactUpdaterViewHome extends SugarView
{
    public function __construct()
    {
        parent::SugarView();
        $this->options['show_footer'] = false;
    }
    
    public function display()
    {
        if(array_key_exists('suppressions', $_REQUEST) && isset($_REQUEST['suppressions'])) {
            $this->ss->assign('suppressions', $_REQUEST['suppressions']);
        }
        
        if(array_key_exists('contacts', $_REQUEST)  && isset($_REQUEST['contacts'])) {
            $this->ss->assign('contacts', $_REQUEST['contacts']);
        }
        
        if(array_key_exists('campaigns', $_REQUEST)  && isset($_REQUEST['campaigns'])) {
            $this->ss->assign('campaigns', $_REQUEST['campaigns']);
        }
        
        $this->ss->display('modules/IB_DMContactUpdater/tpls/home.tpl');
    }
}