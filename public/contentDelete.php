<?php
//
// Description
// -----------
// This method will delete a content from the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business the content is attached to.
// content_id:			The ID of the content to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_info_contentDelete(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'content_id'=>array('required'=>'yes', 'default'=>'', 'blank'=>'yes', 'name'=>'Content'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'private', 'checkAccess');
	$ac = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.contentDelete');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Get the uuid of the content to be deleted
	//
	$strsql = "SELECT uuid FROM ciniki_info_content "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1678', 'msg'=>'The content does not exist'));
	}
	$item = $rc['item'];

	//
	// Start transaction
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.info');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Remove the images
	//
	$strsql = "SELECT id, uuid, image_id FROM ciniki_info_content_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND content_id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'image');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.info');
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		$images = $rc['rows'];
		
		foreach($images as $iid => $image) {
			$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.info.content_image', 
				$image['id'], $image['uuid'], 0x04);
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.info');
				return $rc;	
			}
		}
	}

	//
	// Remove the files for the content
	//
	$strsql = "SELECT id, uuid "
		. "FROM ciniki_info_content_files "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND content_id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'file');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.info');
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		$files = $rc['rows'];
		foreach($files as $fid => $file) {
			$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.info.content_file', 
				$file['id'], $file['uuid'], 0x04);
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.info');
				return $rc;	
			}
		}
	}

	//
	// Remove the content
	//
	$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.info.content', 
		$args['content_id'], $item['uuid'], 0x04);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.info');
		return $rc;
	}

	//
	// Commit the transaction
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.info');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'info');

	return array('stat'=>'ok');
}
?>
