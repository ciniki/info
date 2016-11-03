<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business.
// testimonial_id:      The ID of the testimonial to get.
//
// Returns
// -------
//
function ciniki_info_testimonialGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'testimonial_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Testimonial'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'private', 'checkAccess');
    $rc = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.testimonialGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Get the main information
    //
    $strsql = "SELECT id, "
        . "sequence, "
        . "quote, "
        . "who, "
        . "webflags, "
        . "DATE_FORMAT(testimonial_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS testimonial_date, "
        . "image_id, "
        . "image_caption, "
        . "image_url "
        . "FROM ciniki_info_testimonials "
        . "WHERE ciniki_info_testimonials.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_info_testimonials.id = '" . ciniki_core_dbQuote($ciniki, $args['testimonial_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'testimonials', 'fname'=>'id', 'name'=>'testimonial',
            'fields'=>array('id', 'sequence', 'quote', 'who', 'webflags', 'testimonial_date', 
                'image_id', 'image_caption', 'image_url')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['testimonials']) ) {
        return array('stat'=>'ok', 'err'=>array('code'=>'ciniki.info.30', 'msg'=>'Unable to find testimonial'));
    }
    $testimonial = $rc['testimonials'][0]['testimonial'];
    
    return array('stat'=>'ok', 'testimonial'=>$testimonial);
}
?>
