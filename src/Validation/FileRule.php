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
}



protected
function validateOptionExists( $o, $param = 'exists' )
{
	if( ! is_bool( $o ) )

		$this->log->invalidArgumentException
		(
			"Option [exists] should be a boolean. Got: " . Util::getType( $o )
		)
	;

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


	$input   = parent::sanitize        ( $input, $context );

	$input   = $this->sanitizeExists   ( $input, $context );

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


	$input   = parent::_validate       ( $input, $context );

	$input   = $this->validateExists   ( $input, $context );


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

		return $this->options[ 'exists' ];


	// setter
	//
	$this->setOpt( 'exists', $this->validateOptionexists( $exists ) );

	return $this;
}



protected
function sanitizeExists( $input, $context )
{
	if( ! $this->isValidExists( $input ) )
	{

		if( $this->options( 'exists' ) )

			$input->touch();


		else

			$input->rm();

	}


	return $input;
}



protected
function validateExists( $input, $context )
{
	if( $this->isValidExists( $input ) )

		return $input;


	$error = $this->options( 'exists' ) ?

		  "$context: File [$input] does not exist."
		: "$context: File [$input] exist but it shouldn't."
	;


	$this->log->validationException( $error );
}



public
function isValidExists( $input )
{
	if
	(
		   isset( $this->options[ 'exists' ] )
		&& $input->exists() === $this->options[ 'exists' ]
	)

		return true;


	return false;
}

}
