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

/**
 * Used for input type checking.
 */
protected $inputType;


abstract protected function ensureType( $value );



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


	if( isset( $this->options[ 'type' ] ) )

		$this->options[ 'type' ] = $this->validateOptiontype( $this->options[ 'type' ] );
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



protected
function validateOptionType( $option )
{
	if( ! is_string( $option ) && ! $option instanceof String )

		$this->log->invalidArgumentException
		(
			  "Option 'type' should be an a string or a Golem\Reference\Data\String. Got: "
			. var_export( $option, /* return = */ true )
		)
	;


	return $option;
}



public
function sanitize( $input, $context )
{
	$this->inputType = Util::getType( $input );

	$input = $this->ensureType  ( $input           );

	$input = $this->sanitizeType( $input, $context );
	$input = $this->sanitizeIn  ( $input, $context );

	return $input;
}



public
function validate( $input, $context )
{
	$this->inputType = Util::getType( $input );

	$input = $this->ensureType  ( $input           );

	$input = $this->validateType( $input, $context );
	$input = $this->validateIn  ( $input, $context );

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
	if( ! isset( $this->options[ 'in' ] ) )

		return true;



	foreach( $this->options( 'in' ) as $allowed )

		if( $this->areEqual( $input, $allowed ) )

			return true;


	return false;
}



public
function type( $type = null )
{
	$args = func_get_args();


	// getter
	//
	if( $type === null )

		return $this->options[ 'type' ];


	// setter
	//
	$this->options[ 'type' ] = $this->validateOptionType( $type );

	return $this;
}



public
function sanitizeType( $input, $context )
{
	if( $this->isValidType( $this->inputType )  ||  $this->isValidType( Util::getType( $input ) ) )

		return $input;


	if( isset( $this->options[ 'defaultValue' ] ) )

		return $this->validate( $this->options[ 'defaultValue' ], $context );


	$this->log->validationException
	(
		  "$context: No default value set and input value [$input] is not of type: {$this->options['type']}, "
		. "got a: $this->inputType. for input: " . var_export( $input, /* return = */ true )
	);
}



public
function validateType( $input, $context )
{
	// Only check the type when the value came in, not the current one which might have been cast by ensureType.
	//
	if( $this->isValidType( $this->inputType ) )

		return $input;


	$this->log->validationException
	(
		  "$context: Input value [$input] is not of type: {$this->options['type']}, got a: $this->inputType. "
		. "for input: " . var_export( $input, /* return = */ true )
	);
}



public
function isValidType( $type )
{
	if
	(
		   ! isset( $this->options[ 'type' ] )
		|| $this->inputType === $this->options[ 'type' ]
 	)

		return true;


	return false;
}



}
