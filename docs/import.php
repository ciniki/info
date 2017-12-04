<?php
//
// Description
// -----------
//
//  **************************  BE CAREFUL!!!!
//  This code will add duplicate entries if run twice.
//  This code was designed to convert page-about%-% settings to ciniki.fino
//
// Arguments
// ---------
// api_key:
// auth_token:
//
// Returns
// -------
//
function ciniki_info_import($ciniki) {
    //
    // Must be a sysadmin to run this
    //
    if( ($ciniki['session']['user']['perms'] & 0x01) != 0x01 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.info.1', 'msg'=>'Access denied'));
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

//
//
// FORCE EXIT
//
//
    return array('stat'=>'ok');


/*  Code to rename page settings with - between about and name (abouthistory -> about-history).
    $pages = array('artiststatement','cv', 'awards','history','donations','membership', 'boardofdirectors');
    
    foreach($pages as $pname) {
        $strsql = "UPDATE ciniki_web_settings SET detail_key = 'page-about-$pname-active' "
            . "WHERE detail_key = 'page-about$pname-active' "
            . "";
        $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
        $strsql = "UPDATE ciniki_web_history SET table_key = 'page-about-$pname-active' "
            . "WHERE table_key = 'page-about$pname-active' "
            . "";
        $rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.web');
    }
*/



    //
    // Enable the module with basic About page for every tenant
    //
    $strsql = "SELECT id "
        . "FROM ciniki_tenants "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.tenants', 'tenant');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $tenants = $rc['rows'];
    foreach($tenants as $tenant) {
        $tnid = $tenant['id'];
        $strsql = "INSERT INTO ciniki_tenant_modules (tnid, package, module, "
            . "status, flags, ruleset, date_added, last_updated, last_change) VALUES ("
            . "$tnid, 'ciniki', 'info', "
            . "1, 1, '', UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.tenants');
    }

    //
    // Get the list of about pages, by tenant
    //
    $strsql = "SELECT tnid, detail_key, detail_value "
        . "FROM ciniki_web_settings "
        . "WHERE detail_key LIKE 'page-about%' "
        . "ORDER BY tnid "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'tenants', 'fname'=>'tnid',
            'fields'=>array('id'=>'tnid')),
        array('container'=>'settings', 'fname'=>'detail_key',
            'fields'=>array('value'=>'detail_value')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $web_settings = $rc['tenants'];

    //
    // Get the list of about content by tenant
    //
    $strsql = "SELECT tnid, detail_key, detail_value "
        . "FROM ciniki_web_content "
        . "WHERE detail_key LIKE 'page-about%' "
        . "ORDER BY tnid "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
        array('container'=>'tenants', 'fname'=>'tnid',
            'fields'=>array('id'=>'tnid')),
        array('container'=>'settings', 'fname'=>'detail_key',
            'fields'=>array('value'=>'detail_value')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $web_content = $rc['tenants'];

    //
    // Go through web settings and setup about information
    //
    $pages = array(
        '1'=>array('title'=>'About', 'permalink'=>'about', 'name'=>'about'),
        '2'=>array('title'=>'Artist Statement', 'permalink'=>'artiststatement', 'name'=>'aboutartiststatement'),
        '3'=>array('title'=>'CV', 'permalink'=>'cv', 'name'=>'aboutcv'),
        '4'=>array('title'=>'Awards', 'permalink'=>'awards', 'name'=>'aboutawards'),
        '5'=>array('title'=>'History', 'permalink'=>'history', 'name'=>'abouthistory'),
        '6'=>array('title'=>'Donations', 'permalink'=>'donations', 'name'=>'aboutdonations'),
        '7'=>array('title'=>'Membership', 'permalink'=>'membership', 'name'=>'aboutmembership'),
        '8'=>array('title'=>'Board of Directors', 'permalink'=>'boardofdirectors', 'name'=>'aboutboardofdirectors'),
        );
    foreach($web_settings as $tnid => $tenant) {
        //
        // Setup about page
        //
        foreach($pages as $content_type => $page) {
            $args = array(
                'parent_id'=>0,
                'content_type'=>$content_type,
                'title'=>$page['title'],
                'permalink'=>$page['permalink'],
                'sequence'=>1,
                'primary_image_id'=>0,
                'primary_image_caption'=>'',
                'primary_image_url'=>'',
                'excerpt'=>'',
                'content'=>'',
            );
            $pname = $page['name'];
            if( isset($tenant['settings']["page-$pname-image"]['value']) 
                && $tenant['settings']["page-$pname-image"]['value'] != '' ) {
                $args['primary_image_id'] = $tenant['settings']["page-$pname-image"]['value'];
            }
            if( isset($tenant['settings']["page-$pname-image-caption"]['value']) ) {
                $args['primary_image_caption'] = $tenant['settings']["page-$pname-image-caption"]['value'];
            }
            if( isset($tenant['settings']["page-$pname-image-url"]['value']) ) {
                $args['primary_image_url'] = $tenant['settings']["page-$pname-image-url"]['value'];
            }
            if( isset($web_content[$tnid]['settings']["page-$pname-content"]['value']) ) {
                $args['content'] = $web_content[$tnid]['settings']["page-$pname-content"]['value'];
            }
            if( $content_type == 1 || $args['content'] != '' || $args['primary_image_id'] != 0 
                || (isset($tenant['settings']["page-$pname-active"]['value']) 
                    && $tenant['settings']["page-$pname-active"]['value'] == 'yes') ) {
                $rc = ciniki_core_objectAdd($ciniki, $tnid, 'ciniki.info.content', $args, 0x03);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
            }
        }
    }

    return array('stat'=>'ok');
}
?>
