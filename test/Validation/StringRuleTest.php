<?php
namespace Golem\Test;

use

	  Golem\Golem

	, Golem\Data\String

	, stdClass

;


class   StringRuleTest
extends \PHPUnit_Framework_TestCase
{

private static $golem ;
private static $cfgEnc;
private static $enc   ;


public
static
function setUpBeforeClass()
{
	self::$golem  = new Golem;
	self::$cfgEnc = self::$golem->options( 'Golem', 'configEncoding' );
	self::$enc    = [ 'encoding' => self::$cfgEnc ];
}



/**
 *
 *
 */
public
function	testConstructor()
{
	$rule = self::$golem->validator()->string();

	$this->assertEquals( 0                                            , $rule->options( 'minLength' ) );
	$this->assertEquals( PHP_INT_MAX                                  , $rule->options( 'maxLength' ) );
	$this->assertEquals( false                                        , $rule->options( 'allowNull' ) );
	$this->assertEquals( self::$golem->options( 'String', 'encoding' ), $rule->options( 'encoding'  ) );
}



/**
 * @dataProvider validLengths
 *
 */
public
function	testValidateOptionLength( $length )
{
	$rule = self::$golem->validator()->string( [ 'length' => $length ] );
	$this->assertEquals( $length, $rule->length() );
}



/**
 * @dataProvider validLengths
 *
 */
public
function	testValidateOptionMinLength( $length )
{
	$rule = self::$golem->validator()->string( [ 'minLength' => $length ] );
	$this->assertEquals( $length, $rule->minLength() );
}



/**
 * @dataProvider validLengths
 *
 */
public
function	testValidateOptionMaxLength( $length )
{
	$rule = self::$golem->validator()->string( [ 'maxLength' => $length ] );
	$this->assertEquals( $length, $rule->maxLength() );
}


public
function validLengths()
{
	return
	[
		  [ 0           ]
		, [ 1           ]
		, [ PHP_INT_MAX ]
		, [ 043         ]
		, [ 0xa3        ]

	];
}



/**
 * @dataProvider      invalidLengths
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionLength( $length )
{
	$rule = self::$golem->validator()->string( [ 'length' => $length ] );
}



/**
 * @dataProvider      invalidLengths
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionMinLength( $length )
{
	$rule = self::$golem->validator()->string( [ 'minLength' => $length ] );
}



/**
 * @dataProvider      invalidLengths
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionMaxLength( $length )
{
	$rule = self::$golem->validator()->string( [ 'maxLength' => $length ] );
}



public
function invalidLengths()
{
	return
	[
		  [ -1           ]
		, [ -PHP_INT_MAX ]
		, [ 3.4          ]
		, [ '12'         ]
		, [ true         ]
		, [ []           ]
		, [ new stdClass ]
	];
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionMaxLengthSmallerThanMinLength()
{
	$rule = self::$golem->validator()->string( [ 'minLength' => 3, 'maxLength' => 2 ] );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionMaxLengthSmallerThanMinLength2()
{
	$rule = self::$golem->validator()->string( [ 'maxLength' => 2 ] );
	$rule->minLength( 3 );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthSmallerThanMinLength()
{
	$rule = self::$golem->validator()->string( [ 'minLength' => 3, 'length' => 2 ] );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthSmallerThanMinLength2()
{
	$rule = self::$golem->validator()->string( [ 'length' => 2 ] );
	$rule->minLength( 3 );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthBiggerThanMaxLength()
{
	$rule = self::$golem->validator()->string( [ 'maxLength' => 3, 'length' => 4 ] );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthBiggerThanMaxLength2()
{
	$rule = self::$golem->validator()->string( [ 'length' => 4 ] );
	$rule->maxLength( 3 );
}



public
function testTypeValidation()
{
	$rule = self::$golem->validator()->string( [ 'type' => 'string' ] );
	$this->assertEquals( 'string', $rule->type() );


	$rule = self::$golem->validator()->string( [ 'type' => 'Golem\Data\String' ] );
	$this->assertEquals( 'Golem\Data\String', $rule->type() );
}



/**
 * @dataProvider      invalidLengths
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionType( $type )
{
	$rule = self::$golem->validator()->string( [ 'type' => $type ] );
}



public
function invalidTypes()
{
	return
	[
		  [ -1                  ]
		, [ -PHP_INT_MAX        ]
		, [ 3.4                 ]
		, [ '12'                ]
		, [ 'integer'           ]
		, [ 'String'            ]
		, [ 'Golem\Data\string' ]
		, [ []                  ]
		, [ new stdClass        ]
	];
}



public
function testEncodingValidation()
{
	$rule = self::$golem->validator()->string( [ 'encoding' => 'UTF-8' ] );
	$this->assertEquals( 'UTF-8', $rule->encoding() );


	$rule = self::$golem->validator()->string( [ 'encoding' => 'ASCII' ] );
	$this->assertEquals( 'ASCII', $rule->encoding() );
}


/**
 * @dataProvider      invalidEncodings
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionEncoding( $type )
{
	$rule = self::$golem->validator()->string( [ 'type' => $type ] );
}



public
function invalidEncodings()
{
	return
	[
		  [ -1                  ]
		, [ -PHP_INT_MAX        ]
		, [ 3.4                 ]
		, [ '12'                ]
		, [ 'integer'           ]
		, [ 'String'            ]
		, [ 'Golem\Data\string' ]
		, [ []                  ]
		, [ new stdClass        ]
	];
}

}
