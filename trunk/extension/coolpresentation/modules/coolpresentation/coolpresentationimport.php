<?php



/*! \file coolpresentationimport.php
 */

/*!
 \class eZOoimport coolpresentationimport.php
 \brief The class eZOoimport does

 */
class eZCSPImport
{
	/*!
	 Constructor
	 */

	function eZCSPImport()
	{
	}

	/*!
	 Imports an OpenOffice.org document from the given file.
	 */
	function import( $file, $placeNodeID )
	{
		$importResult = array();
		include_once( "lib/ezfile/classes/ezdir.php" );
		$unzipResult = "";

		eZDir::mkdir( $this->ImportDir );

		$http =& eZHTTPTool::instance();
		// echo $file;
		$file = $http->sessionVariable( "coolpresentation_import_filename" );
		$originalFilename = $http->sessionVariable( "coolpresentation_import_original_filename" );

		$originalFilename = str_replace(".zip", ".xml", $originalFilename);
		$originalFilename = str_replace(".ZIP", ".xml", $originalFilename);

		// Check if zlib extension is loaded, if it's loaded use bundled ZIP library,
		// if not rely on the unzip commandline version.
		if ( !function_exists( 'gzopen' ) )
		{
			exec( "unzip -o $file -d " . $this->ImportDir, $unzipResult );
		}
		else
		{
			require_once('extension/coolpresentation/lib/pclzip.lib.php');
			$archive = new PclZip( $file );
			$archive->extract( PCLZIP_OPT_PATH, $this->ImportDir );
		}

		$fileName = $this->ImportDir . $originalFilename;

		$xml = new eZXML();
		$xmlDoc = file_get_contents( $fileName );

		function cleanPptXml( $xmlDoc ) {
			$varis = array( 'â€˜', 'â„¢', 'â€œ', '™', '”', '“', '’', '—', '…', 'â€');
			$values = array( "'", '&#8482;', '&quot;', '&#8482;', '&quot;', '&quot;', "'", '-', ' ' , '&quot;');
			return preg_replace( $varis, $values, $xmlDoc );
		}

		function rebuild_textarea( $str ) {
			$vars = array( 'â€˜', 'â„¢', 'â€œ', '™', '”', '“', '’', '—', '…','‘' ,'<br/>');
			$values = array( "'", '&#8482;', '&quot;', '&#8482;', '&quot;', '&quot;', "'", '-', '...',"'" ,' ');
			return str_replace( $vars, $values, $str );
		}

		function cleanString ($cleanString) {


			$replace_this = "/\s{2,}/";
			$simplifyCharacter = ' ';
			$cleanString = preg_replace( $replace_this, $simplifyCharacter, $cleanString );

			return trim($cleanString);

		}




		function shorten($string,$repl =" ...",$start= 60,$limit = 100) {
			if(strlen($string) > $limit) {
				return substr_replace($string,$repl,$start,$limit);
			} else {
				return $string;
			};
		};

		function array_to_str($MyArray){
			$str_of_array_keys_n_vals = "";
			foreach($MyArray as $key=>$val){
				if(is_array($val)){
					$str_of_array_keys_n_vals .= "{". array_to_str($val)."} ";
				}
				else{
					$str_of_array_keys_n_vals .= "$val ";
				}
			}

			return substr($str_of_array_keys_n_vals, 0, strlen($str_of_array_keys_n_vals)-3);
		}



		function saveImage ($subNode, $h1Tag, $notesTag, $href, $altTag, $hrefPur){
			// Import image
			if ( file_exists( $href ) )
			{

				$classID = 5;
				$class =& eZContentClass::fetch( $classID );
				$creatorID = 14;
				// $parentNodeID = $placeNodeID;
				$parentNodeID = $subNode;

				$contentObject =& $class->instantiate( $creatorID, 1 );

				$nodeAssignment =& eZNodeAssignment::create( array(
                                                             'contentobject_id' => $contentObject->attribute( 'id' ),
                                                             'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                             'parent_node' => $subNode,
                                                             'sort_field' => 8,
			                                                 'sort_order' => 1,
			                                                 'is_main' => 1

			                                                 )
			                                                 );
			                                                 $nodeAssignment->store();

			                                                 $version =& $contentObject->version( 1 );
			                                                 $version->setAttribute( 'modified', eZDateTime::currentTimeStamp() );
			                                                 $version->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );

			                                                 $version->store();

			                                                 $contentObjectID = $contentObject->attribute( 'id' );
			                                                 $dataMap =& $contentObject->dataMap();

			                                                 $dataMap['name']->setAttribute( 'data_text', $h1Tag );
			                                                 $dataMap['name']->store();

			                                                 $dataMap['caption']->setAttribute( 'data_text', $notesTag );
			                                                 $dataMap['caption']->store();
			                                                 //echo $hrefPur;
			                                                 $imageContent =& $dataMap['image']->attribute( 'content' );
			                                                 $imageContent->initializeFromFile( $href , $altTag , $hrefPur);
			                                                 $dataMap['image']->store();



			                                                 include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
			                                                 $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $contentObjectID,
                                                                                                   'version' => 1 ) );

                                                                                                   $mypriorityID = $contentObjectID;

                                                                                                   return $mypriorityID;

			} //if file exist
		}
		$xmlDoc3 = mb_convert_encoding($xmlDoc, "UTF-8", "UTF-16LE");
		$xmlDoc = rebuild_textarea ($xmlDoc3);
		//$xmlDoc = mb_convert_encoding($xmlDoc, "iso-8859-1", "UTF-8");
		$dom =& $xml->domTree( $xmlDoc );


		$sectionNodeArray =& $dom->elementsByName( 'section' );

		$coolpresentationINI =& eZINI::instance( 'coolpresentation.ini' );

		$importClassIdentifier = $coolpresentationINI->variable( 'CSPImport', 'DefaultImportClass' );
		$customClassFound = false;
		if ( count( $sectionNodeArray ) > 0 )
		{
			$registeredClassArray = $coolpresentationINI->variable( 'CSPImport', 'RegisteredClassArray' );


			$class =& eZContentClass::fetchByIdentifier( "gallery" );

			$creatorID = 14; // 14 == admin
			$parentNodeID = $placeNodeID;
			$contentObject =& $class->instantiate( $creatorID, 1 );

			$nodeAssignment =& eZNodeAssignment::create( array(  'contentobject_id' => $contentObject->attribute( 'id' ),
                                                                     'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                                     'parent_node' => $parentNodeID,
                                                                     'sort_field' => 8,
			                                                         'sort_order' => 1,
                                                                     'is_main' => 1
                                                                     )
                                                                     );

                                                                     $nodeAssignment->store();

                                                                     $version =& $contentObject->version( 1 );
                                                                     $version->setAttribute( 'modified', eZDateTime::currentTimeStamp() );
                                                                     $version->setAttribute( 'status', EZ_VERSION_STATUS_DRAFT );
                                                                     $version->store();

                                                                     $contentObjectID = $contentObject->attribute( 'id' );
                                                                     $dataMap =& $contentObject->dataMap();

                                                                     $titleAttribudeIdentifier = 'name';

                                                                     $dataMap[$titleAttribudeIdentifier]->setAttribute( 'data_text', 'Presentation' );
                                                                     $dataMap[$titleAttribudeIdentifier]->store();

                                                                     include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
                                                                     $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $contentObjectID,
                                                                                             'version' => 1 ) );
                                                                                             $contentObject = eZContentObject::fetch( $contentObjectID );

                                                                                             $subNode =   $contentObject->attribute( 'main_node_id' );


                                                                                             $mainNode = $contentObject->attribute( 'main_node' );
                                                                                             // Create object stop.
                                                                                             $importResult['URLAlias'] = $mainNode->attribute( 'url_alias' );
                                                                                             $importResult['NodeName'] = $contentObject->attribute( 'name' );

                                                                                             // Check the defined sections in CSP document
                                                                                             $sectionNameArray = array();
                                                                                             $priority = array();
                                                                                             $priorityID = array();
                                                                                             $h1Content = array();
                                                                                             $textContent = array();
                                                                                             $notesContent = array();
                                                                                             $myprioritycounter = 0;
                                                                                             foreach ( $sectionNodeArray as $sectionNode )
                                                                                             {
                                                                                             	$sectionNameArray[] = strtolower( $sectionNode->attributeValue( "heading" ) ); // Überschriften (nicht h1), wobei die erste eine allgemeine ist, also eine Startseite.

                                                                                             	$level = 1;

                                                                                             	foreach ( $sectionNode->children() as $childNode )
                                                                                             	{

                                                                                             		if ($childNode->name = "h1"){
                                                                                             			$childNodeChildren = "";


                                                                                             			$childNodeChildren = $childNode->children( '#text' );

                                                                                             			$h1Content[$myprioritycounter][] = $childNodeChildren[0]->content;

                                                                                             		}
                                                                                             		// alt text or comment
                                                                                             		if ($childNode->name = "#text"){

                                                                                             			 
                                                                                             			// text orNotes
                                                                                             			$pos = strpos ($childNode->content(), "Notes:");
                                                                                             			if ($pos === false) {

                                                                                             				$textContent[$myprioritycounter][] = $childNode->content();

                                                                                             			} else{

                                                                                             				$notesContent[$myprioritycounter][] = $childNode->content();

                                                                                             			}
                                                                                             			 
                                                                                             		}


                                                                                             		// text oder Kommentar ende

                                                                                             		if ($childNode->name = "img" && $childNode->attributeValue( 'src' ) != "")
                                                                                             		{
                                                                                             			 
                                                                                             			 
                                                                                             			$hrefPur = $childNode->attributeValue( 'src' );

                                                                                             			$href = $this->ImportDir . $hrefPur;


                                                                                             		}  // img


                                                                                             	} // ( $sectionNode->children() as $childNode )
                                                                                             	$altTag = "";
                                                                                             	$notesTag = "";
                                                                                             	$h1Tag = "";
                                                                                             	$aTag = "";
                                                                                             	$altTag = cleanString(array_to_str($textContent[$myprioritycounter]));
                                                                                             	$notesTag = cleanString(array_to_str($notesContent[$myprioritycounter]));
                                                                                             	$h1Tag = cleanString(array_to_str($h1Content[$myprioritycounter]));
                                                                                             	$aTag = "";



                                                                                             	//    if ($aTag != ""){
                                                                                             	//                 $childNodeA= "";
                                                                                             	//                }else{
                                                                                             	//                	$aTag = "Presentation". $myprioritycounter;
                                                                                             	//                //	$h1Tag = $altTag;
                                                                                             	//                }

                                                                                             	 
                                                                                             	if ($altTag != ""){
                                                                                             		//  $altTag = array_to_str($textContent[$myprioritycounter]);
                                                                                             		// $charset = eZTextCodec::internalCharset();
                                                                                             		//	$altTag=  shorten($altTag  );




                                                                                             	}else{

                                                                                             		// $altTag = mb_convert_encoding($altTag, "iso-8859-1", "UTF-8");
                                                                                             		if ($h1Tag != ""){
                                                                                             			// $altTag =   mb_convert_encoding($h1Tag, "iso-8859-1", "UTF-8");
                                                                                             			$altTag =   shorten($h1Tag);
                                                                                             		}else{
                                                                                             			 
                                                                                             			$altTag = "Slide BB ". $myprioritycounter;
                                                                                             		}
                                                                                             	}
                                                                                             	//$altTag = "Slide BB ". $myprioritycounter;
                                                                                             	if ($notesTag != ""){
                                                                                             		$notesTag = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>"."<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\" "."  xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\"><paragraph>".$notesTag."</paragraph></section>";
                                                                                             	}else{
                                                                                             		$notesTag = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"."<section xmlns:image=\"http://ez.no/namespaces/ezpublish3/image/\" "." xmlns:xhtml=\"http://ez.no/namespaces/ezpublish3/xhtml/\" "." xmlns:custom=\"http://ez.no/namespaces/ezpublish3/custom/\" />";
                                                                                             	}
                                                                                             	//                                                                                             	               if ($h1Tag != ""){
                                                                                             	//                                                                                             	$h1Tag = shorten($h1Tag);
                                                                                             	//                                                                                             	                }else{
                                                                                             	//                                                                                             	                	//$h1Tag = "Slide ". $myprioritycounter;
                                                                                             	//                                                                                             	                	$h1Tag = shorten($altTag);
                                                                                             	//                                                                                          	                }
                                                                                             	$h1Tag = "BB Slide ". $myprioritycounter;
                                                                                             	//echo $charset = eZTextCodec::internalCharset();


                                                                                             	$priorityID[] =	saveImage ($subNode, $h1Tag, $notesTag, $href, $altTag, $hrefPur);

                                                                                             	$priority[] = $myprioritycounter;
                                                                                             	$myprioritycounter++;

                                                                                             } // end  foreach ( $sectionNodeArray as $sectionNode )

                                                                                             include_once( "lib/ezdb/classes/ezdb.php" );
                                                                                             $db =& eZDB::instance();
                                                                                             $db->setIsSQLOutputEnabled(true);
                                                                                             $priorityArray = $priority;
                                                                                             $priorityIDArray = $priorityID;
                                                                                             for ( $i=1; $i<count( $priorityArray );$i++ )
                                                                                             {
                                                                                             	$priority2 = (int) $priorityArray[$i];
                                                                                             	//     echo $priority2;
                                                                                             	$nodeID2 = $priorityIDArray[$i];
                                                                                             	//     echo $nodeID2;
                                                                                             	$db->query( "UPDATE ezcontentobject_tree SET priority=$priority2 WHERE contentobject_id=$nodeID2" );
                                                                                             }

                                                                                             // }


		} // if ( count( $sectionNodeArray ) > 0 ) end


		include_once( "lib/ezfile/classes/ezdir.php" );
		eZDir::recursiveDelete( $this->ImportDir );
		return $importResult;

	} // funct import end


	/*!
	 \private
	 Converts a dom node/tree to a plain ascii string
	 */
	function domToText( $node )
	{
		$textContent = "";

		foreach ( $node->children() as $childNode )
		{
			$textContent .= eZCSPImport::domToText( $childNode );
 		}

 		if  ( $node->name() == "#text" )
 		{
 			$textContent .= $node->content();
 		}
 		return $textContent;
 	}



 	var $ImportDir = "var/cache/coolpresentation/import/";
 	var $mypriorityID;
 	var $priorityID;
 }

 ?>
