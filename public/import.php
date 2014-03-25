<?php
//
// Description
// -----------
// This method will move phone numbers from the customer record to the ciniki_customer_phones table.
//
// Arguments
// ---------
// api_key:
// auth_token:
//
// Returns
// -------
//
function ciniki_info_import($ciniki) {
	//
	// Must be a sysadmin to run this
	//
	if( ($ciniki['session']['user']['perms'] & 0x01) != 0x01 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1607', 'msg'=>'Access denied'));
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// Enable the module with basic About page for every business
	//
	$strsql = "SELECT id "
		. "FROM ciniki_businesses "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.businesses', 'business');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$businesses = $rc['rows'];
	foreach($businesses as $business) {
		$business_id = $business['id'];
		$strsql = "INSERT INTO ciniki_business_modules (business_id, package, module, "
			. "status, flags, ruleset, date_added, last_updated, last_change) VALUES ("
			. "$business_id, 'ciniki', 'info', "
			. "1, 1, '', UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())";
		$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.businesses');
	}

	//
	// Get the list of about pages, by business
	//
	$strsql = "SELECT business_id, detail_key, detail_value "
		. "FROM ciniki_web_settings "
		. "WHERE detail_key LIKE 'page-about%' "
		. "ORDER BY business_id "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'businesses', 'fname'=>'business_id',
			'fields'=>array('id'=>'business_id')),
		array('container'=>'settings', 'fname'=>'detail_key',
			'fields'=>array('value'=>'detail_value')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$web_settings = $rc['businesses'];

	//
	// Get the list of about content by business
	//
	$strsql = "SELECT business_id, detail_key, detail_value "
		. "FROM ciniki_web_content "
		. "WHERE detail_key LIKE 'page-about%' "
		. "ORDER BY business_id "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.web', array(
		array('container'=>'businesses', 'fname'=>'business_id',
			'fields'=>array('id'=>'business_id')),
		array('container'=>'settings', 'fname'=>'detail_key',
			'fields'=>array('value'=>'detail_value')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$web_content = $rc['businesses'];

	//
	// Go through web settings and setup about information
	//
	$pages = array(
		'1'=>array('title'=>'About', 'permalink'=>'about', 'name'=>'about'),
		'2'=>array('title'=>'Artist Statement', 'permalink'=>'artiststatement', 'name'=>'aboutartiststatement'),
		'3'=>array('title'=>'CV', 'permalink'=>'cv', 'name'=>'aboutcv'),
		'4'=>array('title'=>'Awards', 'permalink'=>'awards', 'name'=>'aboutawards'),
		'5'=>array('title'=>'History', 'permalink'=>'history', 'name'=>'abouthistory'),
		'6'=>array('title'=>'Donations', 'permalink'=>'donations', 'name'=>'aboutdonations'),
		'7'=>array('title'=>'Membership', 'permalink'=>'membership', 'name'=>'aboutmembership'),
		'8'=>array('title'=>'Board of Directors', 'permalink'=>'boardofdirectors', 'name'=>'aboutboardofdirectors'),
		);
	foreach($web_settings as $business_id => $business) {
		//
		// Setup about page
		//
		foreach($pages as $content_type => $page) {
			$args = array(
				'parent_id'=>0,
				'content_type'=>$content_type,
				'title'=>$page['title'],
				'permalink'=>$page['permalink'],
				'sequence'=>1,
				'primary_image_id'=>0,
				'primary_image_caption'=>'',
				'primary_image_url'=>'',
				'excerpt'=>'',
				'content'=>'',
			);
			$pname = $page['name'];
			if( isset($business['settings']["page-$pname-image"]['value']) 
				&& $business['settings']["page-$pname-image"]['value'] != '' ) {
				$args['primary_image_id'] = $business['settings']["page-$pname-image"]['value'];
			}
			if( isset($business['settings']["page-$pname-image-caption"]['value']) ) {
				$args['primary_image_caption'] = $business['settings']["page-$pname-image-caption"]['value'];
			}
			if( isset($business['settings']["page-$pname-image-url"]['value']) ) {
				$args['primary_image_url'] = $business['settings']["page-$pname-image-url"]['value'];
			}
			if( isset($web_content[$business_id]['settings']["page-$pname-content"]['value']) ) {
				$args['content'] = $web_content[$business_id]['settings']["page-$pname-content"]['value'];
			}
			if( $content_type == 1 || $args['content'] != '' || $args['primary_image_id'] != 0 
				|| (isset($business['settings']["page-$pname-active"]['value']) 
					&& $business['settings']["page-$pname-active"]['value'] == 'yes') ) {
				$rc = ciniki_core_objectAdd($ciniki, $business_id, 'ciniki.info.content', $args, 0x03);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
			}
		}
	}

	return array('stat'=>'ok');
}
?>
