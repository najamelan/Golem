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
class      String
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
	$this->golem = $golem;


	$this->posIntRule = $this->golem->validator()->number()

		-> type( 'integer' )
		-> min ( 0         )
		-> seal()
	;


	$this->setupOptions( $this->golem->options( 'String' ), $options );
	$this->setupLog();

	self::ensureValidEncoding( $this->golem, $this->options( 'encoding' ) );
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

		      $golem->options( 'String', 'encoding' )
		    : $encoding
	);



	$codePoint = $golem->validator()->number()

		->type    ( 'integer'  )
		->sanitize( $codePoint, 'fromUniCodePoint: parameter $codePoint' );

	;


	$utf32 = new self( $golem, pack( "N", $codePoint ), [ 'encoding' => 'UTF-32' ] );

	return $utf32->encoding( $encoding );
}



public
function copy()
{
	return clone $this;
}



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


	if( ! Util::canBeString( $value ) )

		$this->log->warning
		(
			  'Passing non string value to String::raw(). Trying implicit cast to string. Got: '
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


	self::ensureValidEncoding( $this->golem, $toEncoding );

	$oldEncoding                 = $this->encoding();
	$this->options[ 'encoding' ] = $toEncoding;

	$this->raw( $this->sanitizeEncoding( $this->raw(), $oldEncoding, $toEncoding ) );

	return $this;
}



/**
 * Safely convert a string from one encoding to another. We cannot use StringRule to validate parameters
 * because this is used in String construction, leading to an endless loop.
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



public
static
function encodingSupported( $encoding )
{
	return in_array( $encoding, mb_list_encodings(), /* strict = */ true );
}



public
static
function ensureValidEncoding( Golem $golem, $encoding )
{
	if( ! self::encodingSupported( $encoding ) )

		$golem->log->unexpectedValueException
		(
			"Encoding passed in not supported by the mbstring extension: [$encoding]"
		)
	;


	return $encoding;
}



/**
 * We convert to the configEncoding, which should be the encoding all php files are in.
 *
 */
public
function __toString()
{
	return $this->copy()->encoding( $this->golem->options( 'Golem', 'configEncoding' ) )->raw();
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
 * @param string  $raw       Whether to return raw php strings instead of String objects (default false).
 *
 * @return array $result An array containing one element per character in the string.
 *
 */
public
function split( $chunksize = 1, $raw = false )
{
	$chunksize = $this->golem->validator()->number()

		-> type    ( 'integer' )
		-> min     ( 1         )

		-> validate( $chunksize, 'Split: parameter $chunksize' )
	;


	$raw = $this->golem->validator()->boolean()->validate( $raw, 'Parameter $raw' );


	for( $i = 0, $result = []; $i < $this->length(); $i += $chunksize )

		$result[] = $raw ?

			                          mb_substr( $this->raw, $i, $chunksize, $this->encoding() )
			: new self( $this->golem, mb_substr( $this->raw, $i, $chunksize, $this->encoding() ), $this->options() )
		;


	return $result;
}



/**
 * Pops a number of characters off the end of a string.
 *
 * @param int $amount The number of characters to pop.
 *
 * @return String $result A new String consisting of just the popped characters of the original string.
 *
 */
public
function pop( $amount = 1 )
{
	$amount = $this->golem->validator()->number()

		-> type    ( 'integer' )
		-> min     ( 1         )

		-> validate( $amount, 'Parameter $amount' )
	;


	$amount = min( $amount, $this->length() );

	$result = mb_substr( $this->raw, $this->length() - $amount, $amount, $this->encoding() );

	$this->raw( mb_substr( $this->raw, 0, $this->length() - $amount, $this->encoding() ) );

	return new String( $this->golem, $result, $this->options() );
}



/**
 * Shifts a number of characters of the beginning of a string.
 *
 * @param int $amount The number of characters to shift.
 *
 * @return String $result A new String consisting of just the shifted characters of the original string.
 *
 */
public
function shift( $amount = 1 )
{
	$amount = $this->golem->validator()->number()

		-> type    ( 'integer' )
		-> min     ( 1         )

		-> validate( $amount, 'Parameter $amount' )
	;


	$amount = min( $amount, $this->length() );

	$result = mb_substr( $this->raw, 0, $amount, $this->encoding() );

	$this->raw( mb_substr( $this->raw, $amount, $this->length() - $amount, $this->encoding() ) );

	return new String( $this->golem, $result, $this->options() );
}



/**
 * Pushes a character onto the end of a string.
 *
 * @param String $new The string to append to the string.
 *
 * @return String $this
 *
 */
public
function append( String $new )
{
	$this->raw( $this->raw() . $new->copy()->encoding( $this->encoding() )->raw() );

	return $this;
}



/**
 * Pushes a character onto the end of a string.
 *
 * @param String $new The string to append to the string.
 *
 * @return String $this
 *
 */
public
function prepend( String $new )
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
function equals( String $input )
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
		-> validate( $index, 'Parameter $index' )
	;


	$raw = mb_substr( $this->raw(), $index, 1, $this->encoding() );

	return new self( $this->golem, $raw, [ 'encoding' => $this->encoding() ] );
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


	$value = $this->golem->validator()->string()

		-> type  ( 'Golem\Data\String' )
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
function splice( $offset, $amount, String $replacement = null )
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
function insert( $offset, String $replacement )
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

}
