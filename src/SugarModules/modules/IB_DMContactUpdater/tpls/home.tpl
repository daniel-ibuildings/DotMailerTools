<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link rel="SHORTCUT ICON" href="{$FAVICON_URL}">
<meta http-equiv="Content-Type" content="text/html; charset={$APP.LBL_CHARSET}">
<title>{$MOD.LBL_HOMEPAGE_TITLE}</title>
{literal}
<style type='text/css'>
    .dm-content span.message{
        color: green;
        font-size: 1.5em;
        margin: 10 0 10 0;
    }
    .dm-content table {
        margin-top:10px;
    }
    .dm-button {
        width:40%;
    }
    .dm-button input {
        width:200px;
    }
</style>
{/literal}
</head>
<body class="dm-body">
    <div id="main">
        <div id="content" class="dm-content">
            { if $SYNC_SUCCESS eq '1' }
                <span class="message">{sugar_translate label='LBL_IB_DMCONTACTUPDATER_SUCCESS' module='IB_DMContactUpdater'}</span>
            {/if}
            
            <h2>Synchronise Actions</h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
                <tbody>
                    <tr>
                        <td>{sugar_translate label='LBL_IB_DMCONTACTUPDATER_SYNC_CONTACT_LAB' module='IB_DMContactUpdater'}</td>
                        <td class="dm-button"><input type="button" onclick="location.href='index.php?module=IB_DMContactUpdater&action=sync_contact'" class="button" value="{sugar_translate label='LBL_IB_DMCONTACTUPDATER_SYNC_CONTACT' module='IB_DMContactUpdater'}"></td>
                    </tr>
                    <tr>
                        <td>{sugar_translate label='LBL_IB_DMCONTACTUPDATER_SYNC_SUPPRESSION_LAB' module='IB_DMContactUpdater'}</td>
                        <td class="dm-button"><input type="button" onclick="location.href='index.php?module=IB_DMContactUpdater&action=sync_suppression'" class="button primary" value="{sugar_translate label='LBL_IB_DMCONTACTUPDATER_SYNC_SUPPRESSION' module='IB_DMContactUpdater'}"></td>
                    </tr>
                    <tr>
                        <td>{sugar_translate label='LBL_IB_DMCONTACTUPDATER_SYNC_CAMPAIGNS_LAB' module='IB_DMContactUpdater'}</td>
                        <td class="dm-button"><input type="button" onclick="location.href='index.php?module=IB_DMContactUpdater&action=sync_campaigns'" class="button primary" value="{sugar_translate label='LBL_IB_DMCONTACTUPDATER_SYNC_CAMPAIGNS' module='IB_DMContactUpdater'}"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>