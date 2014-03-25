<?php
//
// Description
// ===========
// This function will return the file details and content so it can be sent to the client.
//
// Returns
// -------
//
function ciniki_info_web_fileDownload($ciniki, $business_id, $file_permalink) {

	//
	// Get the file details
	//
	$strsql = "SELECT ciniki_info_content_files.id, "
		. "ciniki_info_content_files.name, "
		. "ciniki_info_content_files.permalink, "
		. "ciniki_info_content_files.extension, "
		. "ciniki_info_content_files.binary_content "
		. "FROM ciniki_info_content_files "
		. "WHERE ciniki_info_content_files.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND CONCAT_WS('.', ciniki_info_content_files.permalink, ciniki_info_content_files.extension) = '" . ciniki_core_dbQuote($ciniki, $file_permalink) . "' "
		. "AND (ciniki_info_content_files.webflags&0x01) = 0 "		// Make sure file is to be visible
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'file');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['file']) ) {
		return array('stat'=>'noexist', 'err'=>array('pkg'=>'ciniki', 'code'=>'1681', 'msg'=>'Unable to find requested file'));
	}
	$rc['file']['filename'] = $rc['file']['name'] . '.' . $rc['file']['extension'];

	return array('stat'=>'ok', 'file'=>$rc['file']);
}
?>