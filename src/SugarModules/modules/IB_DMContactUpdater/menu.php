<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $mod_strings, $app_strings, $sugar_config;
 
if(ACLController::checkAccess('IB_DMContactUpdater', 'sync_contact', true))
    $module_menu[]=	array(
        "index.php?module=IB_DMContactUpdater&action=sync_contact&return_module=IB_DMContactUpdater&return_action=index",
        'Sync Contact',
        "IB_DMContactUpdater"
    );
if(ACLController::checkAccess('IB_DMContactUpdater', 'sync_suppression', true))
    $module_menu[]=	array(
        "index.php?module=IB_DMContactUpdater&action=sync_suppression&return_module=IB_DMContactUpdater&return_action=index",
        'Sync Suppression List',
        "IB_DMContactUpdater"
    );
