<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to update the content for.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_info_contentUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content ID'), 
        'parent_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Parent'), 
        'content_type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Content Type'), 
        'title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
		'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'),
		'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
		'primary_image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
		'primary_image_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image URL'),
		'excerpt'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Excerpt'),
        'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'), 
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
    $rc = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.contentUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Get the existing content details 
	//
	$strsql = "SELECT id, content_type, parent_id, uuid "
		. "FROM ciniki_info_content "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1674', 'msg'=>'Content not found'));
	}
	$item = $rc['item'];

	if( isset($args['title']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		if( $args['title'] != '' ) {
			$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
		} else {
			$args['permalink'] = ciniki_core_makePermalink($ciniki, $item['uuid']);
		}
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, title, permalink FROM ciniki_info_content "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $item['parent_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'image');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1675', 'msg'=>'You already have content with this title, please choose another title.'));
		}
	}

	//
	// Update the content in the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.info.content', $args['content_id'], $args);
}
?>
