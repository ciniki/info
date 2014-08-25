<?php
//
// Description
// -----------
// This method will return the list of content and their titles for use in the interface.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get testimonials for.
//
// Returns
// -------
//
function ciniki_info_contentList($ciniki) {
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
    $rc = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.contentList');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
	$modules = $rc['modules'];

	//
	// Get the list of titles from the database
	//
	$strsql = "SELECT content_type, title "
		. "FROM ciniki_info_content "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND parent_id = 0 "
		. "ORDER BY content_type "
		. "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
		array('container'=>'titles', 'fname'=>'content_type',
			'fields'=>array('title')),
		));
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
	if( isset($rc['titles']) ) {
		$titles = $rc['titles'];
	} else {
		$titles = array();
	}
	
	//
	// Based on flags and titles, return the list content titles
	//
	$content = array();
	$flags = $modules['ciniki.info']['flags'];
	if( ($flags&0x01) > 0 ) {
		$content[] = array('content'=>array('id'=>'1', 
			'title'=>((isset($titles['1']['title'])&&$titles['1']['title']!='')?$titles['1']['title']:'About')));
	}
	if( ($flags&0x02) > 0 ) {
		$content[] = array('content'=>array('id'=>'2', 
			'title'=>((isset($titles['2']['title'])&&$titles['2']['title']!='')?$titles['2']['title']:'Artist Statement')));
	}
	if( ($flags&0x04) > 0 ) {
		$content[] = array('content'=>array('id'=>'3', 
			'title'=>((isset($titles['3']['title'])&&$titles['3']['title']!='')?$titles['3']['title']:'CV')));
	}
	if( ($flags&0x08) > 0 ) {
		$content[] = array('content'=>array('id'=>'4', 
			'title'=>((isset($titles['4']['title'])&&$titles['4']['title']!='')?$titles['4']['title']:'Awards')));
	}
	if( ($flags&0x10) > 0 ) {
		$content[] = array('content'=>array('id'=>'5', 
			'title'=>((isset($titles['5']['title'])&&$titles['5']['title']!='')?$titles['5']['title']:'History')));
	}
	if( ($flags&0x20) > 0 ) {
		$content[] = array('content'=>array('id'=>'6', 
			'title'=>((isset($titles['6']['title'])&&$titles['6']['title']!='')?$titles['6']['title']:'Donations')));
	}
	if( ($flags&0x40) > 0 ) {
		$content[] = array('content'=>array('id'=>'7', 
			'title'=>((isset($titles['7']['title'])&&$titles['7']['title']!='')?$titles['7']['title']:'Membership')));
	}
	if( ($flags&0x80) > 0 ) {
		$content[] = array('content'=>array('id'=>'8', 
			'title'=>((isset($titles['8']['title'])&&$titles['8']['title']!='')?$titles['8']['title']:'Board of Directors')));
	}
	if( ($flags&0x0100) > 0 ) {
		$content[] = array('content'=>array('id'=>'9', 
			'title'=>((isset($titles['9']['title'])&&$titles['9']['title']!='')?$titles['9']['title']:'Facilities')));
	}
	if( ($flags&0x0200) > 0 ) {
		$content[] = array('content'=>array('id'=>'10', 
			'title'=>((isset($titles['10']['title'])&&$titles['10']['title']!='')?$titles['10']['title']:'Exhibition Application')));
	}
	if( ($flags&0x0400) > 0 ) {
		$content[] = array('content'=>array('id'=>'11', 
			'title'=>((isset($titles['11']['title'])&&$titles['11']['title']!='')?$titles['11']['title']:'Warranty')));
	}
	if( ($flags&0x0800) > 0 ) {
		$content[] = array('content'=>array('id'=>'12', 
			'title'=>((isset($titles['12']['title'])&&$titles['12']['title']!='')?$titles['12']['title']:'Testimonials')));
	}
	if( ($flags&0x1000) > 0 ) {
		$content[] = array('content'=>array('id'=>'13', 
			'title'=>((isset($titles['13']['title'])&&$titles['13']['title']!='')?$titles['13']['title']:'Reviews')));
	}
	if( ($flags&0x2000) > 0 ) {
		$content[] = array('content'=>array('id'=>'14', 
			'title'=>((isset($titles['14']['title'])&&$titles['14']['title']!='')?$titles['14']['title']:'Green Policy')));
	}
	if( ($flags&0x4000) > 0 ) {
		$content[] = array('content'=>array('id'=>'15', 
			'title'=>((isset($titles['15']['title'])&&$titles['15']['title']!='')?$titles['15']['title']:'Why Us')));
	}
	if( ($flags&0x8000) > 0 ) {
		$content[] = array('content'=>array('id'=>'16', 
			'title'=>((isset($titles['16']['title'])&&$titles['16']['title']!='')?$titles['16']['title']:'Privacy Policy')));
	}

	return array('stat'=>'ok', 'content'=>$content);
}
?>
