<?php
//
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.9.0
// BUILD VERSION: 17785
// COPYRIGHT NOTICE: Copyright (C) 2005-2007 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

class coolpresentationInfo
{
    function info()
    {
        return array( 'Name' => "http://www.coolscreen.de  PowerPoint Import extension",
                      'Version' => "1.0.1",
                      'Copyright' => "2005-2007 Ekkehard Dörre - <a href='http://www.coolscreen.de/'>coolscreen.de</a>",
                      'License' => "GNU General Public License v2.0",
                      '1. Includes the following third-party software' => array( 'Name' => 'PhpConcept Library - Zip Module',
                                                                              'Version' => '2.5',
                                                                              'License' => 'GNU/LGPL - Vincent Blavet - November 2004',
                                                                              'For more information' => 'http://www.phpconcept.net',
                                                                              ),
                      '2. Includes the following third-party software' => array(  'Name' => 'PowerPoint Tools for the Radiologist',
                                                                              'Version' => '0.5',
                                                                              'License' => 'GNU/GPL - Bonerad - November 2004',
                                                                              'For more information' => 'http://ppradtools.sourceforge.net/',
                                                                               ),
                   '3. Includes the following third-party software' => array(   'Name' => 'OpenOffice.org extension',
                                                                              'Version' => '1.0',
                                                                              'License' => 'GNU/GPL - eZ systems AS - May 2005',
                                                                              'For more information' => 'http://ez.no/community/contribs/import_export/openoffice_org_extension' )

                      );
    }
}
?>
