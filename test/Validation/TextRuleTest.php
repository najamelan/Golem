<?php
namespace Golem\Test;

use

	  Golem\Golem

	, Golem\Data\Text

	, stdClass

;


class   TextRuleTest
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
	$rule = self::$golem->textRule();

	$this->assertEquals( 0                                            , $rule->options( 'minLength' ) );
	$this->assertEquals( PHP_INT_MAX                                  , $rule->options( 'maxLength' ) );
	$this->assertEquals( false                                        , $rule->options( 'allowNull' ) );
	$this->assertEquals( self::$golem->options( 'Text', 'encoding' ), $rule->options( 'encoding'  ) );
}



/**
 * @dataProvider validLengths
 *
 */
public
function	testValidateOptionLength( $length )
{
	$rule = self::$golem->textRule( [ 'length' => $length ] );
	$this->assertEquals( $length, $rule->length() );
}



/**
 * @dataProvider validLengths
 *
 */
public
function	testValidateOptionMinLength( $length )
{
	$rule = self::$golem->textRule( [ 'minLength' => $length ] );
	$this->assertEquals( $length, $rule->minLength() );
}



/**
 * @dataProvider validLengths
 *
 */
public
function	testValidateOptionMaxLength( $length )
{
	$rule = self::$golem->textRule( [ 'maxLength' => $length ] );
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
	$rule = self::$golem->textRule( [ 'length' => $length ] );
}



/**
 * @dataProvider      invalidLengths
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionMinLength( $length )
{
	$rule = self::$golem->textRule( [ 'minLength' => $length ] );
}



/**
 * @dataProvider      invalidLengths
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionMaxLength( $length )
{
	$rule = self::$golem->textRule( [ 'maxLength' => $length ] );
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
	$rule = self::$golem->textRule( [ 'minLength' => 3, 'maxLength' => 2 ] );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionMaxLengthSmallerThanMinLength2()
{
	$rule = self::$golem->textRule( [ 'maxLength' => 2 ] );
	$rule->minLength( 3 );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthSmallerThanMinLength()
{
	$rule = self::$golem->textRule( [ 'minLength' => 3, 'length' => 2 ] );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthSmallerThanMinLength2()
{
	$rule = self::$golem->textRule( [ 'length' => 2 ] );
	$rule->minLength( 3 );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthBiggerThanMaxLength()
{
	$rule = self::$golem->textRule( [ 'maxLength' => 3, 'length' => 4 ] );
}



/**
 * @expectedException InvalidArgumentException
 */
public
function	testOptionLengthBiggerThanMaxLength2()
{
	$rule = self::$golem->textRule( [ 'length' => 4 ] );
	$rule->maxLength( 3 );
}



public
function testTypeValidation()
{
	$rule = self::$golem->textRule( [ 'type' => 'string' ] );
	$this->assertEquals( 'string', $rule->type() );


	$rule = self::$golem->textRule( [ 'type' => 'Golem\Data\Text' ] );
	$this->assertEquals( 'Golem\Data\Text', $rule->type() );
}



/**
 * @dataProvider      invalidTypeOptions
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionType( $type )
{
	$rule = self::$golem->textRule( [ 'type' => $type ] );
}



public
function invalidTypeOptions()
{
	return
	[
		  [ -1                  ]
		, [ -PHP_INT_MAX        ]
		, [ 3.4                 ]
		, [ '12'                ]
		, [ 'integer'           ]
		, [ 'Text'            ]
		, [ 'Golem\Data\string' ]
		, [ []                  ]
		, [ new stdClass        ]
	];
}



public
function testEncodingOptionValidation()
{
	$rule = self::$golem->textRule( [ 'encoding' => 'UTF-8' ] );
	$this->assertEquals( 'UTF-8', $rule->encoding() );


	$rule = self::$golem->textRule( [ 'encoding' => 'ASCII' ] );
	$this->assertEquals( 'ASCII', $rule->encoding() );


	// Send in encoding as Golem\Data\Text
	//
	$rule = self::$golem->textRule( [ 'encoding' => self::$golem->text( 'EUC-JP', self::$cfgEnc )->encoding( 'UTF-32' ) ] );

	$this->assertEquals      ( 'EUC-JP', $rule->encoding() );
	$this->assertInternalType( 'string', $rule->encoding() );
}


/**
 * @dataProvider      invalidEncodings
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionEncoding( $type )
{
	$rule = self::$golem->textRule( [ 'type' => $type ] );
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
		, [ 'Text'            ]
		, [ 'Golem\Data\string' ]
		, [ []                  ]
		, [ new stdClass        ]
	];
}


public
function testTypeSanitation()
{
	// Ask 'string' and send in 'string'
	//
	$rule   = self::$golem->textRule( [ 'type' => 'string' ] );
	$result = $rule->sanitize( 'test', 'testType' );

	$this->assertEquals      ( 'test'  , $result       );
	$this->assertEquals      ( 'string', $rule->type() );
	$this->assertInternalType( 'string', $result       );


	// Ask Golem\Data\Text and send in string
	//
	$rule   = self::$golem->textRule( [ 'type' => 'Golem\Data\Text' ] );
	$result = $rule->sanitize( 'test', 'testType2' );

	$this->assertEquals    ( 'test'             , $result       );
	$this->assertEquals    ( 'Golem\Data\Text', $rule->type() );
	$this->assertInstanceOf( 'Golem\Data\Text', $result       );


	// Ask 'string' and send in Golem\Data\Text
	// Also send in the type parameter itself as a Golem Text
	//
	$rule   = self::$golem->textRule( [ 'type' => self::$golem->text( 'string', self::$cfgEnc ) ] );
	$result = $rule->sanitize( self::$golem->text( 'test', self::$cfgEnc ), 'testType' );

	$this->assertEquals      ( 'test'  , $result       );
	$this->assertEquals      ( 'string', $rule->type() );
	$this->assertInternalType( 'string', $result       );


	// Ask Golem\Data\Text and send in Golem\Data\Text
	// Also send in the type parameter itself as a Golem Text
	//
	$rule   = self::$golem->textRule( [ 'type' => self::$golem->text( 'Golem\Data\Text', self::$cfgEnc ) ] );
	$result = $rule->sanitize( self::$golem->text( 'test', self::$cfgEnc ), 'testType2' );

	$this->assertEquals    ( 'test'             , $result       );
	$this->assertEquals    ( 'Golem\Data\Text', $rule->type() );
	$this->assertInstanceOf( 'Golem\Data\Text', $result       );
}


public
function testSanitationTypeReuseRule()
{
	// Ask 'string' and send in 'string'
	//
	$rule   = self::$golem->textRule( [ 'type' => 'string' ] );
	$result = $rule->sanitize( 'test', 'testType' );

	$this->assertEquals      ( 'test'  , $result       );
	$this->assertEquals      ( 'string', $rule->type() );
	$this->assertInternalType( 'string', $result       );


	// Ask Golem\Data\Text and send in string
	//
	$rule->type( 'Golem\Data\Text' );
	$result = $rule->sanitize( 'test', 'testType2' );

	$this->assertEquals    ( 'test'             , $result       );
	$this->assertEquals    ( 'Golem\Data\Text', $rule->type() );
	$this->assertInstanceOf( 'Golem\Data\Text', $result       );


	// Ask 'string' and send in Golem\Data\Text
	//
	$rule->type( 'string' );
	$result = $rule->sanitize( self::$golem->text( 'test' ), 'testType' );

	$this->assertEquals      ( 'test'  , $result       );
	$this->assertEquals      ( 'string', $rule->type() );
	$this->assertInternalType( 'string', $result       );


	// Ask Golem\Data\Text and send in Golem\Data\Text
	//
	$rule->type( 'Golem\Data\Text' );
	$result = $rule->sanitize( self::$golem->text( 'test' ), 'testType2' );

	$this->assertEquals    ( 'test'             , $result       );
	$this->assertEquals    ( 'Golem\Data\Text', $rule->type() );
	$this->assertInstanceOf( 'Golem\Data\Text', $result       );
}



/**
 * @dataProvider      invalidTypes
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testInvalidTypesSanitation( $input )
{
	// Ask 'string'
	//
	$rule   = self::$golem->textRule( [ 'type' => 'string' ] );
	$result = $rule->sanitize( $input, 'testInvalidTypesSanitation' );
}



public
function invalidTypes()
{
	return
	[
		  [ -1           ]
		, [ -PHP_INT_MAX ]
		, [ 3.4          ]
		, [ []           ]
		, [ new stdClass ]
	];
}



/**
 * @dataProvider      invalidTypes
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testInvalidTypesValidation( $input )
{
	// Ask 'string'
	//
	$rule   = self::$golem->textRule( [ 'type' => 'string' ] );
	$result = $rule->validate( $input, 'testInvalidTypesValidation' );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testInvalidTypesValidationNativeString()
{
	// Ask 'string'
	//
	$rule   = self::$golem->textRule( [ 'type' => 'string' ] );
	$result = $rule->validate( self::$golem->text( 'test', self::$cfgEnc ), 'testInvalidTypesValidation' );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testInvalidTypesValidationGolemString()
{
	// Ask 'Golem\Data\Text'
	//
	$rule   = self::$golem->textRule( [ 'type' => 'Golem\Data\Text' ] );
	$result = $rule->validate( 'test', 'testInvalidTypesValidation' );
}



/**
 * Make sure exception gets thrown correctly when reusing the rule
 *
 */
private $typeValidationReuseRule;

public
function	prepareInvalidTypesValidationGolemStringReuse()
{
	// Ask 'string'
	//
	$this->typeValidationReuseRule = self::$golem->textRule( [ 'type' => 'string' ] );
	$result = $this->typeValidationReuseRule->validate( 'test', 'testInvalidTypesValidation' );
}



/**
 * @expectedException Golem\Errors\ValidationException
 * @depends           prepareInvalidTypesValidationGolemStringReuse
 *
 */
public
function	testInvalidTypesValidationGolemStringReuse()
{
	// Ask 'Golem\Data\Text'
	//
	$this->typeValidationReuseRule->type( 'Golem\Data\Text' );
	$result = $this->typeValidationReuseRule->validate( 'test', 'testInvalidTypesValidationGolemStringReuse' );
}



public
function testLengthSanitation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'length' => 0 ] );
	$result = $rule->sanitize( '', 'testLengthSanitation' );

	$this->assertEquals( '', $result );


	// send in a correct length
	//
	$rule   = self::$golem->textRule( [ 'length' => 4 ] );
	$result = $rule->sanitize( 'test', 'testLengthSanitation' );

	$this->assertEquals( 'test', $result );


	// send in unicode
	//
	$rule   = self::$golem->textRule( [ 'length' => 5 ] );
	$result = $rule->sanitize( 'ｳﾞｶｷｸ', 'testLengthSanitation' );

	$this->assertEquals( 'ｳﾞｶｷｸ', $result );


	// test truncate
	//
	$rule   = self::$golem->textRule( [ 'length' => 4 ] );
	$result = $rule->sanitize( 'tester', 'testLengthSanitation' );

	$this->assertEquals( 'test', $result );

	// test rule reuse
	//
	$rule->length( 6 );
	$result = $rule->sanitize( 'tester', 'testLengthSanitation' );

	$this->assertEquals( 'tester', $result );


	// test default value
	//
	$rule   = self::$golem->textRule( [ 'length' => 4, 'defaultValue' => 'test' ] );
	$result = $rule->sanitize( 't', 'testLengthSanitation' );

	$this->assertEquals( 'test', $result );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testLengthSanitationToShortNoDefaultValue()
{
	// test short string without default Value
	//
	$rule   = self::$golem->textRule( [ 'length' => 4 ] );
	$result = $rule->sanitize( 'te', 'testLengthSanitationToShortNoDefaultValue' );
}



public
function testLengthValidation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'length' => 0 ] );
	$result = $rule->validate( '', 'testLengthValidation' );

	$this->assertEquals( '', $result );


	// send in a correct length
	//
	$rule   = self::$golem->textRule( [ 'length' => 4 ] );
	$result = $rule->validate( 'test', 'testLengthValidation' );

	$this->assertEquals( 'test', $result );


	// test rule reuse
	//
	$rule->length( 6 );
	$result = $rule->validate( 'tester', 'testLengthValidation' );

	$this->assertEquals( 'tester', $result );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testLengthValidationToShort()
{
	// test short string without default Value
	//
	$rule   = self::$golem->textRule( [ 'length' => 4 ] );
	$result = $rule->validate( 'te', 'testLengthValidationToShort' );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testLengthValidationToLong()
{
	// test short string without default Value
	//
	$rule   = self::$golem->textRule( [ 'length' => 0 ] );
	$result = $rule->validate( 'te', 'testLengthValidationToLong' );
}



public
function testMinLengthSanitation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 0 ] );
	$result = $rule->sanitize( '', 'testMinLengthSanitation' );

	$this->assertEquals( '', $result );


	// send in a correct minLength
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 4 ] );
	$result = $rule->sanitize( 'test', 'testMinLengthSanitation' );

	$this->assertEquals( 'test', $result );


	// send in a string longer than minLength
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 4 ] );
	$result = $rule->sanitize( 'tester', 'testMinLengthSanitation' );

	$this->assertEquals( 'tester', $result );


	// test rule reuse
	//
	$rule->minLength( 6 );
	$result = $rule->sanitize( 'testers', 'testMinLengthSanitation' );

	$this->assertEquals( 'testers', $result );


	// test default value
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 4, 'defaultValue' => 'test' ] );
	$result = $rule->sanitize( 't', 'testMinLengthSanitation' );

	$this->assertEquals( 'test', $result );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testMinLengthSanitationToShortNoDefaultValue()
{
	// test short string without default Value
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 4 ] );
	$result = $rule->sanitize( 'te', 'testMinLengthSanitationToShortNoDefaultValue' );
}



public
function testMinLengthValidation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 0 ] );
	$result = $rule->validate( '', 'testMinLengthValidation' );

	$this->assertEquals( '', $result );


	// send in a correct minLength
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 4 ] );
	$result = $rule->validate( 'tester', 'testMinLengthValidation' );

	$this->assertEquals( 'tester', $result );


	// test rule reuse
	//
	$rule->minLength( 6 );
	$result = $rule->validate( 'testers', 'testMinLengthValidation' );

	$this->assertEquals( 'testers', $result );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testMinLengthValidationToShort()
{
	// test short string without default Value
	//
	$rule   = self::$golem->textRule( [ 'minLength' => 4 ] );
	$result = $rule->validate( 'te', 'testMinLengthValidationToShort' );
}



public
function testMaxLengthSanitation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'maxLength' => 0 ] );
	$result = $rule->sanitize( '', 'testMaxLengthSanitation' );

	$this->assertEquals( '', $result );


	// send in a correct maxLength
	//
	$rule   = self::$golem->textRule( [ 'maxLength' => 4 ] );
	$result = $rule->sanitize( 'test', 'testMaxLengthSanitation' );

	$this->assertEquals( 'test', $result );


	// send in a string shorter than maxLength
	//
	$rule   = self::$golem->textRule( [ 'maxLength' => 8 ] );
	$result = $rule->sanitize( 'tester', 'testMaxLengthSanitation' );

	$this->assertEquals( 'tester', $result );


	// test rule reuse
	//
	$rule->maxLength( 7 );
	$result = $rule->sanitize( 'testers', 'testMaxLengthSanitation' );

	$this->assertEquals( 'testers', $result );


	// test truncate
	//
	$rule   = self::$golem->textRule( [ 'maxLength' => 4 ] );
	$result = $rule->sanitize( 'tester', 'testMaxLengthSanitation' );

	$this->assertEquals( 'test', $result );
}



public
function testMaxLengthValidation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'maxLength' => 0 ] );
	$result = $rule->validate( '', 'testMaxLengthValidation' );

	$this->assertEquals( '', $result );


	// send in a correct maxLength
	//
	$rule   = self::$golem->textRule( [ 'maxLength' => 9 ] );
	$result = $rule->validate( 'tester', 'testMaxLengthValidation' );

	$this->assertEquals( 'tester', $result );


	// test rule reuse
	//
	$rule->maxLength( 7 );
	$result = $rule->validate( 'testers', 'testMaxLengthValidation' );

	$this->assertEquals( 'testers', $result );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testMaxLengthValidationToLong()
{
	// test short string without default Value
	//
	$rule   = self::$golem->textRule( [ 'maxLength' => 4 ] );
	$result = $rule->validate( 'tester', 'testMaxLengthValidationToShort' );
}



public
function testEncodingSanitation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'encoding' => self::$cfgEnc ] );
	$result = $rule->sanitize( self::$golem->text( '', self::$cfgEnc ), 'testEncodingSanitation' );

	$this->assertEquals( self::$cfgEnc, $result->encoding() );
	$this->assertEquals( ''           , $result()           );


	// send in a correct encoding
	//
	$rule   = self::$golem->textRule( [ 'encoding' => self::$cfgEnc ] );
	$result = $rule->sanitize( self::$golem->text( 'ｳﾞｶｷｸ', self::$cfgEnc ), 'testEncodingSanitation' );

	$this->assertEquals( self::$cfgEnc, $result->encoding() );
	$this->assertEquals( 'ｳﾞｶｷｸ'      , $result()           );


	// send in a different encoding
	//
	$rule   = self::$golem->textRule( [ 'encoding' => 'UTF-32' ] );
	$result = $rule->sanitize( self::$golem->text( 'ｳﾞｶｷｸ', self::$cfgEnc ), 'testEncodingSanitation' );

	$this->assertEquals( 'UTF-32', $result->encoding() );
	$this->assertEquals( mb_convert_encoding( 'ｳﾞｶｷｸ', 'UTF-32', self::$cfgEnc ), $result() );


	// test rule reuse
	//
	$rule->encoding( 'UTF-16BE' );
	$result = $rule->sanitize( self::$golem->text( 'ｳﾞｶｷｸ', self::$cfgEnc ), 'testEncodingSanitation' );

	$this->assertEquals( 'UTF-16BE', $result->encoding() );
	$this->assertEquals( mb_convert_encoding( 'ｳﾞｶｷｸ', 'UTF-16BE', self::$cfgEnc ), $result() );
}



public
function testEncodingValidation()
{
	// send in an empty string
	//
	$rule   = self::$golem->textRule( [ 'encoding' => self::$cfgEnc ] );
	$result = $rule->validate( '', 'testEncodingValidation' );

	$this->assertEquals( '', $result );


	// send in a correct encoding
	//
	$rule   = self::$golem->textRule( [ 'encoding' => self::$cfgEnc ] );
	$result = $rule->validate( self::$golem->text( 'ｳﾞｶｷｸ', self::$cfgEnc ), 'testEncodingValidation' );

	$this->assertEquals( 'ｳﾞｶｷｸ', $result() );


	// test rule reuse
	//
	$string =  self::$golem->text( 'ｳﾞｶｷｸ', self::$cfgEnc )->encoding( 'UTF-32' );

	$rule->encoding( 'UTF-32' );
	$result = $rule->validate( $string, 'testEncodingValidation' );

	$this->assertEquals( $string, $result );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testEncodingValidationWrong()
{
	// send in a wrong encoding
	//
	$rule   = self::$golem->textRule( [ 'encoding' => 'UTF-16' ] );
	$result = $rule->validate( self::$golem->text( 'ｳﾞｶｷｸ', self::$cfgEnc ), 'testEncodingValidationWrong' );

	$this->assertEquals( 'ｳﾞｶｷｸ', $result() );
}



/**
 * @expectedException Golem\Errors\ValidationException
 *
 */
public
function	testEncodingValidationWrongNativeString()
{
	// send in a wrong encoding
	//
	$rule   = self::$golem->textRule( [ 'encoding' => 'UTF-16' ] );
	$result = $rule->validate( 'ｳﾞｶｷｸ', 'testEncodingValidationWrongNativeString' );

	$this->assertEquals( 'ｳﾞｶｷｸ', $result );
}


}
