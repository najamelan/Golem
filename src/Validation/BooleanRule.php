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
	parent::__construct( $golem );

	$this->setupOptions( $golem->options( 'Validation', 'BooleanRule' ), $options );
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



public
function _validate( $input, $context )
{
	if( $this->validNull( $input ) )

		return null;


	$context = $this->annotateContext( $context );

	$input = parent::_validate( $input, $context );

	return $input;
}


protected function validateOptions() {}


}
