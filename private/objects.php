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
function ciniki_info_objects($ciniki) {
	
	$objects = array();
	$objects['content'] = array(
		'name'=>'Content',
		'sync'=>'yes',
		'table'=>'ciniki_info_content',
		'fields'=>array(
			'parent_id'=>array(),
			'content_type'=>array(),
			'title'=>array(),
			'permalink'=>array(),
			'category'=>array(),
			'sequence'=>array(),
			'primary_image_id'=>array('ref'=>'ciniki.images.image'),
			'primary_image_caption'=>array(),
			'primary_image_url'=>array(),
			'child_title'=>array(),
			'excerpt'=>array(),
			'content'=>array(),
			),
		'history_table'=>'ciniki_info_history',
		);
	$objects['content_image'] = array(
		'name'=>'Content Image',
		'sync'=>'yes',
		'table'=>'ciniki_info_content_images',
		'fields'=>array(
			'content_id'=>array('ref'=>'ciniki.info.content'),
			'name'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'description'=>array(),
			),
		'history_table'=>'ciniki_info_history',
		);
	$objects['content_file'] = array(
		'name'=>'Content File',
		'sync'=>'yes',
		'table'=>'ciniki_info_content_files',
		'fields'=>array(
			'content_id'=>array('ref'=>'ciniki.info.content'),
			'extension'=>array(),
			'name'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'description'=>array(),
			'org_filename'=>array(),
			'binary_content'=>array('history'=>'no'),
			),
		'history_table'=>'ciniki_info_history',
		);
	$objects['testimonial'] = array(
		'name'=>'Testimonial',
		'sync'=>'yes',
		'table'=>'ciniki_info_testimonials',
		'fields'=>array(
			'sequence'=>array(),
			'quote'=>array(),
			'who'=>array(),
			'webflags'=>array(),
			'testimonial_date'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'image_caption'=>array(),
			'image_url'=>array(),
			),
		'history_table'=>'ciniki_info_history',
		);

	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
