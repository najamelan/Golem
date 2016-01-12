<?php

namespace Golem;


use

	  Golem\iFace\Randomizer as iRandomizer

	, Golem\Golem
	, Golem\Traits\HasLog

	, LengthException
	, UnexpectedValueException
;



class      Randomizer
implements iRandomizer
{
use HasLog;

private $golem;

public
function __construct( Golem $golem )
{
	$this->golem = $golem;
	$this->setupLog();
}


/**
 * {@inheritDoc}
 *
 * @todo Check about encodings (utf) and strlen
 */
public
function randomString( $numChars, $charset )
{
	if( $numChars < 1 || strlen( $charset ) < 2 )

		$this->log->exception( new LengthException() );


	for( $i = 0, $rs = ''; $i < $numChars; ++$i )

		$rs .= $charset[ $this->randomInt( 0, strlen( $charset ) - 1 ) ];


	return $rs;
}



/**
 * {@inheritDoc}
 *
 * @todo check for out of bounds on integers and others.
 *
 */
public
function randomBytes( $amount, $form = 'dec' )
{
	switch( $form )
	{
		case 'hex': return bin2hex( $this->randomBytes( $amount, 'raw' ) );
		case 'dec': return hexdec ( $this->randomBytes( $amount, 'hex' ) );
		case 'bin': return decbin ( $this->randomBytes( $amount, 'dec' ) );
		case 'raw': return openssl_random_pseudo_bytes( $amount );

		default: $this->log->unexpectedValueException
		         (
		              'Wrong [$form] parameter, legal values are: "hex", "dec", bin", "raw". Got: '
		            . var_export( $form, /* return = */ true )
		         );
	}
}



/**
 * {@inheritDoc}
 */
public
function randomBool()
{
	return  boolval( $this->randomBytes( 1 ) % 2 );
}



/**
 * {@inheritDoc}
 */
public
function randomInt( $min, $max )
{
	$factor = $this->randomBytes( 4 ) / 0xFFFFFFFF;

	// floor will return a double by default, so cast to int
	//
	return intval( floor( $factor * ( $max - $min + 1 ) + $min ) );
}



/**
 * {@inheritDoc}
 */
public
function randomLong()
{
	return $this->randomBytes( 8 );
}



/**
 * {@inheritDoc}
 */
public
function randomFilename( $extension = '' )
{
	// Because PHP runs on case insensitive OS as well as case sensitive OS, only use lowercase
	//
	return

		$this->randomString( 16, 'abcdefghijklmnopqrstuvxyz0123456789' ) . $extension
	;
}



/**
 * {@inheritDoc}
 */
public
function randomFloat( $min, $max )
{
	$rf = $this->randomBytes( 4 ) / 0xFFFFFFFF;

	return $rf * ( $max - $min ) + $min;
}



/**
 * {@inheritDoc}
 */
public
function randomGUID()
{
	$guid = sprintf
	(
		  '%08x-%04x-%04x-%04x-%12x'

		, $this->randomBytes( 4 ) // 32 bits for "time_low"
		, $this->randomBytes( 2 ) // 16 bits for "time_mid"
		, $this->randomBytes( 2 ) // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
		, $this->randomBytes( 2 ) // 8 bits for "clk_seq_hi_res" and 8 bits for "clk_seq_low"
		, $this->randomBytes( 6 ) // 48 bits for "node"
	);


	// 0100 of (version) 4 for "time_hi_and_version"
	//
	$guid[ 14 ] = '4';

	// 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
	// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
	//
	$guid[ 20 ] = $this->randomString( 1, '159d' );

	return $guid;
}
}
