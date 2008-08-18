<?php


include_once( "kernel/common/template.php" );
include_once( 'lib/ezxml/classes/ezxml.php' );
include_once( 'lib/ezutils/classes/ezhttpfile.php' );

include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'lib/ezlocale/classes/ezdatetime.php' );

include_once( "kernel/classes/ezcontentbrowse.php" );

include_once( "extension/coolpresentation/modules/coolpresentation/coolpresentationimport.php" );

$http =& eZHTTPTool::instance();
$module =& $Params["Module"];

$tpl =& templateInit();

$sourceFile = "documents/test1.sxw";

if ( $module->isCurrentAction( 'CSPPlace' ) )
{
	// We have the file and the placement. Do the actual import.
	$selectedNodeIDArray = eZContentBrowse::result( 'CSPPlace' );

	$nodeID = $selectedNodeIDArray[0];

	if ( is_numeric( $nodeID ) )
	{
		$fileName = $http->sessionVariable( "coolpresentation_import_filename" );
		if ( file_exists( $fileName ) )
		{
			$import = new eZCSPImport();
			$result = $import->import( $http->sessionVariable( "coolpresentation_import_filename" ), $nodeID );

			$tpl->setVariable( 'class_identifier', $result['ClassIdentifier'] );
			$tpl->setVariable( 'url_alias', $result['URLAlias'] );
			$tpl->setVariable( 'node_name', $result['NodeName'] );

			$http->removeSessionVariable( 'coolpresentation_import_step' );
			$http->removeSessionVariable( 'coolpresentation_import_filename' );
			$http->removeSessionVariable( 'coolpresentation_import_original_filename' );
		}
		else
		{
			eZDebug::writeError( "Cannot import. File not found. Already imported?" );
		}
	}
	else
	{
		eZDebug::writeError( "Cannot import document, supplied placement nodeID is not valid." );
	}

	$tpl->setVariable( 'coolpresentation_mode', 'imported' );
}
else
{
	$file = eZHTTPFile::fetch( "coolpresentation_file" );

	if ( $file )
	{
		if ( $file->store() )
		{
			$fileName = $file->attribute( 'filename' );
			$originalFileName = $file->attribute( 'original_filename' );

			$http->setSessionVariable( 'coolpresentation_import_step', 'browse' );
			$http->setSessionVariable( 'coolpresentation_import_filename', $fileName );
			$http->setSessionVariable( 'coolpresentation_import_original_filename', $originalFileName );

			eZContentBrowse::browse( array( 'action_name' => 'CSPPlace',
                                            'description_template' => 'design:coolpresentation/browse_place.tpl',
                                            'content' => array(),
                                            'from_page' => '/coolpresentation/import/',
                                            'cancel_page' => '/coolpresentation/import/' ),
                                            $module );
                                            return;
		}
		else
		{
			eZDebug::writeError( "Cannot store uploaded file, cannot import" );
		}
	}

	$tpl->setVariable( 'coolpresentation_mode', 'browse' );
}

$Result = array();
$Result['content'] =& $tpl->fetch( "design:coolpresentation/import.tpl" );
$Result['path'] = array( array( 'url' => '/coolpresentation/import/',
                                'text' => ezi18n( 'extension/coolpresentation', 'Cool PowerPoint import' ) ));



                                ?>
