<?php

/**
 * JpgDriver class of library Golem.
 *
 */

namespace Golem\Data\Drivers;

use

	  Golem\Golem

	, Golem\iFace\Data\FileDriver as iFileDriver

	, Golem\Data\Drivers\Mat
	, Golem\Data\Drivers\BaseDriver
	, Golem\Data\String
	, Golem\Data\File

	, RuntimeException
	, SplFileInfo
	, finfo

;


/**
 * Basic file reading functionality.
 *
 * Does smart parsing of text formats based on mime type and extension.
 *
 */
class   JpgDriver
extends BaseDriver
{

private $file  ;
private $parser;
private $metaBackend;



public
function __construct( Golem $golem, File $file, $options = [] )
{
	parent::__construct( $golem );

	$this->setupOptions( $golem->options( 'Driver', 'jpeg' ), $options );

	$this->file = $file;


	switch( $this->options( 'metaBackend' ) )
	{
		case 'Mat': $this->metaBackend = new Mat( $golem );
	}
}



public
function can( $capability )
{
	$this->golem->validator()->string()

		->encoding( $this->cfgEnc                             )
		->in      ( iFileDriver::CAPABILITIES                 )
		->validate( $capability, 'can: parameter $capability' )
	;


	switch( $capability )
	{
		case 'readMeta' :
		case 'cleanMeta': return true;

		case 'parse'    :
		default         : return false;
	}
}



public function hasMetadata()
{

}



public function metaData()
{
	return $this->metaBackend->metaData( $this->file );
}



public function cleanMeta()
{
	return $this->metaBackend->cleanMeta( $this->file );
}



public function parse(){ $this->log()->exception( 'Cannot parse Jpeg files' ); }


}
