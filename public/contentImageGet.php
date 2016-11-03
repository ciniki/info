<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:         The ID of the business.
// content_image_id:    The ID of the content image to get.
//
// Returns
// -------
//
function ciniki_info_contentImageGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'content_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image'),
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
    $rc = ciniki_info_checkAccess($ciniki, $args['business_id'], 'ciniki.info.contentImageGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    //
    // Get the main information
    //
    $strsql = "SELECT ciniki_info_content_images.id, "
        . "ciniki_info_content_images.name, "
        . "ciniki_info_content_images.permalink, "
        . "ciniki_info_content_images.sequence, "
        . "ciniki_info_content_images.webflags, "
        . "ciniki_info_content_images.image_id, "
        . "ciniki_info_content_images.description "
        . "FROM ciniki_info_content_images "
        . "WHERE ciniki_info_content_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND ciniki_info_content_images.id = '" . ciniki_core_dbQuote($ciniki, $args['content_image_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'images', 'fname'=>'id', 'name'=>'image',
            'fields'=>array('id', 'name', 'permalink', 'sequence', 'webflags', 'image_id', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['images']) ) {
        return array('stat'=>'ok', 'err'=>array('code'=>'ciniki.info.24', 'msg'=>'Unable to find image'));
    }
    $image = $rc['images'][0]['image'];
    
    return array('stat'=>'ok', 'image'=>$image);
}
?>
