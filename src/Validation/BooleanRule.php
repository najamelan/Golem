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
function ensureType( $input )
{
	if( ! is_bool( $input ) )

		$this->log->validationException( "Input should be boolean, got: " . var_export( $input, /* return = */ true ) );


	return $input;
}



/**
 * Needed for BaseRule
 */
protected
function areEqual( $a, $b )
{
	return $a === $b;
}



public
function sanitize( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


	$context = $this->annotateContext( $context );

	$input = parent::sanitize( $input, $context );

	return $this->validate( $input );
}



public
function validate( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


	$context = $this->annotateContext( $context );

	$input = parent::validate( $input, $context );

	return $input;
}


}
