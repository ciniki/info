<?php
//
// Description
// -----------
// This method will return the list of testimonials for a business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:     The ID of the business to get testimonials for.
//
// Returns
// -------
//
function ciniki_info_testimonialList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'private', 'checkAccess');
    $ac = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.testimonialList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);
    
    //
    // Load the testimonials
    //
    $strsql = "SELECT id, sequence, quote, who, "
        . "DATE_FORMAT(testimonial_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS testimonial_date "
        . "FROM ciniki_info_testimonials "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "ORDER BY ciniki_info_testimonials.testimonial_date ASC, sequence DESC "
        . "";

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.testimonials', 'testimonials', 'testimonial', array('stat'=>'ok', 'testimonials'=>array()));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    
    $rsp = array('stat'=>'ok', 'testimonials'=>$rc['testimonials']);

    return $rsp;
}
?>
