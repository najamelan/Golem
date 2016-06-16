<?php

namespace Golem\Data\Drivers;

use

	  Golem\Golem

	, Golem\Data\File
	, Golem\Validation\NumberRule
	, Golem\Validation\BooleanRule

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions
	, Golem\Traits\HasLog

	, Golem\Util

	, SplFileInfo
	, Exception

;


class Mat
{

use Seal, HasOptions, HasLog;

protected $golem ;


public
function __construct( Golem $golem, array $options = [] )
{
	$this->g = $golem;

	$this->setupOptions( $golem->options( 'Mat' ), $options );
	$this->setupLog();


	// $this->g->fileRule()

	// 	->executable()
	// 	->validate( $this->options( 'binary' ), 'Mat: option binary' )
	// ;
}



public
function hasMetaData( File $file )
{
	$output = $status = null;

	exec( $this->options( 'binary' ) . ' --check ' . escapeshellarg( $file->path() ), $output, $status );


	if( preg_match( '/is clean$/u', $output[0] ) === 1 )

		return false;


	elseif( preg_match( '/is not clean$/u', $output[0] ) === 1 )

		return true;


	else

		throw new Exception( "something went wrong" );
}



public
function metadata( File $file )
{
	$output = $status = null;

	exec( $this->options( 'binary' ) . ' --display ' . escapeshellarg( $file->path() ), $output, $status );

	return $this->g->text( join( $output, PHP_EOL ) );
}


public
function cleanMeta( File $file )
{
	if( $this->options( 'backup' ) )
	{
		if( ! copy( $file->path(), $file->path() . '.bak' ) )
		{
			$errors= error_get_last();
			$this->log->runtimeException( "Error backing up file [$file->path]: $errors[type]: $errors[message]" );
		}
	}


	$output = $status = null;

	exec( $this->options( 'binary' ) . ' ' . escapeshellarg( $file->path() ), $output, $status );

	$output = join( $output, PHP_EOL );


	if( $status !== 0 )

		$this->log->runtimeException( "Mat had an error cleaning metadata of file [$file->path]. $ouput. Status code: $status." );


	return $output;
}


}
