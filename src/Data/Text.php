<?php

/**
 * File class of library Golem.
 *
 */

namespace Golem\Data;

use

	  Golem\Golem
	, Golem\Util

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions
	, Golem\Traits\HasLog

	, Iterator
	, ArrayAccess
	, Countable

	, Exception

;


/**
 * Basic string funcionality.
 *
 * Object oriented strings. Provide encoding safety.
 *
 */
class      Text
implements Iterator, ArrayAccess, Countable
{

use Seal, HasOptions, HasLog;

private $golem;
private $raw  ;

// For the iterator
//
private $position = 0;


// Validators
//
private $posIntRule;



public
function __construct( Golem $golem, $content = '', array $options = [] )
{
	$this->g = $golem;


	$this->posIntRule = $this->g->numberRule()

		-> type( 'integer' )
		-> min ( 0         )
		-> seal()
	;


	$this->setupOptions( $this->g->options( 'Text' ), $options );
	$this->setupLog();

	self::ensureValidEncoding( $this->g, $this->options( 'encoding' ) );
	$this->raw               ( $content );
}



public
static
function fromUniCodePoint( Golem $golem, $codePoint, $encoding = null )
{
	$encoding = self::ensureValidEncoding
	(
		  $golem

		, $encoding === null ?

		      $golem->options( 'Text', 'encoding' )
		    : $encoding
	);



	$codePoint = $golem->numberRule()

		->type    ( 'integer' )
		->sanitize( $codePoint, 'fromUniCodePoint: parameter $codePoint' );

	;


	$utf32 = new self( $golem, pack( "N", $codePoint ), [ 'encoding' => 'UTF-32' ] );

	return $utf32->encoding( $encoding );
}



public
function copy()
{
	$c = clone $this;
	$c->sealed = false;
	return $c;
}



/**
 * Get the raw php string, or set the content of the string. When setting, if content is not valid in the
 * encoding of the Text object, it will be modified to be valid. Wrong characters will be replaced with the
 * substitue character.
 *
 * As an alias for this method you can just invoke the Text object:
 * '$myString()'              is equivalent to '$myString->raw()'
 * '$myString( 'new value' )' is equivalent to '$myString->raw( 'new value' )'
 *
 * @param string $value Optional, the new content.
 *
 * @return string|$this The raw php string if parameter $value is null, $this when setting a new value.
 *
 */
public
function raw( $value = null )
{
	// Getter
	//
	if( $value === null )

		return $this->raw;


	// Setter
	//
	if( $value instanceof self )

		$value = $value->copy()->encoding( $this->encoding() )->raw();


	if( ! self::canBeString( $value ) )

		$this->log->warning
		(
			  'Passing non string value to Text::raw(). Trying implicit cast to string. Got: '
			. var_export( $value, /* return = */ true  )
		)
	;


	$this->raw = $this->sanitizeEncoding( $value, $this->options[ 'encoding' ], $this->options[ 'encoding' ] );

	return $this;
}



public
function encoding( $toEncoding = null )
{
	// getter
	//
	if( $toEncoding === null )

		return $this->options( 'encoding' );


	// setter
	//
	if( $toEncoding === $this->encoding() )

		return $this;


	self::ensureValidEncoding( $this->g, $toEncoding );

	$oldEncoding = $this->encoding();


	return $this

		->setOpt( 'encoding', $toEncoding )
		->raw   ( $this->sanitizeEncoding( $this->raw(), $oldEncoding, $toEncoding ) )
	;
}



/**
 * Safely convert a string from one encoding to another. We cannot use TextRule to validate parameters
 * because this is used in Text construction, leading to an endless loop.
 *
 */
protected
function sanitizeEncoding( $input, $from, $to )
{
	$substitute = mb_substitute_character();

	mb_substitute_character( $this->sanitizeSubstitute() );


	// Sending wrong input to mbstring will cause a warning, and if someone turns warnings into exceptions
	// (eg. phpunit), the substitute char won't be set back unless we call finally.
	//
	try
	{
		$sane = mb_convert_encoding( $input, $to, $from );
	}


	catch( Exception $e )
	{
		$this->log->trow( $e );
	}


	finally
	{
		mb_substitute_character( $substitute );
	}


	if( mb_check_encoding( $sane, $to ) === false )

		$this->log->validationException( 'Encoding doesn\'t validate after conversion.' );


	return $sane;
}



/**
 * Makes sure the substitute character is valid in our current encoding. Mainly checks for ascii.
 * If it isn't valid ascii, it will use '?' instead.
 *
 * TODO: a general purpose algorithm which will also check encodings other than ascii.
 *
 */
protected
function sanitizeSubstitute()
{
	$sub = $this->options( 'substitute' );


	if( $this->encoding() === 'ASCII'  &&  is_numeric( $sub )  &&  $sub >= 127 )

		return ord( '?' );


	return $sub;
}



/**
 * Checks whether a certain encoding is supported by this class.
 *
 * @param  string|Golem\Data\Text $encoding The encoding to check.
 *
 * @return bool Whether it's supported
 *
 */
public
static
function encodingSupported( $encoding )
{
	if( $encoding instanceof self )

		$encoding = $encoding->encoding( mb_internal_encoding() )->raw();


	return in_array( $encoding, mb_list_encodings(), /* strict = */ true );
}



public
static
function ensureValidEncoding( Golem $golem, $encoding )
{
	if( ! self::encodingSupported( $encoding ) )

		$golem->logger( __CLASS__ )->unexpectedValueException
		(
			"Encoding passed in not supported by the mbstring extension: [$encoding]"
		)
	;


	return $encoding;
}



public
function hex( $prettify = false )
{
	$hex = bin2hex( $this->raw() );


	if( $prettify )

		$hex =  join( ' ', str_split( $hex, 2 ) );


	return $hex;
}



public
function length()
{
	return mb_strlen( $this->raw, $this->encoding() );
}



public
function count()
{
	return $this->length();
}


/**
 * Splits a string into an array of characters. See also str_split()
 *
 * @param integer $chunksize The size of the chunks in characters.
 * @param string  $raw       Whether to return raw php strings instead of Text objects (default false).
 *
 * @return array $result An array containing one element per character in the string.
 *
 */
public
function split( $chunksize = 1, $raw = false )
{
	$chunksize = $this->g->numberRule()

		-> type    ( 'integer' )
		-> min     ( 1         )

		-> validate( $chunksize, 'Split: parameter $chunksize' )
	;


	$raw = $this->g->booleanRule()->validate( $raw, 'Split: parameter $raw' );


	for( $i = 0, $result = []; $i < $this->length(); $i += $chunksize )
	{
		$ss = $this->substr( $i, $chunksize );

		$result[] = $raw ? $ss->raw() : $ss;
	}


	return $result;
}



/**
 * Pops a number of characters off the end of a string.
 *
 * @param int $amount The number of characters to pop. If amount is bigger than $this->length(), the whole string is popped.
 *
 * @return Text $result A new Text consisting of just the popped characters of the original string.
 *
 */
public
function pop( $amount = 1 )
{
	$amount = $this->g->numberRule()

		-> type    ( 'integer' )
		-> min     ( 1         )

		-> validate( $amount, 'Parameter $amount' )
	;


	$amount = min( $amount, $this->length() );
	$result = $this->substr( $this->length() - $amount, $amount );

	$this->raw( mb_substr( $this->raw, 0, $this->length() - $amount, $this->encoding() ) );

	return $result;
}



/**
 * Shifts a number of characters of the beginning of a string.
 *
 * @param int $amount The number of characters to shift.
 *
 * @return Text $result A new Text consisting of just the shifted characters of the original string.
 *
 */
public
function shift( $amount = 1 )
{
	$amount = $this->g->numberRule()

		-> type    ( 'integer' )
		-> min     ( 1         )

		-> validate( $amount, 'Parameter $amount' )
	;


	$amount = min( $amount, $this->length() );

	$result = $this->substr( 0, $amount );

	$this->raw( mb_substr( $this->raw, $amount, $this->length() - $amount, $this->encoding() ) );

	return $result;
}



/**
 * Pushes a character onto the end of a string.
 *
 * @param Text $new The string to append to the string.
 *
 * @return Text $this
 *
 */
public
function append( Text $new )
{
	$this->raw( $this->raw() . $new->copy()->encoding( $this->encoding() )->raw() );

	return $this;
}



/**
 * Pushes a character onto the end of a string.
 *
 * @param Text $new The string to append to the string.
 *
 * @return Text $this
 *
 */
public
function prepend( Text $new )
{
	$this->raw( $new->copy()->encoding( $this->encoding() )->raw() . $this->raw() );

	return $this;
}



function uniCodePoint()
{
	$utf32  = $this->copy()->encoding( 'UTF-32' );
	$result = [];


	foreach( $utf32 as $char )

		$result[] = hexdec( bin2hex( $char->raw() ) );


	return $result;
}



public
function equals( Text $input )
{
	if( $input->encoding() !== $this->encoding() )

		$input = $input->copy()->encoding( $this->encoding() );


	return $this->raw() === $input->raw();
}



/**
 * Iterator implementation
 *
 */
public
function current()
{
	return $this->offsetGet( $this->position );
}



public
function key()
{
	return $this->position;
}



public
function next()
{
	++$this->position;
}



public
function rewind()
{
	$this->position = 0;
}



public
function valid()
{
	return $this->position < $this->length();
}



/*
 * ArrayAccess Implementation
 */

/**
 * @ignore
 *
 */
public
function offsetExists( $index )
{
	$index = $this->posIntRule->validate( $index, 'Parameter $index' );

	return $index >= 0  &&  $index < $this->length();
}



/**
 * @ignore
 *
 */
public
function offsetGet( $index )
{
	$index = $this->posIntRule->copy()

		-> max     ( max( 0, $this->length() - 1 )        )
		-> validate( $index, 'offsetGet: Parameter $index' )
	;


	return $this->substr( $index, 1 );
}



/**
 * @ignore
 *
 */
public
function offsetSet( $index , $value )
{
	$index = $this->posIntRule->copy()

		-> max      ( $this->length() )
		-> allowNull( true                )

		-> validate ( $index, 'Parameter $index' )
	;


	$value = $this->g->textRule()

		-> type  ( 'Golem\Data\Text' )
		-> length( 1      )

		-> validate( $index, 'Parameter $value' )
	;



	if( is_null( $index ) || $index === $this->length() )

		$this->append( $value );


	else

		$this->splice[ $index ] = $value;
}



/**
 * @ignore
 *
 */
public function offsetUnset( $index )
{
	$index = $this->posIntRule

		-> copy()
		-> max ( max( 0, $this->length() - 1 ) )
		-> validate( $index, 'Parameter $index' )
	;


	$this->splice( $index, 1 );
}



/**
 * @ignore
 *
 */
public
function splice( $offset, $amount, Text $replacement = null )
{
	$amount = $this->posIntRule->validate( $amount, 'Parameter $amount' );

	$offset = $this->posIntRule

		-> copy()
		-> max ( $this->length() )
		-> validate( $offset, 'Parameter $offset' )
	;


	$first  = $this->substr( 0                , $offset  );
	$last   = $this->substr( $offset + $amount           );


	if( $replacement )

		$first->append( $replacement );


	return $first->append( $last );
}



/**
 * @ignore
 *
 */
public
function insert( $offset, Text $replacement )
{
	$this->splice( $offset, 0, $replacement );
}



/**
 * @ignore
 *
 */
public
function substr( $offset, $length = null )
{
	$offset = $this->posIntRule-> validate( $offset, 'Parameter $offset' );

	$length = $this->posIntRule->copy()

		-> allowNull( true            )
		-> validate( $length, 'Parameter $length' )
	;


	return

		$this

			-> copy()

			-> raw( mb_substr( $this->raw(), $offset, $length, $this->encoding() ) )
	;
}



/**
 * Tells you whether some variable can automatically be converted to a string
 * (eg. strings, numbers and objects which implement __toString)
 *
 * @param mixed $value The variable to test
 *
 * @return bool Returns TRUE if it can be used as a string or FALSE otherwise.
 *
 * @internal
 *
 */
public
static
function canBeString( $value )
{
	if
	(
		   is_string ( $value )
		|| is_numeric( $value )
		|| is_object ( $value )  &&  method_exists( $value, '__toString' )
	)
	{
		return true;
	}


	return false;
}



/**
 * Magic Methods
 */



/**
 * We convert to the configEncoding, which should be the encoding all php files are in. This will be convenient
 * for comparing to hard coded strings.
 *
 */
public
function __toString()
{
	return $this->copy()->encoding( $this->g->options( 'Golem', 'configEncoding' ) )->raw();
}



/**
 * Alias for Text::raw()
 *
 */
public
function __invoke( $raw = null )
{
	return $this->raw( $raw );
}



public
function __debugInfo()
{
	return
	[
		  'raw'      => $this->raw
		, 'position' => $this->position
		, 'sealed'   => $this->sealed ? 'true' : 'false'
		, 'options'  => $this->options
		, 'defaults' => $this->defaults
		, 'userset'  => $this->userset
	];
}


}
