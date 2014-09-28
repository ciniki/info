<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business.
//
// Returns
// -------
//
function ciniki_info_contentGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'content_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Content'),
		'content_type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Content Type'),
		'images'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Images'),
		'files'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Files'),
		'sponsors'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sponsors'),
		'children'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Children'),
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
    $rc = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.contentGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

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
		. "ciniki_info_content.content "
		. "FROM ciniki_info_content "
		. "WHERE ciniki_info_content.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	//
	// Content ID or Type must be specified
	//
	if( isset($args['content_id']) && $args['content_id'] != '' && $args['content_id'] != '0' ) {
		$strsql .= "AND ciniki_info_content.id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' ";
	} elseif( isset($args['content_type']) && $args['content_type'] != '' && $args['content_type'] != '0' ) {
		$strsql .= "AND ciniki_info_content.content_type = '" . ciniki_core_dbQuote($ciniki, $args['content_type']) . "' ";
		$strsql .= "AND ciniki_info_content.parent_id = 0 ";
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1677', 'msg'=>'You must specify the content you want.'));
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.info', array(
		array('container'=>'content', 'fname'=>'id', 'name'=>'content',
			'fields'=>array('id', 'parent_id', 'content_type',
				'title', 'permalink', 'sequence', 
				'primary_image_id', 'primary_image_caption', 'primary_image_url', 'excerpt', 'content')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['content'][0]['content']) ) {
		//
		// If the content type was specified and nothing was found, create blank entry
		//
		if( isset($args['content_type']) && $args['content_type'] != '' && $args['content_type'] != '0' ) {
			$content = array(
				'parent_id'=>0,
				'content_type'=>$args['content_type'],
				'title'=>'',
				'permalink'=>'',
				'sequence'=>'1',
				'primary_image_id'=>'0',
				'primary_image_caption'=>'',
				'primary_image_url'=>'',
				'excerpt'=>'',
				'content'=>'',
				'images'=>array(),
				'files'=>array(),
				'children'=>array(),
				);
			//
			// Get the title maps
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'private', 'contentTitleMaps');
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
			$rc = ciniki_info_contentTitleMaps($ciniki);
			$titles = $rc['maps'];
			if( isset($titles[$args['content_type']]) ) {
				$content['title'] = $titles[$args['content_type']];
				$content['permalink'] = ciniki_core_makePermalink($ciniki, $content['title']);
				if( $content['content_type'] < 11 ) {
					$content['permalink'] = preg_replace('/-/', '', $content['permalink']);
				}
			}

			//
			// Add the blank content record
			//
			ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
			$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.info.content', $content, 0x07);
			if( $rc['stat'] != 'ok' ) {	
				return $rc;
			}
			$content['id'] = $rc['id'];

			//
			// Return the blank content
			//
			return array('stat'=>'ok', 'content'=>$content);
		} else {
			//
			// Return error if requested specific content and doesn't exist
			//
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1676', 'msg'=>'Unable to find content'));
		}
	}
	$content = $rc['content'][0]['content'];
	if( !isset($args['content_id']) ) {
		$args['content_id'] = $content['id'];
	}

	//
	// Get the images
	//
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		$strsql = "SELECT id, name, image_id, webflags "
			. "FROM ciniki_info_content_images "
			. "WHERE content_id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.info', array(
			array('container'=>'images', 'fname'=>'id', 'name'=>'image',
				'fields'=>array('id', 'name', 'image_id', 'webflags')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['images']) ) {
			$content['images'] = $rc['images'];
			ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
			foreach($content['images'] as $inum => $img) {
				if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
					$rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], 
						$img['image']['image_id'], 75);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$content['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
				}
			}
		}
	}

	//
	// Get the files
	//
	if( isset($args['files']) && $args['files'] == 'yes' ) {
		$strsql = "SELECT id, name, extension, permalink "
			. "FROM ciniki_info_content_files "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_info_content_files.content_id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.info', array(
			array('container'=>'files', 'fname'=>'id', 'name'=>'file',
				'fields'=>array('id', 'name', 'extension', 'permalink')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['files']) ) {
			$content['files'] = $rc['files'];
		} else {
			$content['files'] = array();
		} 
	}

	//
	// Get the child items
	//
	if( isset($args['children']) && $args['children'] == 'list' ) {
		$strsql = "SELECT id, title "
			. "FROM ciniki_info_content "
			. "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.info', array(
			array('container'=>'children', 'fname'=>'id', 'name'=>'child',
				'fields'=>array('id', 'title')),
				));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['children']) ) {
			$content['children'] = $rc['children'];
		} else {
			$content['children'] = array();
		}
	}

	//
	// Get any sponsors for the content
	//
	if( isset($args['sponsors']) && $args['sponsors'] == 'yes' 
		&& isset($ciniki['business']['modules']['ciniki.sponsors']) 
		&& ($ciniki['business']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'hooks', 'sponsorList');
		$rc = ciniki_sponsors_hooks_sponsorList($ciniki, $args['business_id'], 
			array('object'=>'ciniki.info.content', 'object_id'=>$args['content_id']));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['sponsors']) ) {
			$content['sponsors'] = $rc['sponsors'];
		}
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
