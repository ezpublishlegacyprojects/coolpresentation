<?php


$Module = array( 'name' => 'coolpresentation',
                 'variable_params' => true );

$ViewList = array();
$ViewList['import'] = array(
    'script' => 'import.php',
    'post_actions' => array( 'BrowseActionName' ) );

?>
