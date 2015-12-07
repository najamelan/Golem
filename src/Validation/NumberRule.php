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
	// Since the user can send in a mix of options concerning all levels of the class hierarchy,
	// we do not send them to the superclasses. First every class sets their default options in order
	// and afterwards we will override all with the user options.
	//
	parent::__construct( $golem );


	// Thus, $options will be empty unless this is this is the last subclass and the user
	// sets options through the constructor.
	//
	$this->setupOptions( $golem->options( 'Validation', 'NumberRule' ), $options );


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

	isset( $o[ 'min'  ] )  &&  $o[ 'min'  ] = $this->validateOptionEncoding( $o[ 'min'  ] );
	isset( $o[ 'max'  ] )  &&  $o[ 'max'  ] = $this->validateOptionLength  ( $o[ 'max'  ] );
	isset( $o[ 'type' ] )  &&  $o[ 'type' ] = $this->validateOptionType    ( $o[ 'type' ] );
}



protected
function validateOptionMin( $o )
{
	if( ! is_numeric( $o ) )

		$this->log->invalidArgumentException
		(
			  'Validation misconfiguration - expected numeric $min. Got: '
			. var_export( $o, /* return = */ true )
		)
	;


	return (int) $o;
}



protected
function validateOptionMax( $o )
{
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


	return (int) $o;
}



protected
function validateOptionType( $o )
{
	if( ! in_array( $o, [ 'integer', 'float', 'double' ] ) )

		$this->log->unexpectedValueException
		(
			"Unsupported type [$o]. Should be one of: 'integer', 'float', 'double'."
		)
	;


	return $o;
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



public
function sanitize( $input, $context )
{
	if( $this->validNull( $input ) )

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
	if( $this->validNull( $input ) )

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
	return $this->setOpt( 'min', $this->validateOptionMin( $min ) );
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
	return $this->setOpt( 'max', $this->validateOptionMax( $max ) );
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
