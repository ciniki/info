<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_info_flags($ciniki, $modules) {
	$flags = array(
		array('flag'=>array('bit'=>'1', 'name'=>'About')),
		array('flag'=>array('bit'=>'2', 'name'=>'Artist Statement')),
		array('flag'=>array('bit'=>'3', 'name'=>'CV')),
		array('flag'=>array('bit'=>'4', 'name'=>'Awards')),
		array('flag'=>array('bit'=>'5', 'name'=>'History')),
		array('flag'=>array('bit'=>'6', 'name'=>'Donations')),
		array('flag'=>array('bit'=>'7', 'name'=>'Membership')),
		array('flag'=>array('bit'=>'8', 'name'=>'Board of Directors')),
		array('flag'=>array('bit'=>'9', 'name'=>'Facilities')),
		array('flag'=>array('bit'=>'10', 'name'=>'Exhibition Application')),
		);

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
