<?php
/**
 *
 */



namespace Golem\Validation;

use

	  Golem\Golem

	, Golem\iFace\ValidationRule

	, Golem\Data\String
	, Golem\Validation\BaseRule

	, Golem\Util
;

/**
 * The basic string rule.
 *
 */
class      StringRule
extends    BaseRule
{


private $encodingUsed = false;



public
function __construct( Golem $golem, array $options = [] )
{
	parent::__construct( $golem );

	$this->setupOptions( $golem->options( 'Validation', 'StringRule' ), $options );
}



protected
function validateOptions()
{
	parent::validateOptions();

	$o = &$this->options;

	isset( $o[ 'encoding' ] )  &&  $this->validateOptionEncoding();
	isset( $o[ 'length'   ] )  &&  $this->validateOptionLength  ();
}



protected
function validateOptionEncoding()
{
	$o = &$this->options[ 'encoding' ];


	if( ! String::encodingSupported( $o ) )

		$this->log->unexpectedValueException( "Encoding passed in not supported by the mbstring extension: [$o]" );

}



protected
function validateOptionLength()
{
	$o = &$this->options[ 'length' ];


	if( is_numeric( $o ) )
	{
		$o = (int) $o;
		return;
	}


	$this->log->invalidArgumentException
	(
		  'Validation misconfiguration - expected numeric $length. Got: '
		. var_export( $o, /* return = */ true )
	);
}



protected
function validateOptionType()
{
	parent::validateOptionType();

	$o = &$this->options[ 'type' ];



	if( ! in_array( $o, [ 'string', 'Golem\Data\String' ] ) )

		$this->log->unexpectedValueException
		(
			"Unsupported type [$o]. Should be one of: 'int', 'float' or 'double'."
		)
	;


	return $o;
}



protected
function ensureType( $string )
{
	if( ! $string instanceof String )
	{
		$string = $this->golem->string( $string, $this->encoding() );
		$this->encodingUsed = true;
	}


	elseif( $string->encoding() !== $this->encoding() )

		$string->encoding( $this->encoding() );


	return $string;
}



/**
 * Needed for BaseRule
 */
protected
function areEqual( String $a, String $b )
{
	return $a->equals( $b );
}



public
function encoding( $encoding = null )
{
	// getter
	//
	if( $encoding === null )

		return $this->options( 'encoding' );


	// setter
	//
	$this->checkSeal();

	if( $this->encodingUsed )

		$this->log->logicException( 'Already used encoding to interprete scalar strings, cannot change anymore' );


	$this->options[ 'encoding' ] = $encoding;
	$this->validateOptionEncoding();

	return $this;
}



public
function sanitize( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


	$context = $this->annotateContext( $context );

	$input = parent::sanitize( $input, $context );

	$input = $this->sanitizeLength( $input, $context );

	return $this->validate( $input );
}



public
function validate( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


	$context = $this->annotateContext( $context );

	$input = parent::validate( $input, $context );

	$input = $this->validateLength( $input, $context );

	return $input;
}



public
function length( $length )
{
	// getter
	//
	if( $length === null )

		return $this->options[ 'length' ];


	// setter
	//
	$this->checkSeal();

	$this->options[ 'length' ] = $length;
	$this->validateOptionLength();

	return $this;
}



protected
function sanitizeLength( $input, $context )
{
	if( $this->isValidLength( $input ) )

		return $input;


	$length        = $input->length();
	$allowedLength = $this ->options( 'length' );


	if( $length > $allowedLength )

		return $this->validateLength( $input->substr( 0, $allowedLength ) );


	// length is < allowedLength
	//
	if( isset( $this->options[ 'defaultValue' ] ) )

		return $this->validate( $this->options[ 'defaultValue' ], $context );


	$this->log->validationException
	(
		  "$context: No default value set and input value [$input] is shorter than allowed length: "
		. var_export( $this->options( 'length' ), /* return = */ true ) . " characters, got: {$input->length()}"
	);

}



protected
function validateLength( $input, $context )
{
	if( $this->isValidLength( $input ) )

		return $input;


	$this->log->validationException
	(
		  "$context: Input value [$input] is not of the correct length: should be: "
		. var_export( $this->options( 'length' ), /* return = */ true ) . " characters, got: {$input->length()}"
	);
}



public
function isValidLength( $input )
{
	if( ! isset( $this->options[ 'length' ] ) )

		return true;


	if( $input->length() === $this->options( 'length' ) )

		return true;


	return false;
}








}
