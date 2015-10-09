<?php

/**
 * File class of library Golem.
 *
 */

namespace Golem\Reference\Data;

use

	  Golem\Golem
	, Golem\Reference\Codecs\ParseYAML

	, RuntimeException
	, finfo

;


/**
 * Basic file reading functionality.
 *
 * Does smart parsing of text formats based on mime type and extension.
 *
 */
class      File
//implements \Golem\iFace\Data\File
{
	private $filename;
	private $golem   ;



	public
	function __construct( Golem $golem, $filename )
	{
		$this->filename = $filename;
		$this->golem    = $golem;
	}


	/**
	 * Returns the filename.
	 *
	 * @return string The filename passed to the constructor
	 *
	 * @api
	 *
	 */
	public
	function filename()
	{
		return $this->filename;
	}


	/**
	 * Returns the contents of the file.
	 *
	 * @return string|boolean The contents as a string or false on failure.
	 *
	 * @throws Exception When the file does not exist.
	 *
	 * @api
	 *
	 */
	public
	function readFile()
	{
		if( ! file_exists( $this->filename ) )

			throw new RuntimeException( "Cannot find file: {$this->filename}" );


		return file_get_contents( $this->filename );
	}


	/**
	 * Identifies the file type and parsed it with an appropriate parser.
	 *
	 */
	public
	function parse()
	{
		return $this->parser()->decode( $this->readFile() );
	}




	protected
	function parser()
	{
		// Reading it in before using finfo also means we throw an Exception if the file does not exist.
		//
		$contents = $this->readFile();
		$finfo    = new finfo;

		$mime     = $finfo->file( $this->filename, FILEINFO_MIME_TYPE );


		if( $mime === 'text/plain' )

			$mime = $this->extension2mime();



		switch( $mime )
		{
			case 'text/x-yaml': return new ParseYAML( $contents );

			                    // for now we don't use logger()->exception because this class is used to read
			                    // the default options, and when it fails, we can't get a logger...
			                    //
			default           : throw  new RuntimeException( "Unsupported mime type of file: '{$this->filename}'. Detected type: '$mime'." );
		}
	}


	protected
	function  extension2mime( $extension = null )
	{
		if( $extension === null )

			$extension = pathinfo( $this->filename, PATHINFO_EXTENSION );


		switch( $extension )
		{
			case 'yml': return "text/x-yaml";
			            break;

			default   : return "text/plain";
			            break;
		}
	}
}
