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
implements ValidationRule
{


private $encodingUsed = false;



public
function __construct( Golem $golem, array $options = [] )
{
	parent::__construct( $golem, $golem->options( 'Validation', 'StringRule' ), $options );
}



protected
function validateOptions()
{
	parent::validateOptions();


	if( isset( $this->options[ 'encoding' ] ) )

		$this->options[ 'encoding' ] = $this->validateOptionEncoding( $this->options[ 'encoding' ] );


	if( isset( $this->options[ 'length' ] ) )

		$this->options[ 'length' ] = $this->validateOptionLength( $this->options[ 'length' ] );
}



protected
function validateOptionEncoding( $option )
{
	if( ! String::encodingSupported( $option ) )

		$this->log->unexpectedValueException
		(
			"Encoding passed in not supported by the mbstring extension: [$option]"
		)
	;

	return $option;
}



protected
function validateOptionLength( $option )
{
	if( ! is_numeric( $option ) )

		$this->log->invalidArgumentException
		(
			  'Validation misconfiguration - length expected numeric $length. Got: '
			. var_export( $option, /* return = */ true )
		)
	;


	return (int) $option;
}



protected
function validateOptionType( $option )
{
	$option = parent::validateOptionType( $option );


	if( ! in_array( $option, [ 'string', 'Golem\Data\String' ] ) )

		$this->log->unexpectedValueException
		(
			"Unsupported type [$option]. Should be one of: 'int', 'float' or 'double'."
		)
	;


	return $option;
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

		$string->convert( $this->encoding() );


	return $string;
}



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
	if( $this->encodingUsed )

		$this->log->logicException( 'Already used encoding to interprete scalar strings, cannot change anymore' );


	$this->options[ 'encoding' ] = $this->validateOptionEncoding( $encoding );

	return $this;
}



public
function sanitize( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


	$input = parent::sanitize( $input, $context );

	$input = $this->sanitizeLength( $input, $context );

	return $this->validate( $input );
}



public
function validate( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


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
	$this->options[ 'length' ] = $this->validateOptionLength( $length );

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
