<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to update the content for.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_info_contentUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content ID'), 
        'parent_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Parent'), 
        'content_type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Content Type'), 
        'title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Permalink'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Category'),
        'sequence'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Sequence'),
        'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'),
        'primary_image_caption'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image Caption'),
        'primary_image_url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image URL'),
        'child_title'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Children Title'),
        'excerpt'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Excerpt'),
        'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'info', 'private', 'checkAccess');
    $rc = ciniki_info_checkAccess($ciniki, $args['tnid'], 'ciniki.info.contentUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Get the existing content details 
    //
    $strsql = "SELECT id, content_type, parent_id, uuid "
        . "FROM ciniki_info_content "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'item');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['item']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.info.27', 'msg'=>'Content not found'));
    }
    $item = $rc['item'];

    if( isset($args['title']) ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
        if( $args['title'] != '' ) {
            $args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
            if( $item['parent_id'] == 0 && $item['content_type'] < 11 ) {
                $args['permalink'] = preg_replace('/-/', '', $args['permalink']);
            }
        } else {
            $args['permalink'] = ciniki_core_makePermalink($ciniki, $item['uuid']);
        }
        if( $item['content_type'] == 7 ) {
            $args['permalink'] = 'membership';
        }
        //
        // Make sure the permalink is unique
        //
        $strsql = "SELECT id, title, permalink FROM ciniki_info_content "
            . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND parent_id = '" . ciniki_core_dbQuote($ciniki, $item['parent_id']) . "' "
            . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'image');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( $rc['num_rows'] > 0 ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.info.28', 'msg'=>'You already have content with this title, please choose another title.'));
        }
    }

    //
    // Update the content in the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
    return ciniki_core_objectUpdate($ciniki, $args['tnid'], 'ciniki.info.content', $args['content_id'], $args);
}
?>
