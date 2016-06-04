<?php
/**
 *
 */



namespace Golem\Validation;

use

	  Golem\Golem

	, Golem\iFace\ValidationRule

	, Golem\Validation\BaseRule

	, Golem\Util
;

/**
 * The basic boolean rule.
 *
 */
class      BooleanRule
extends    BaseRule
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
	$this->setupOptions( $golem->options( 'Validation', 'BooleanRule' ), $options );


	// This shouldn't be done in superclasses, because it needs to be done
	// after all constructors have run and after the userset options are
	// merged in.
	//
	if( __CLASS__ === get_class( $this ) )

		$this->validateOptions();
}



protected function validateOptions()
{
	// Always call this, since all the way up to BaseRule there are options to validate.
	// Every class takes care of validating it's own supported options.
	//
	parent::validateOptions();
}



public
function ensureType( $input, $context )
{
	if( ! is_bool( $input ) )

		$this->log->validationException( "$context: Input should be boolean, got: " . var_export( $input, /* return = */ true ) );


	return $input;
}



public
function sanitize( $input, $context )
{
	$context = $this->init( $context );


	if( $this->validNull( $input ) )

		return null;


	$input = parent::sanitize( $input, $context );

	return $this->_validate( $input );
}




public
function validate( $input, $context )
{
	$context = $this->init( $context );

	return $this->_validate( $input, $context );
}



protected
function _validate( $input, $context )
{
	if( $this->validNull( $input ) )

		return null;


	$context = $this->annotateContext( $context );

	$input = parent::_validate( $input, $context );

	return $input;
}

}
