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
	// Since the user can send in a mix of options concerning all levels of the class hierarchy,
	// we do not send them to the superclasses. First every class sets their default options in order
	// and afterwards we will override all with the user options.
	//
	parent::__construct( $golem );


	// Thus, $options will be empty unless this is this is the last subclass and the user
	// sets options through the constructor.
	//
	$this->setupOptions( $golem->options( 'Validation', 'StringRule' ), $options );


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

	isset( $o[ 'encoding'  ] )  &&  $o[ 'encoding'  ] = $this->validateOptionEncoding( $o[ 'encoding'  ] );
	isset( $o[ 'type'      ] )  &&  $o[ 'type'      ] = $this->validateOptionType    ( $o[ 'type'      ] );

	isset( $o[ 'length'    ] )  &&  $o[ 'length'    ] = $this->validateOptionLength( $o[ 'length'    ]              );
	isset( $o[ 'minLength' ] )  &&  $o[ 'minLength' ] = $this->validateOptionLength( $o[ 'minLength' ], 'minLength' );
	isset( $o[ 'maxLength' ] )  &&  $o[ 'maxLength' ] = $this->validateOptionLength( $o[ 'maxLength' ], 'maxLength' );

	$this->compareLengths();
}



protected
function validateOptionEncoding( $o )
{
	if( ! is_string( $o ) )

		$this->log->invalidArgumentException
		(
			"Option [encoding] should be given as a php native string. Got: " . Util::getType( $o )
		)
	;


	if( ! String::encodingSupported( $o ) )

		$this->log->invalidArgumentException( "Encoding passed in not supported by the mbstring extension: [$o]" );


	return $o;
}



protected
function validateOptionLength( $o, $param = 'length' )
{
	if( $o === 'PHP_INT_MAX' )

		$o = PHP_INT_MAX;


	// It must be a positive integer
	//
	if( ! is_int( $o ) || $o < 0 )

		$this->log->invalidArgumentException
		(
			  "Validation misconfiguration - expected positive integer $param. Got: "
			. var_export( $o, /* return = */ true )
		)
	;


	return $o;
}



protected
function compareLengths()
{
	$len = &$this->options[ 'length'    ];
	$min = &$this->options[ 'minLength' ];
	$max = &$this->options[ 'maxLength' ];


	// maxlength shouldn't be smaller than minlength
	//
	if( isset( $min )  &&  isset( $max )  &&  $max < $min )

		$this->log->invalidArgumentException
		(
			"StringRule misconfiguration - expected maxLength to be bigger than minLength [$min]. Got: $min"
		)
	;


	// length shouldn't be smaller than minlength
	//
	if( isset( $min )  &&  isset( $len )  &&  $len < $min )

		$this->log->invalidArgumentException
		(
			"StringRule misconfiguration - expected length to be bigger than minLength [$min]. Got: $len"
		)
	;


	// length shouldn't be bigger than maxlength
	//
	if( isset( $max )  &&  isset( $len )  &&  $len > $max )

		$this->log->invalidArgumentException
		(
			"StringRule misconfiguration - expected length to be smaller or equal than maxLength [$max}. Got: $len"
		)
	;
}



protected
function validateOptionType( $o )
{
	if( ! is_string( $o ) )

		$this->log->invalidArgumentException
		(
			"Option [type] should be given as a php native string. Got: " . Util::getType( $o )
		)
	;


	if( ! in_array( $o, [ 'string', 'Golem\Data\String' ] ) )

		$this->log->invalidArgumentException
		(
			"Unsupported type [$o]. Should be one of: 'string', 'Golem\Data\String'."
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
function areEqual( $a, $b )
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


	return $this->setOpt( 'encoding', $this->validateOptionEncoding( $encoding ) );
}



public
function sanitize( $input, $context )
{
	if( $this->validNull( $input ) )

		return null;


	$context = $this->annotateContext( $context         );
	$input   = parent::sanitize      ( $input, $context );

	$input   = $this->sanitizeLength ( $input, $context );

	return $this->validate( $input );
}



public
function validate( $input, $context )
{
	if( $this->validNull( $input ) )

		return null;


	$context = $this->annotateContext( $context         );
	$input   = parent::validate      ( $input, $context );

	$input   = $this->validateLength ( $input, $context );


	$outputType = isset( $this->options[ 'type' ] ) ? $this->options[ 'type' ] : $this->inputType;

	if( $outputType === 'string' )
	{
		$input = $input->raw();
		$this->inputType = null;
	}


	return $input;
}


/**
 * Length validation
 *
 */

public
function length( $length = null )
{
	// getter
	//
	if( $length === null )

		return $this->options[ 'length' ];


	// setter
	//
	$this->setOpt( 'length', $this->validateOptionLength( $length ) );
	$this->compareLengths();

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

		return $input->substr( 0, $allowedLength );


	// length is < allowedLength
	//
	if( isset( $this->options[ 'defaultValue' ] ) )

		return $this->validate( $this->options[ 'defaultValue' ], $context );


	$this->log->validationException
	(
		  "$context: No default value set and input value [$input] is shorter than allowed length: "
		. var_export( $this->options( 'length' ), /* return = */ true ) . " characters, got: $length"
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
	if
	(
		   ! isset( $this->options[ 'length' ] )
		|| $input->length() === $this->options[ 'length' ]
	)

		return true;


	return false;
}


/**
 * minLength validation
 *
 */

public
function minLength( $length = null )
{
	// getter
	//
	if( $length === null )

		return $this->options[ 'minLength' ];


	// setter
	//
	$this->setOpt( 'minLength', $this->validateOptionLength( $length, 'minLength' ) );
	$this->compareLengths();

	return $this;
}



protected
function sanitizeMinLength( $input, $context )
{
	if( $this->isValidMinLength( $input ) )

		return $input;


	// length is < minLength
	//
	if( isset( $this->options[ 'defaultValue' ] ) )

		return $this->validate( $this->options[ 'defaultValue' ], $context );


	$this->log->validationException
	(
		  "$context: No default value set and input value [$input] is shorter than allowed minLength: "
		. var_export( $this->options( 'minLength' ), /* return = */ true ) . " characters, got: {$input->length()}"
	);

}



protected
function validateMinLength( $input, $context )
{
	if( $this->isValidMinLength( $input ) )

		return $input;


	$this->log->validationException
	(
		  "$context: Input value [$input] is shorter than allowed minLength: "
		. var_export( $this->options( 'minLength' ), /* return = */ true ) . " characters, got: {$input->length()}"
	);
}



public
function isValidMinLength( $input )
{
	if
	(
		   ! isset( $this->options[ 'minLength' ] )
		|| $input->length() >= $this->options[ 'minLength' ]
	)

		return true;


	return false;
}


/**
 * maxLength validation
 *
 */

public
function maxLength( $length = null )
{
	// getter
	//
	if( $length === null )

		return $this->options[ 'maxLength' ];


	// setter
	//
	$this->setOpt( 'maxLength', $this->validateOptionLength( $length, 'maxLength' ) );
	$this->compareLengths();

	return $this;
}



protected
function sanitizeMaxLength( $input, $context )
{
	if( $this->isValidMaxLength( $input ) )

		return $input;


	// length is > maxLength
	//
	return $this->validate( $input->substr( 0, $this->options( 'maxLength' ) ), $context );
}



protected
function validateMaxLength( $input, $context )
{
	if( $this->isValidMaxLength( $input ) )

		return $input;


	$this->log->validationException
	(
		  "$context: Input value [$input] is longer than allowed maxLength: "
		. var_export( $this->options( 'maxLength' ), /* return = */ true ) . " characters, got: {$input->length()}"
	);
}



public
function isValidMaxLength( $input )
{
	if
	(
		   ! isset( $this->options[ 'maxLength' ] )
		|| $input->length() <= $this->options[ 'maxLength' ]
	)

		return true;


	return false;
}


/**
 * Type validation
 * Most of this is dealt with by BaseRule, but since we internally convert all strings to Golem\Data\String,
 * we should we make sure both native php strings and Golem Strings pass sanitation.
 *
 */


public
function sanitizeType( $input, $context )
{

	if( $this->inputType === 'string'  ||  $this->inputType === 'Golem\Data\String' )
	{
		// Prevent the validation after sanitation to throw because the inputType is not the correct one.
		//
		if( isset( $this->options[ 'type' ] ) )

			$this->inputType = $this->options[ 'type' ];


		return $input;
	}


	if( isset( $this->options[ 'defaultValue' ] ) )

		return $this->validate( $this->options[ 'defaultValue' ], $context );


	$this->log->validationException
	(
		  "$context: No default value set and input value [$input] is not of type: {$this->options['type']}, "
		. "got a: $this->inputType. for input: " . print_r( $input, /* return = */ true )
	);
}


}
