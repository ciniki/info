<?php
//
// Description
// -----------
// This function returns the array of status text for ciniki_sapos_invoices.status.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_info_contentTitleMaps($ciniki) {
    
    $maps = array(
        '1'=>'About',
        '2'=>'Artist Statement',
        '3'=>'CV',
        '4'=>'Awards',
        '5'=>'History',
        '6'=>'Donations',
        '7'=>'Membership',
        '8'=>'Board of Directors',
        '9'=>'Facilities',
        '10'=>'Exhibition Application',
        '11'=>'Warranty',
//      '12'=>'Testimonials',
//      '13'=>'Reviews',
        '14'=>'Green Policy',
        '15'=>'Why Us',
        '16'=>'Privacy Policy',
        '17'=>'Volunteer',
        '18'=>'Rental',
        '19'=>'Financial Assistance',
        '20'=>'Artists',
        '21'=>'Employment',
        '22'=>'Staff',
        '23'=>'Sponsorship',
        '24'=>'Jobs',
        '25'=>'Extended Bio',
        '26'=>'Subscription Agreement',
        '27'=>'Committees',
        '28'=>'Bylaws',
        );
    
    return array('stat'=>'ok', 'maps'=>$maps);
}
?>
