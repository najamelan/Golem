<?php
/**
 *
 */



namespace Golem\Validation;

use

	  Golem\Golem

	, Golem\iFace\ValidationRule

	, Golem\Validation\BaseRule

	, Golem\Data\File

	, Golem\Util
;

/**
 * The filename validation rule.
 *
 */
class   FileRule
extends BaseRule
{

public
function __construct( Golem $golem, array $options = [] )
{
	// Since the user can send in a mix of options concerning all levels of the class hierarchy,
	// we do not send them to the superclasses. First every class sets their default options in order
	// and afterwards we will override all with the user options.
	//
	parent::__construct( $golem );


	// Thus, $options will be empty unless this is this is the last subclass and the user
	// sets options through the constructor.
	//
	$this->setupOptions( $golem->options( 'Validation', 'FileRule' ), $options );


	// This shouldn't be done in superclasses, because it needs to be done
	// after all constructors have run and after the userset options are
	// merged in.
	//
	if( __CLASS__ === get_class( $this ) )

		$this->validateOptions();
}



protected
function validateOptions()
{
	// Always call this, since all the way up to BaseRule there are options to validate.
	// Every class takes care of validating it's own supported options.
	//
	parent::validateOptions();


	$o = &$this->options;

	isset( $o[ 'exists' ] )  &&  $o[ 'exists' ] = $this->validateOptionExists( $o[ 'exists' ] );
	isset( $o[ 'isDir'  ] )  &&  $o[ 'isDir'  ] = $this->validateOptionIsDir ( $o[ 'isDir'  ] );
}



protected
function validateOptionExists( $o )
{
	if( ! is_bool( $o ) )

		$this->log->invalidArgumentException
		(
			"Option [exists] should be a boolean. Got: " . Util::getType( $o )
		)
	;


	// For now we haven't dealt with unsetting options, but in any case don't allow setting
	// exists to false when other options that require it are active
	//
	if( !$o )
	{
		if
		(
			$this->isDir()
		)
		{
			$this->log->invalidArgumentException
			(
				  "Option [exists] cannot be set to false when one of the following is set to true:\n"
				. " - isDir\n"
			);
		}
	}


	return $o;
}



protected
function validateOptionIsDir( $o )
{
	if( ! is_bool( $o ) )

		$this->log->invalidArgumentException
		(
			"Option [isDir] should be a boolean. Got: " . Util::getType( $o )
		)
	;


	// it doesn't make sense unless the file has to exist
	//
	$this->exists( true );


	return $o;
}



protected
function ensureType( $file, $context )
{
	if( ! $file instanceof File )

		$this->log->validationException
		(
			  "$context: Input value is not of type Golem::File."
			. " Got a: " . Util::getType( $file ) . " for input: " . print_r( $file, /* return = */ true )
		)
	;


	return $file;
}



public
function sanitize( $input, $context )
{
	$context = $this->init( $context );


	if( $this->validNull( $input ) )

		return null;


	$input   = parent::sanitize( $input, $context );

	if( $this->options( 'exists' ) !== null ) $input = $this->sanitizeExists( $input, $context );
	if( $this->options( 'isDir'  ) !== null ) $input = $this->sanitizeIsDir ( $input, $context );

	return $this->_validate( $input, $context );
}




public
function validate( $input, $context )
{
	$context = $this->init( $context );

	return $this->_validate( $input, $context );
}



/**
 * Allows _validate to be called internally without reinitializing context
 *
 */
protected
function _validate( $input, $context )
{
	if( $this->validNull( $input ) )

		return null;


	$input   = parent::_validate( $input, $context );

	if( $this->options( 'exists' ) !== null ) $input = $this->validateExists( $input, $context );
	if( $this->options( 'isDir'  ) !== null ) $input = $this->validateIsDir ( $input, $context );


	if( __CLASS__ === get_class( $this ) )

		return $this->finalize( $input );


	return $input;
}



protected
function  finalize( $input )
{
	// $outputType = isset( $this->options[ 'type' ] ) ? $this->options[ 'type' ] : $this->inputType;

	// if( $outputType === 'string' )

	// 	$input = $input->raw();


	return $input;
}



/**
 * Exists validation
 *
 */

public
function exists( $exists = null )
{
	// getter
	//
	if( $exists === null )

		return $this->options( 'exists' );


	// setter
	//
	$this->setOpt( 'exists', $this->validateOptionExists( $exists ) );

	return $this;
}



protected
function sanitizeExists( $input, $context )
{
	if( $this->isValidExists( $input ) )

		return $input;


	// we need to sanitize it
	//
	if( $this->options( 'exists' ) === true )
	{
		if( $this->options( 'isDir' ) )

			$input->mkdir();

		else

			$input->touch();
	}


	else

		$input->rm();


	return $input;
}



protected
function validateExists( $input, $context )
{
	if( $this->isValidExists( $input ) )

		return $input;


	$error = $this->options( 'exists' ) ?

		  "$context: File [$input] does not exist."
		: "$context: File [$input] exists but it shouldn't."
	;


	$this->log->validationException( $error );
}



public
function isValidExists( $input )
{
	if( $input->exists() === $this->options( 'exists' ) )

		return true;


	return false;
}



/**
 * IsDir validation
 *
 */

public
function isDir( $isDir = null )
{
	// getter
	//
	if( $isDir === null )

		return $this->options( 'isDir' );


	// setter
	//
	$this->setOpt( 'isDir', $this->validateOptionIsDir( $isDir ) );

	return $this;
}



protected
function sanitizeIsDir( $input, $context )
{
	if( $this->isValidIsDir( $input ) )

		return $input;


	// Creation should already be taken care of by 'exists', but if it exists and is not the right type
	// we should sanitize it here.
	//
	$input->rm();


	if( $this->options( 'isDir' ) )

		$input->mkdir();

	else

		$input->touch();


	return $input;
}



protected
function validateIsDir( $input, $context )
{
	if( $this->isValidExists( $input ) )

		return $input;


	$error = $this->options( 'isDir' ) ?

		  "$context: File [$input] exists but is not a directory."
		: "$context: File [$input] is a directory but it shouldn't be."
	;


	$this->log->validationException( $error );
}



public
function isValidIsDir( $input )
{
	if( $input->isDir() === $this->options( 'isDir' ) )

		return true;


	return false;
}

}
