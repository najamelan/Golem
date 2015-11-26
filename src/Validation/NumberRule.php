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
 * The basic number rule.
 *
 */
class      NumberRule
extends    BaseRule
{



public
function __construct( Golem $golem, array $options = [] )
{
	parent::__construct( $golem );

	$this->setupOptions( $golem->options( 'Validation', 'NumberRule' ), $options );
}



protected
function validateOptions()
{
	parent::validateOptions();

	$o = &$this->options;

	isset( $o[ 'min' ] )  &&  $this->validateOptionMin();
	isset( $o[ 'max' ] )  &&  $this->validateOptionMax();
}



protected
function validateOptionMin()
{
	$o = &$this->options[ 'min' ];


	if( ! is_numeric( $o ) )

		$this->log->invalidArgumentException
		(
			  'Validation misconfiguration - expected numeric $min. Got: '
			. var_export( $o, /* return = */ true )
		)
	;


	$max = &$this->options[ 'max' ];

	if( isset( $max )  &&  $o > $max )

		$this->log->invalidArgumentException
		(
			  "Validation misconfiguration - \$min must be smaller or equal than \$max ($max). Got: "
			. var_export( $o, /* return = */ true )
		)
	;


	$o = (int) $o;
}



protected
function validateOptionMax()
{
	$o = &$this->options[ 'max' ];


	if( ! is_numeric( $o ) )

		$this->log->invalidArgumentException
		(
			  'Validation misconfiguration - expected numeric $max. Got: '
			. var_export( $o, /* return = */ true )
		)
	;


	$min = &$this->options[ 'min' ];

	if( isset( $min )  &&  $o < $min )

		$this->log->invalidArgumentException
		(
			  "Validation misconfiguration - \$max must be bigger or equal than \$min ($min). Got: "
			. var_export( $o, /* return = */ true )
		)
	;


	$o = (int) $o;
}



public
function ensureType( $number )
{

	if( ! is_numeric( $number ) )

		$this->log->validationException( "Cannot turn [$number] into a number." );


	$type = $this->options[ 'type' ];


	switch( $type )
	{
		case 'integer' : $number = (int)    $number;
		                 break;

		case 'float'   : $number = (float)  $number;
		                 break;

		case null      :
		case 'double'  : $number = (double) $number;
		                 break;

		default        : $this->log->unexpectedValueException( "Unsupported type [$type]. Should be one of: 'integer', 'float' or 'double'." );
	}


	return $number;
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

	$input = $this->sanitizeMin( $input, $context );
	$input = $this->sanitizeMax( $input, $context );

	return $this->validate( $input, $context );
}



public
function validate( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


	$context = $this->annotateContext( $context );

	$input = parent::validate( $input, $context );

	$input = $this->validateMin( $input, $context );
	$input = $this->validateMax( $input, $context );


	return $input;
}



public
function min( $min )
{
	// getter
	//
	if( $min === null )

		return $this->options[ 'min' ];


	// setter
	//
	$this->checkSeal();

	$this->options[ 'min' ] = $min;
	$this->validateOptionMin();

	return $this;
}



public
function max( $max )
{
	// getter
	//
	if( $max === null )

		return $this->options[ 'max' ];


	// setter
	//
	$this->checkSeal();

	$this->options[ 'max' ] = $max;
	$this->validateOptionmax();

	return $this;
}



protected
function sanitizeMin( $input, $context )
{
	if( $this->isValidMin( $input ) )

		return $input;


	$min     = $this->options( 'min' );
	$default = isset( $this->options[ 'default' ] )  ?  $this->options[ 'default' ]  :  $min;


	if( $input < $min )

		return $this->validateMin( $default );
}



protected
function validateMin( $input, $context )
{
	if( $this->isValidMin( $input ) )

		return $input;


	$this->log->validationException
	(
		  "$context: Input value [$input] is smaller than the minimum allowed, should be bigger or equal than: "
		. var_export( $this->options( 'min' ), /* return = */ true )
	);
}



public
function isValidMin( $input )
{
	if( ! isset( $this->options[ 'min' ] ) )

		return true;


	if( $input >= $this->options( 'min' ) )

		return true;


	return false;
}



protected
function sanitizeMax( $input, $context )
{
	if( $this->isValidMax( $input ) )

		return $input;


	$max     = $this->options( 'max' );
	$default = isset( $this->options[ 'default' ] )  ?  $this->options[ 'default' ]  :  $max;


	if( $input > $max )

		return $this->validateMax( $default );
}



protected
function validateMax( $input, $context )
{
	if( $this->isValidMax( $input ) )

		return $input;


	$this->log->validationException
	(
		  "$context: Input value [$input] is bigger than the maximum allowed, should be smaller or equal than: "
		. var_export( $this->options( 'max' ), /* return = */ true )
	);
}



public
function isValidMax( $input )
{
	if( ! isset( $this->options[ 'max' ] ) )

		return true;


	if( $input <= $this->options( 'max' ) )

		return true;


	return false;
}

}
