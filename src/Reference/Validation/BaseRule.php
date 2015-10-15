<?php
/**
 *
 */



namespace Golem\Reference\Validation;

use

	  Golem\Golem

	, Golem\iFace\ValidationRule

	, Golem\Reference\Traits\Seal
	, Golem\Reference\Traits\HasOptions
	, Golem\Reference\Traits\HasLog

	, Golem\Reference\Data\String

	, Golem\Reference\Util
;

/**
 * The basic string rule.
 *
 */
abstract
class      BaseRule
implements ValidationRule
{

use HasOptions, Seal, HasLog;

protected $golem;



public
function __construct( Golem $golem, array $defaults = [], array $options = [] )
{
	$this->golem = $golem;

	$this->setupOptions( $defaults, $options );
	$this->setupLog();

	$this->validateOptions();
}



protected
function validateOptions()
{
	if( isset( $this->options[ 'in' ] ) )

		$this->options[ 'in' ] = $this->validateOptionIn( $this->options[ 'in' ] );
}



protected
function validateOptionIn( $option )
{
	if( ! is_array( $option ) )

		$this->log->invalidArgumentException
		(
			  "Option 'in' should be an array. Got: "
			. var_export( $option, /* return = */ true )
		)
	;


	foreach( $option as $key => $allowed )

		$option[ $key ] = $this->ensureType( $allowed );


	return $option;
}



public
function sanitize( $input, $context )
{
	$input = $this->ensureType( $input           );
	$input = $this->sanitizeIn( $input, $context );

	return $input;
}



public
function validate( $input, $context )
{
	$input = $this->ensureType( $input           );
	$input = $this->validateIn( $input, $context );

	return $input;
}



public
function in()
{
	$args = func_get_args();


	// getter
	//
	if( ! $args )

		return $this->options[ 'in' ];


	// setter
	// if list is passed as array
	//
	if( is_array( $args[ 0 ] ) )

		$args = $args[ 0 ];


	$this->options[ 'in' ] = $this->validateOptionIn( $args );

	return $this;
}



public
function sanitizeIn( $input, $context )
{
	if( $this->isValidIn( $input ) )

		return $input;


	if( isset( $this->options[ 'defaultValue' ] ) )

		return $this->validate( $this->options[ 'defaultValue' ], $context );


	$this->log->validationException
	(
		  "$context: No default value set and input value [$input] not found in list: "
		. var_export( $this->options( 'in' ), /* return = */ true )
	);
}



public
function validateIn( $input, $context )
{
	if( $this->isValidIn( $input ) )

		return $input;


	$this->log->validationException
	(
		  "$context: Input value [$input] not found in list: "
		. var_export( $this->options( 'in' ), /* return = */ true )
	);
}



public
function isValidIn( $input )
{
	if( ! isset( $option ) )

		return true;



	foreach( $this->options( 'in' ) as $allowed )

		if( $this->areEqual( $input, $allowed ) )

			return true;


	return false;
}



}
