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
        if($_REQUEST['suppressions']) {
            $this->ss->assign('suppressions', $_REQUEST['suppressions']);
        }
        
        if($_REQUEST['contacts']) {
            $this->ss->assign('contacts', $_REQUEST['contacts']);
        }
        
        if($_REQUEST['campaigns']) {
            $this->ss->assign('campaigns', $_REQUEST['campaigns']);
        }
        $this->ss->display('modules/IB_DMContactUpdater/tpls/home.tpl');
    }
}