<?php

/**
 * File class of library Golem.
 *
 */

namespace Golem\Reference\Data;

use
	  \Golem\Reference\Codecs\ParseYAML
	, \Exception
	, \finfo
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
	private $fileName;
	private $fileContents;



	public
	function __construct( $fileName )
	{
		$this->fileName = $fileName;

		$this->readFile();
	}


	/**
	 * Stores the file contents in $this->fileContents
	 *
	 */
	private
	function readFile()
	{
		if( ! file_exists( $this->fileName ) )

			throw new Exception( "Cannot find file: {$this->fileName}" );


		$this->fileContents = file_get_contents( $this->fileName );
	}


	/**
	 * Identifies the file type and parsed it with an appropriate parser.
	 *
	 */
	public
	function parse()
	{
		return $this->parser()->decode( $this->fileContents );
	}




	public
	function parser()
	{
		$finfo = new finfo; // return mime type ala mimetype extension

		$mime = $finfo->file( $this->fileName, FILEINFO_MIME_TYPE );


		switch( $mime )
		{
			case 'text/plain' : $mime = $this->getMimeFromExtension();
			                    /* fallthrough */

			case 'text/x-yaml': return new ParseYAML( $this->fileContents );

			default           : throw new Exception( "Unsupported mime type of file: '{$this->fileName}'. Detected type: '$mime'." );
			                    break;
		}
	}


	private
	function  getMimeFromExtension()
	{
		$extension = pathinfo( $this->fileName, PATHINFO_EXTENSION );


		switch( $extension )
		{
			case 'yml': return "text/x-yaml";
			            break;

			default   : return "text/plain";
			            break;
		}
	}
}
