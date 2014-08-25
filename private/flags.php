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
		// 0x01
		array('flag'=>array('bit'=>'1', 'name'=>'About')),
		array('flag'=>array('bit'=>'2', 'name'=>'Artist Statement')),
		array('flag'=>array('bit'=>'3', 'name'=>'CV')),
		array('flag'=>array('bit'=>'4', 'name'=>'Awards')),
		// 0x10
		array('flag'=>array('bit'=>'5', 'name'=>'History')),
		array('flag'=>array('bit'=>'6', 'name'=>'Donations')),
		array('flag'=>array('bit'=>'7', 'name'=>'Membership')),
		array('flag'=>array('bit'=>'8', 'name'=>'Board of Directors')),
		// 0x0100
		array('flag'=>array('bit'=>'9', 'name'=>'Facilities')),
		array('flag'=>array('bit'=>'10', 'name'=>'Exhibition Application')),
		array('flag'=>array('bit'=>'11', 'name'=>'Warranty')),
		array('flag'=>array('bit'=>'12', 'name'=>'Testimonials')),
		// 0x1000
//		array('flag'=>array('bit'=>'13', 'name'=>'Reviews')),
		array('flag'=>array('bit'=>'14', 'name'=>'Green Policy')),
		array('flag'=>array('bit'=>'15', 'name'=>'Why Us')),
		array('flag'=>array('bit'=>'16', 'name'=>'Privacy Policy')),
		);

	return array('stat'=>'ok', 'flags'=>$flags);
}
?>
