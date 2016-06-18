<?php
//
// Description
// -----------
// This function will return the list of pages that contain content for the info package.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_info_web_pages($ciniki, $settings, $business_id) {

    $strsql = "SELECT id, "
        . "content_type, title, permalink, sequence "
        . "FROM ciniki_info_content "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND parent_id = 0 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'pages', 'fname'=>'content_type',
            'fields'=>array('id', 'content_type', 'title', 'permalink', 'sequence')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['pages']) ) {
        return array('stat'=>'ok', 'pages'=>array());
    } 

    return array('stat'=>'ok', 'pages'=>$rc['pages']);
}
?>
