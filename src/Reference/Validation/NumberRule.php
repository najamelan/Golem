<?php
/**
 *
 */



namespace Golem\Reference\Validation;

use

	  Golem\Golem

	, Golem\iFace\ValidationRule

	, Golem\Reference\Data\String

	, Golem\Reference\Validation\BaseRule

	, Golem\Reference\Util
;

/**
 * The basic string rule.
 *
 */
class      NumberRule
extends    BaseRule
implements ValidationRule
{


private $encodingUsed = false;



public
function __construct( Golem $golem, array $options = [] )
{
	parent::__construct( $golem, $golem->options( 'Validation', 'NumberRule' ), $options );
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
function validateOptionType( $option )
{
	$option = parent::validateOptionType( $option );


	if( ! in_array( $option, [ 'integer', 'float', 'double' ] ) )

		$this->log->unexpectedValueException
		(
			"Unsupported type [$option]. Should be one of: 'integer', 'float' or 'double'."
		)
	;


	return $option;
}



public
function ensureType( $number )
{

	if( ! is_numeric( $number ) )

		$this->log->validationException( "Cannot turn [$number] into a number." );


	$type = $this->options[ 'type' ];


	switch( $type )
	{
		case 'integer'   : $number = (int)    $number;
		               break;

		case 'float' : $number = (float)  $number;
		               break;

		case null    :
		case 'double': $number = (double) $number;
		               break;

		default      : $this->log->unexpectedValueException( "Unsupported type [$type]. Should be one of: 'integer', 'float' or 'double'." );
	}


	return $number;
}



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


	$input = parent::sanitize( $input, $context );

	return $this->validate( $input );
}



public
function validate( $input, $context )
{
	if( isset( $this->options[ 'allowNull' ] )  &&  $this->options[ 'allowNull' ] === true  &&  $input === null )

		return null;


	$input = parent::validate( $input, $context );

	return $input;
}


}
