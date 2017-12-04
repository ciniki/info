<?php
//
// Description
// ===========
// This function will return the file details and content so it can be sent to the client.
//
// Returns
// -------
//
function ciniki_info_web_fileDownload($ciniki, $tnid, $content_permalink, $child_permalink, $file_permalink) {

    //
    // Get the file details
    //
    if( $child_permalink != '' ) {
        $strsql = "SELECT ciniki_info_content_files.id, "
            . "ciniki_info_content_files.name, "
            . "ciniki_info_content_files.permalink, "
            . "ciniki_info_content_files.extension, "
            . "ciniki_info_content_files.binary_content "
            . "FROM ciniki_info_content AS c1, ciniki_info_content AS c2, ciniki_info_content_files "
            . "WHERE c1.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND c1.permalink = '" . ciniki_core_dbQuote($ciniki, $content_permalink) . "' "
            . "AND c1.id = c2.parent_id "
            . "AND c2.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND c2.permalink = '" . ciniki_core_dbQuote($ciniki, $child_permalink) . "' "
            . "AND c2.id = ciniki_info_content_files.content_id "
            . "AND ciniki_info_content_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND CONCAT_WS('.', ciniki_info_content_files.permalink, ciniki_info_content_files.extension) = '" . ciniki_core_dbQuote($ciniki, $file_permalink) . "' "
            . "AND (ciniki_info_content_files.webflags&0x01) = 0 "      // Make sure file is to be visible
            . "";
        
    } else {
        $strsql = "SELECT ciniki_info_content_files.id, "
            . "ciniki_info_content_files.name, "
            . "ciniki_info_content_files.permalink, "
            . "ciniki_info_content_files.extension, "
            . "ciniki_info_content_files.binary_content "
            . "FROM ciniki_info_content, ciniki_info_content_files "
            . "WHERE ciniki_info_content.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND ciniki_info_content.permalink = '" . ciniki_core_dbQuote($ciniki, $content_permalink) . "' "
            . "AND ciniki_info_content.id = ciniki_info_content_files.content_id "
            . "AND ciniki_info_content_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "AND CONCAT_WS('.', ciniki_info_content_files.permalink, ciniki_info_content_files.extension) = '" . ciniki_core_dbQuote($ciniki, $file_permalink) . "' "
            . "AND (ciniki_info_content_files.webflags&0x01) = 0 "      // Make sure file is to be visible
            . "";
    }
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.info', 'file');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['file']) ) {
        return array('stat'=>'noexist', 'err'=>array('code'=>'ciniki.info.32', 'msg'=>'Unable to find requested file'));
    }
    $rc['file']['filename'] = $rc['file']['name'] . '.' . $rc['file']['extension'];

    return array('stat'=>'ok', 'file'=>$rc['file']);
}
?>
