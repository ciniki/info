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
function ciniki_info_web_pageDetails($ciniki, $settings, $business_id, $args) {
	//
	// Get the main information
	//
	$strsql = "SELECT ciniki_info_content.id, "
		. "ciniki_info_content.parent_id, "
		. "ciniki_info_content.content_type, "
		. "ciniki_info_content.title, "
		. "ciniki_info_content.permalink, "
		. "ciniki_info_content.sequence, "
		. "ciniki_info_content.primary_image_id, "
		. "ciniki_info_content.primary_image_caption, "
		. "ciniki_info_content.primary_image_url, "
		. "ciniki_info_content.excerpt, "
		. "ciniki_info_content.content, "
		. "ciniki_info_content_images.image_id, "
		. "ciniki_info_content_images.name AS image_name, "
		. "ciniki_info_content_images.permalink AS image_permalink, "
		. "ciniki_info_content_images.description AS image_description, "
		. "UNIX_TIMESTAMP(ciniki_info_content_images.last_updated) AS image_last_updated "
		. "FROM ciniki_info_content "
		. "LEFT JOIN ciniki_info_content_images ON ("
			. "ciniki_info_content.id = ciniki_info_content_images.content_id "
			. "AND ciniki_info_content.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
			. "AND (ciniki_info_content_images.webflags&0x01) = 0 "
			. ") "
		. "WHERE ciniki_info_content.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	//
	// Permalink or Content Type must be specified
	//
	if( isset($args['permalink']) && $args['permalink'] != '' ) {
		$strsql .= "AND ciniki_info_content.permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' ";
	} elseif( isset($args['content_type']) && $args['content_type'] != '' && $args['content_type'] != '0' ) {
		$strsql .= "AND ciniki_info_content.content_type = '" . ciniki_core_dbQuote($ciniki, $args['content_type']) . "' ";
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1679', 'msg'=>'I\'m sorry, we were unable to find the page you requested.'));
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
		array('container'=>'content', 'fname'=>'id',
			'fields'=>array('id', 'parent_id', 'content_type',
				'title', 'permalink', 'sequence', 
				'image_id'=>'primary_image_id', 'image_caption'=>'primary_image_caption', 
				'image_url'=>'primary_image_url', 'excerpt', 'content')),
		array('container'=>'images', 'fname'=>'image_id', 
			'fields'=>array('image_id', 'title'=>'image_name', 'permalink'=>'image_permalink',
				'description'=>'image_description', 'last_updated'=>'image_last_updated')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['content']) || count($rc['content']) < 1 ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'1680', 'msg'=>"I'm sorry, but we can't find the page you requested."));
	}
	$content = array_pop($rc['content']);

	//
	// Check if any files are attached to the content
	//
	$strsql = "SELECT id, name, extension, permalink, description "
		. "FROM ciniki_info_content_files "
		. "WHERE ciniki_info_content_files.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_info_content_files.content_id = '" . ciniki_core_dbQuote($ciniki, $content['id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
		array('container'=>'files', 'fname'=>'id', 
			'fields'=>array('id', 'name', 'extension', 'permalink', 'description')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['files']) ) {
		$content['files'] = $rc['files'];
	}

	//
	// Check if there are any children
	//
	$strsql = "SELECT id, title, "
		. "primary_image_id, "
		. "permalink, excerpt, content, "
		. "'no' AS is_details "
		. "FROM ciniki_info_content "
		. "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $content['id']) . "' "
		. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.customers', array(
		array('container'=>'children', 'fname'=>'id',
			'fields'=>array('id', 'name'=>'title')),
		array('container'=>'list', 'fname'=>'id', 
			'fields'=>array('id', 'title', 'permalink', 'image_id'=>'primary_image_id',
				'description'=>'content', 'is_details')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['children']) ) {
		$content['children'] = $rc['children'];
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
