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
        if($_REQUEST['success']) {
            $this->ss->assign('SYNC_SUCCESS', $_REQUEST['success']);
        }
        
        $this->ss->display('modules/IB_DMContactUpdater/tpls/home.tpl');
    }
}