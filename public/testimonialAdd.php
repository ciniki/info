<?php
//
// Description
// ===========
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_info_testimonialAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'sequence'=>array('required'=>'no', 'default'=>'1', 'blank'=>'yes', 'name'=>'Sequence'),
        'quote'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Quote'), 
        'who'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Who'), 
		'webflags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Website'),
        'testimonial_date'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'type'=>'date', 'name'=>'Testimonial Date'), 
		'image_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Image'),
		'image_caption'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Image Caption'),
		'image_url'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Image URL'),
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
    $rc = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.testimonialAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    } 

	//
	// Check the sequence
	//
	if( !isset($args['sequence']) || $args['sequence'] == '' || $args['sequence'] == '0' ) {
		$strsql = "SELECT MAX(sequence) AS max_sequence "
			. "FROM ciniki_info_testimonials "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'seq');
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		if( isset($rc['seq']) && isset($rc['seq']['max_sequence']) ) {
			$args['sequence'] = $rc['seq']['max_sequence'] + 1;
		} else {
			$args['sequence'] = 1;
		}
	}

	//
	// Add the testimonial to the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	return ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.info.testimonial', $args, 0x07);
}
?>
