<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to update the testimonial for.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_info_testimonialUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'testimonial_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Testimonial ID'), 
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'),
        'quote'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Quote'), 
        'who'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Who'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Website'),
        'testimonial_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Testimonial Date'), 
        'image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
        'image_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image URL'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'private', 'checkAccess');
    $rc = ciniki_info_checkAccess($ciniki, $args['tnid'], 'ciniki.info.testimonialUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the existing testimonial details 
    //
    $strsql = "SELECT id, sequence, uuid "
        . "FROM ciniki_info_testimonials "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['testimonial_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.info.31', 'msg'=>'Testimonial not found'));
    }
    $item = $rc['item'];

    //
    // Update the testimonial in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.info.testimonial', $args['testimonial_id'], $args);
}
?>
