<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_info_web_testimonials($ciniki, $settings, $business_id) {

    //
    // Load the testimonials
    //
    $strsql = "SELECT id, sequence, quote, who "
        . "FROM ciniki_info_testimonials "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND (webflags&0x01) = 1 "
        . "ORDER BY testimonial_date ASC, sequence DESC "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'testimonials', 'fname'=>'id',
            'fields'=>array('id', 'quote', 'who')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['testimonials']) || count($rc['testimonials']) < 1 ) {
        return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1775', 'msg'=>"I'm sorry, but we can't find the page you requested."));
    }
    $testimonials = $rc['testimonials'];

    return array('stat'=>'ok', 'testimonials'=>$testimonials);
}
?>
