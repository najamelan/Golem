<?php

/**
 * File class of library Golem.
 *
 */

namespace Golem\Data;

use

	  Golem\Golem
	, Golem\Data\String

	, Golem\iFace\Data\Driver as iFileDriver

	, Golem\Traits\HasLog

	, Golem\Data\Drivers\YamlDriver
	, Golem\Data\Drivers\JpgDriver

	, SplFileInfo
	, finfo

;


/**
 * Basic file reading functionality.
 *
 * Does smart parsing of text formats based on mime type and extension.
 *
 */
class File
//implements \Golem\iFace\Data\File
{

use HasLog;

private $golem;

private $info;
private $path;
private $driver;
private $mime;



public
function __construct( Golem $golem, $path )
{
	$this->golem = $golem;
	$this->path  = $path ;
	$this->info  = new SplFileInfo( $path );

	$this->setupLog();
}



/**
 * Returns the filename.
 *
 * @return string The filename including extension
 *
 * @api
 *
 */
public
function name()
{
	return $this->info->getBasename();
}



/**
 * Returns the extension.
 *
 * @return string The extension of the file
 *
 * @api
 *
 */
public
function extension()
{
	return $this->info->getExtension();
}



/**
 * Returns whether the file exists on the filesystem.
 *
 * @return bool Whether the file exists
 *
 * @api
 *
 */
public
function exists()
{
	// file_exists caches it's results
	//
	clearstatcache( /*clear_realpath_cache = */ true, $this->path );

	return file_exists( $this->path );
}



/**
 * Returns the absolute path to the file.
 *
 * @return string Absolute path including filename
 *
 * @api
 *
 */
public
function path()
{
	return $this->path;
}



/**
 * Returns the absolute path to the file.
 *
 * @return string Absolute path including filename
 *
 * @api
 *
 */
public
function __toString()
{
	return $this->path;
}



/**
 * Getter/Setter for the mime type of the file.
 *
 * @param  string|Golem\Data\String $mime The mime type to set.
 * @return string|Golem\Data\File The mime as a string or $this.
 *
 * @throws Exception if the input parameter isn't valid.
 *
 * @api
 *
 */
public
function mime( $mime = null )
{
	// getter
	//
	if( $mime === null )
	{
		if( ! $this->mime )
		{
			$finfo = new finfo;
			$mime  = $finfo->file( $this->path, FILEINFO_MIME_TYPE );


			if( $mime === 'text/plain' )

				$mime = $this->extension2mime();


			$this->mime( $mime );
		}


		return $this->mime;
	}


	// setter
	//
	$this->mime = $this->golem->stringRule()

		->encoding( $this->golem->options( 'Golem', 'configEncoding' ) )
		->type    ( 'string'                                           )
		->sanitize( $mime, 'parameter: $mime'                          )
	;

	return $this;
}



protected
function  extension2mime()
{
	switch( $this->extension() )
	{
		case 'yaml':
		case 'yml' : return "text/x-yaml";

		case 'txt' :
		default    : return "text/plain";
	}
}



/**
 * returns a backend class that can handle stuff specific to file formats,
 * like reading metadata.
 *
 */
public
function driver( iFileDriver $driver = null )
{
	// setter
	//
	if( $driver )
	{
		$this->driver = $driver;
		return $this;
	}


	// getter
	//
	if( $this->driver )

		return $this->driver;


	// We need to generate one...
	// This is a bit of guesswork and not always accurate.
	//
	$mime = $this->mime();
	switch( $mime )
	{
		case 'text/x-yaml': return $this->driver = new YamlDriver( $this->golem, $this );
		case 'image/jpeg' : return $this->driver = new JpgDriver ( $this->golem, $this );

		default: $this->log->runtimeException( "Unsupported mime type of file: '{$this->path}'. Detected type: '$mime'." );
	}

}



/**
 * Getter/Setter for the contents of the file.
 *
 * @return string|Golem\Data\File The contents as a string.
 *
 * @throws Exception When the file does not exist.
 * @throws Exception When writing the file fails.
 *
 * @api
 *
 */
public
function content( $content = null, $flags = 0 )
{
	// getter
	//
	if( $content === null )
	{
		if( ! $this->exists() )

			$this->log->runtimeException( "Cannot find file [$this->path]");


		return file_get_contents( $this->path );
	}


	// setter
	//
	$result = file_put_contents( $this->path, $content, $flags );


	if( $result === false )

		$this->log->runtimeException( "Writing to the file failed (file_put_contents returned false)" );


	return $this;
}



/**
 * Appends data to a file.
 *
 * @param string|array|stream resource $content the data to write (see documentation for file_put_contents)
 * @param int                          $flags   the flags to pass to file_put_contents. You can mainly use
 *                                              this to unset LOCK_EX if desired.
 *
 */
public
function append( $content = null, $flags = FILE_APPEND | LOCK_EX )
{
	if( file_put_contents( $this->path, $content, $flags ) === false )

		$this->log->runtimeException( 'file_put_contents failed on ' . $this->path );


	return $this;
}



/**
 * Identifies the file type and parsed it with an appropriate parser.
 *
 */
public
function parse()
{
	return $this->driver()->parse();
}



/**
 * Creates a file or changes the timestamp on it.
 *
 */
public
function touch( $time = null, $atime = null )
{
	$args = func_get_args();
	array_unshift( $args, $this->path );


	if( ! call_user_func_array( 'touch', $args ) )

		$this->log->exception( new RuntimeException( 'Touch failed on ' . $this->path ) );


	return $this;
}



/**
 * Removes a file.
 *
 */
public
function rm()
{
	if( ! $this->exists() )

		return $this;


	if( ! unlink( $this->path ) )

		$this->log->exception( new RuntimeException( 'unlink failed on ' . $this->path ) );


	return $this;
}



/**
 * @return string The metadata contained in the file.
 *
 */
public
function hasMetaData()
{
	return $this->driver()->hasMetaData();
}



/**
 * @return string The metadata contained in the file.
 *
 */
public
function metaData()
{
	return $this->driver()->metaData();
}



/**
 * Clean metadata.
 *
 */
public
function cleanMeta()
{
	return $this->driver()->cleanMeta();
}

}
