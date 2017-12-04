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
function ciniki_info_web_pageDetails($ciniki, $settings, $tnid, $args) {
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
        . "ciniki_info_content.child_title, "
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
            . "AND ciniki_info_content.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND (ciniki_info_content_images.webflags&0x01) = 0 "
            . ") "
        . "WHERE ciniki_info_content.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    //
    // Permalink or Content Type must be specified
    //
    if( isset($args['permalink']) && $args['permalink'] != '' ) {
        $strsql .= "AND ciniki_info_content.permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' ";
    } elseif( isset($args['content_type']) && $args['content_type'] != '' && $args['content_type'] != '0' ) {
        $strsql .= "AND ciniki_info_content.content_type = '" . ciniki_core_dbQuote($ciniki, $args['content_type']) . "' ";
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.info.33', 'msg'=>'I\'m sorry, we were unable to find the page you requested.'));
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'content', 'fname'=>'id',
            'fields'=>array('id', 'parent_id', 'content_type',
                'title', 'permalink', 'sequence', 
                'image_id'=>'primary_image_id', 'image_caption'=>'primary_image_caption', 
                'image_url'=>'primary_image_url', 'child_title', 'excerpt', 'content')),
        array('container'=>'images', 'fname'=>'image_id', 
            'fields'=>array('image_id', 'title'=>'image_name', 'permalink'=>'image_permalink',
                'description'=>'image_description', 'last_updated'=>'image_last_updated')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['content']) || count($rc['content']) < 1 ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.info.34', 'msg'=>"I'm sorry, but we can't find the page you requested."));
    }
    $content = array_pop($rc['content']);

    //
    // Check if any files are attached to the content
    //
    $strsql = "SELECT id, name, extension, permalink, description "
        . "FROM ciniki_info_content_files "
        . "WHERE ciniki_info_content_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
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
    // Check if any files are attached to the content
    //
    $strsql = "SELECT ciniki_info_content.id, "
        . "ciniki_info_content_files.id AS file_id, "
        . "ciniki_info_content_files.content_id, "
        . "ciniki_info_content_files.name, "
        . "ciniki_info_content_files.extension, "
        . "ciniki_info_content_files.permalink, "
        . "ciniki_info_content_files.description "
        . "FROM ciniki_info_content, ciniki_info_content_files "
        . "WHERE ciniki_info_content.parent_id = '" . ciniki_core_dbQuote($ciniki, $content['id']) . "' "
        . "AND ciniki_info_content.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_info_content.id = ciniki_info_content_files.content_id "
        . "AND ciniki_info_content_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.info', array(
        array('container'=>'children', 'fname'=>'id', 
            'fields'=>array('id')),
        array('container'=>'files', 'fname'=>'file_id', 
            'fields'=>array('id', 'name', 'extension', 'permalink', 'description')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['children']) ) {
        $content['child_files'] = $rc['children'];
    }

    //
    // Check if there are any children
    //
    $strsql = "SELECT id, title, "
        . "primary_image_id, "
        . "permalink, category, excerpt, content, "
        . "'no' AS is_details "
        . "FROM ciniki_info_content "
        . "WHERE parent_id = '" . ciniki_core_dbQuote($ciniki, $content['id']) . "' "
        . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "ORDER BY category, sequence, title "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.customers', array(
//      array('container'=>'children', 'fname'=>'id',
//          'fields'=>array('id', 'name'=>'title')),
        array('container'=>'children', 'fname'=>'category', 
            'fields'=>array('name'=>'category')),
        array('container'=>'list', 'fname'=>'id', 
            'fields'=>array('id', 'title', 'permalink', 'image_id'=>'primary_image_id',
                'description'=>'content', 'is_details')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['children']) ) {  
        // If only one category or no category, then display as a list.
        if( count($rc['children']) == 1 ) {
            $content['children'] = array();
            $list = array_pop($rc['children']);
            $list = $list['list'];
            foreach($list as $cid => $child) {
                $content['children'][$child['id']] = array(
                    'id'=>$child['id'], 
                    'name'=>$child['title'], 
                    'list'=>array($cid=>$child),
                    );
            }
        } else {
            $content['child_categories'] = $rc['children'];
        }

    }

    //
    // Get any sponsors for this page, and that references for sponsors is enabled
    //
    if( isset($ciniki['tenant']['modules']['ciniki.sponsors']) 
        && ($ciniki['tenant']['modules']['ciniki.sponsors']['flags']&0x02) == 0x02
        ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'sponsors', 'web', 'sponsorRefList');
        $rc = ciniki_sponsors_web_sponsorRefList($ciniki, $settings, $tnid, 
            'ciniki.info.content', $content['id']);
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
