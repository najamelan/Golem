<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Data\String

	, stdClass

;


class   StringTest
extends \PHPUnit_Framework_TestCase
{
private static $golem;
private static $cfgEnc;
private static $enc;


public
static
function setUpBeforeClass()
{
	self::$golem  = new Golem;
	self::$cfgEnc = self::$golem->options( 'Golem', 'configEncoding' );
	self::$enc    = [ 'encoding' => self::$cfgEnc ];
}



public
function	testConstructor()
{
	// Test Parameter validation (type and content)


	// Create empty string
	//
	$s = new String( self::$golem, '', self::$enc );

	$this->assertEquals( ''           , $s->raw     () );
	$this->assertEquals( self::$cfgEnc, $s->encoding() );


	// Create valid string
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );

	$this->assertEquals( 'κόσμε'      , $s->raw     () );
	$this->assertEquals( self::$cfgEnc, $s->encoding() );


	// Send in invalidly encoded string
	//
	$golemSub = self::$golem->options( 'String', 'substitute' );


	// A byte sequence which is not valid UTF-32
	//
	$s = new String( self::$golem, 'hiii', [ 'encoding' => 'UTF-32' ] );

	$uCP = $s->uniCodePoint();
	$this->assertEquals( 'UTF-32'  , $s->encoding() );
	$this->assertEquals(  1        , count( $uCP )  );
	$this->assertEquals(  $golemSub, $uCP[0]        );


	// Invalid utf-8 data
	//
	$s = new String( self::$golem, file_get_contents( __DIR__ . '/../TestData/UTF-8-test.txt' ), [ 'encoding' => 'UTF-8' ] );

	$this->assertEquals( file_get_contents( __DIR__ . '/../TestData/UTF-8-test-processed.txt' ), $s->raw()        );



	// Send in non string values
	//
	// Number
	//
	$s = new String( self::$golem, 53, self::$enc );

	$this->assertEquals( '53'      , $s->raw     () );
	$this->assertEquals( self::$cfgEnc, $s->encoding() );


	// null
	//
	$s = new String( self::$golem, null, self::$enc );

	$this->assertEquals( null      , $s->raw     () );
	$this->assertEquals( self::$cfgEnc, $s->encoding() );


	// boolean
	//
	$s = new String( self::$golem, true, self::$enc );

	$this->assertEquals( 1            , $s->raw     () );
	$this->assertEquals( self::$cfgEnc, $s->encoding() );


	// Send in a String Object
	//
	$s = new String( self::$golem, new String( self::$golem, 'testｶｷｸ', self::$enc ), self::$enc );

	$this->assertEquals( 'testｶｷｸ'    , $s->raw     () );
	$this->assertEquals( self::$cfgEnc, $s->encoding() );
}



/**
 * @expectedException PHPUnit_Framework_Error
 */
public
function	testContructorParamArray()
{
	$s = new String( self::$golem, [], self::$enc );
}



/**
 * @expectedException PHPUnit_Framework_Error
 */
public
function	testContructorParamObj()
{
	$s = new String( self::$golem, new stdClass, self::$enc );
}



public
function	testFromUniCodePoint()
{
	$g   = self::$golem;
	$enc = $g->options( 'Golem', 'configEncoding' );

	// Make sure fromUniCodePoint->uniCodePoint() returns the same number as we sent in

	// nul byte
	//
	$cp = 0;
	$s  = String::fromUniCodePoint( $g, $cp );
	$this->assertEquals( $cp, $s->uniCodePoint()[ 0 ] );
	$this->assertEquals( "\0", $s->raw() );


	// U+10330 gothic letter ahsa
	//
	$cp = 0x10330;
	$s  = String::fromUniCodePoint( $g, $cp );
	$this->assertEquals( $cp, $s->uniCodePoint()[ 0 ] );
	$this->assertEquals( '𐌰', $s->raw() );


	// U+20FFFF Point beyond unicode standard
	//
	$cp = 0x20FFFF;
	$s  = String::fromUniCodePoint( $g, $cp );
	$this->assertEquals( $g->options( 'String', 'substitute' ), $s->uniCodePoint()[ 0 ] );


	// U+10330 gothic letter ahsa, try to get ascii
	//
	$cp = 0x10330;
	$s  = String::fromUniCodePoint( $g, $cp, 'ASCII' );
	$this->assertEquals( '?', $s->raw() );


	$this->markTestIncomplete();
}



public
function	testCopy()
{
	// Verify copy returns a different object
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$sc = $s->copy();

	$this->assertNotSame( $s, $sc );
	$this->assertEquals ( $s->raw(), $sc->raw() );


	// Verify options are different
	//
	$s->encoding( 'UTF-32' );
	$this->assertNotEquals( $s->encoding(), $sc->encoding() );


	// Verify returned copy is not sealed
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$s->seal();

	$sc = $s->copy();
	$this->assertFalse( $sc->sealed(), 'Verify String copy isn\'t sealed.' );

	$this->markTestIncomplete();
}



public
function	testRaw()
{
	// Test getter functionality (is_string)
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$this->assertTrue( is_string( $s->raw() ) );


	// Test on empty strings
	//
	$s = new String( self::$golem, '', self::$enc );
	$this->assertTrue( is_string( $s->raw() ) );


	// Test setter functionality
	// Test Parameter validation (type and content)
	// Should return $this, not a new string object
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$r = new String( self::$golem, 'ｶｷｸ'  , self::$enc );

	$this->assertEquals( 'ｶｷｸ'  , $s->raw( $r      )->raw() );
	$this->assertEquals( 'κόσμε', $s->raw( 'κόσμε' )->raw() );
	$this->assertEquals( '54'   , $s->raw( 54      )->raw() );

	$this->assertSame  ( $s, $s->raw( 'olé' ) );

	$this->markTestIncomplete();
}



public
function	testEncoding()
{
	// Test it returns a the same encoding as set in constructor
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$this->assertEquals( self::$cfgEnc, $s->encoding() );


	// Test it returns a valid encoding on an empty string
	//
	$s = new String( self::$golem, '', [ 'encoding' => 'UTF-8' ] );
	$this->assertEquals( 'UTF-8', $s->encoding() );


	// Getter functionality

	// Test Parameter validation (type and content)
	//

	// Check encoding gets set to the new encoding
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$this->assertFalse( mb_check_encoding( $s->raw(), 'UTF-16' ) );

	$s->encoding( 'UTF-16' );

	$this->assertEquals( 'UTF-16'                    , $s->encoding(            ) );
	$this->assertEquals( 'UTF-16'                    , $s->options ( 'encoding' ) );
	$this->assertEquals( 'UTF-16'                    , $s->userset ( 'encoding' ) );
	$this->assertTrue  ( mb_check_encoding( $s->raw(), 'UTF-16'                 ) );


	// Make sure conversion is in place (no new String object should be created)
	//
	$this->assertSame( $s, $s->encoding( 'UTF-8' ) );


	// Test return types
	//
	$this->assertInternalType( 'string'           , $s->encoding()           );
	$this->assertInstanceOf  ( 'Golem\Data\String', $s->encoding( 'UTF-32' ) );


	// Test actual conversion

	$this->markTestIncomplete();
}



/**
 * @expectedException RuntimeException
 */
public
function testSetEncodingOnSealed()
{
	// Test different chunk sizes (0)
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$s->seal();

	$s->encoding( 'UTF-32' );
}



public
function	testHex()
{
	// Test hex values for empty string, and some different encodings
	//
	$s = new String( self::$golem, '', self::$enc );
	$this->assertEquals( '', $s->hex() );


	// Test valid strings
	//
	$s = new String( self::$golem, "\x0", self::$enc );
	$this->assertEquals( '00', $s->hex() );

	$s = new String( self::$golem, "\x58", self::$enc );
	$this->assertEquals( '58', $s->hex() );

	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$this->assertEquals( 'cebae1bdb9cf83cebcceb5', $s->hex() );


	// Test prettify
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$this->assertEquals( 'ce ba e1 bd b9 cf 83 ce bc ce b5', $s->hex( true ) );
}



public
function	testLength()
{
	// Test empty string
	//
	$s = new String( self::$golem, '', self::$enc );
	$this->assertEquals( 0, $s->length() );


	// Test a utf-8 string
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$this->assertEquals( 5, $s->length() );


	// Test different encodings
	//
	$s->encoding( 'UTF-7' );
	$this->assertEquals( 5, $s->length() );
}



public
function	testSplit()
{
	// Test Parameter validation (type and content)

	// Test empty string
	//
	$s = new String( self::$golem, '', self::$enc );
	$this->assertEquals( [], $s->split() );


	// Test simple string (raw)
	//
	$control = [ 'κ', 'ό', 'σ', 'μ', 'ε' ];
	$s       = new String( self::$golem, 'κόσμε', self::$enc );

	$this->assertEquals( $control, $s->split( 1, true ) );


	// Test simple string
	//
	$s     = new String( self::$golem, 'κόσμε', self::$enc );
	$split = $s->split();

	for( $i = 0; $i < count( $split ); ++$i )
	{
		$this->assertEquals( $control[ $i ], $split[ $i ]->raw() );
		$this->assertEquals( $s->options(), $split[ $i ]->options() );
	}


	// Test different chunk sizes (2)
	//
	$control = [ 'κό', 'σμ', 'ε' ];
	$s       = new String( self::$golem, 'κόσμε', self::$enc );

	$this->assertEquals( $control, $s->split( 2, true ) );


	// Test different chunk sizes (string length)
	//
	$control = [ 'κόσμε' ];
	$s       = new String( self::$golem, 'κόσμε', self::$enc );

	$this->assertEquals( $control, $s->split( 5, true ) );


	// Test different encodings

	$this->markTestIncomplete();
}



/**
 * @expectedException Golem\Errors\ValidationException
 */
public
function testSplitInvalidParam()
{
	// Test different chunk sizes (0)
	//
	$s = new String( self::$golem, 'κόσμε', self::$enc );
	$s->split( 0 );
}



public
function	testUniCodePoint()
{
	// Test empty string
	//
	$s = new String( self::$golem, '', self::$enc );
	$cp = $s->uniCodePoint();

	$this->assertInternalType( 'array', $cp          );
	$this->assertEquals      ( 0      , count( $cp ) );


	// Test utf-8 character
	//
	$s = new String( self::$golem, 'ê', self::$enc );
	$cp = $s->uniCodePoint();

	$this->assertInternalType( 'array', $cp          );
	$this->assertEquals      ( 1      , count( $cp ) );
	$this->assertEquals      ( 234    , $cp[ 0 ]     );


	// Test some different encodings
	// Test illegal encodings

	$this->markTestIncomplete();
}



public
function	testPop()
{
	$this->markTestIncomplete();

	// Test Parameter validation (type and content)
	// Test right amount of characters popped
	// Result type should be a new String object
	// Popped characters should be removed from original string
}



public
function	testShift()
{
	$this->markTestIncomplete();

	// Test Parameter validation (type and content)
	// Test right amount of characters shifted
	// Result type should be a new String object
	// Shifted characters should be removed from original string
}



public
function	testAppend()
{
	// Should return $this, not a new string object


	// Add to empty string
	//
	$s  = new String( self::$golem, ''   , self::$enc );
	$a  = new String( self::$golem, 'def', self::$enc );

	$t = $s->append( $a );

	$this->assertEquals( 'def', $s->raw() );
	$this->assertEquals( 'def', $a->raw() );
	$this->assertTrue  ( $s === $t        );


	// Add empty string
	//
	$s  = new String( self::$golem, 'abc', self::$enc );
	$a  = new String( self::$golem, '', self::$enc );

	$t = $s->append( $a );

	$this->assertEquals( 'abc', $s->raw() );
	$this->assertEquals( ''   , $a->raw() );
	$this->assertTrue  ( $s === $t           );


	// Standard usage
	//
	$s  = new String( self::$golem, 'abc', self::$enc );
	$a  = new String( self::$golem, 'def', self::$enc );

	$t = $s->append( $a );

	$this->assertEquals( 'abcdef', $s->raw() );
	$this->assertEquals( 'def'   , $a->raw() );
	$this->assertTrue  ( $s === $t           );


	// Combine unicode with ascii
	//
	$s  = new String( self::$golem, 'abc', [ 'encoding' => 'ASCII' ] );
	$a  = new String( self::$golem, 'ｶｷｸ', self::$enc                );

	$t = $s->append( $a );

	$this->assertEquals( 'ｶｷｸ'   , $a->raw     (         )        );
	$this->assertEquals( 'UTF-8' , $a->encoding(         )        );
	$this->assertEquals( '???'   , $a->encoding( 'ASCII' )->raw() );
	$this->assertEquals( 'abc???', $s->raw     (         )        );
	$this->assertTrue  ( $s === $t                                );


	// Combine UTF-8 with UTF-32
	// Kind of assumes that the config encoding is UTF-8 because we compare to hardcoded values
	//
	$s  = new String( self::$golem, 'abc', self::$enc );
	$a  = new String( self::$golem, 'ｶｷｸ', self::$enc );

	$s->encoding( 'UTF-8'  );
	$a->encoding( 'UTF-32' );
	$t = $s->append( $a );

	$this->assertEquals( 'abcｶｷｸ', $s ->raw    (               )         );
	$this->assertEquals( 'ｶｷｸ'   , $a->encoding( self::$cfgEnc )->raw()  );
	$this->assertTrue  ( $s === $t                                       );


	// Test Parameter validation (type and content)
	// Test appending strings with different encodings

	$this->markTestIncomplete();
}



public
function	testPrepend()
{
	// Should return $this, not a new string object


	// Add to empty string
	//
	$s  = new String( self::$golem, ''   , self::$enc );
	$a  = new String( self::$golem, 'def', self::$enc );

	$t = $s->prepend( $a );

	$this->assertEquals( 'def', $s->raw() );
	$this->assertEquals( 'def', $a->raw() );
	$this->assertTrue  ( $s === $t        );


	// Add empty string
	//
	$s  = new String( self::$golem, 'abc', self::$enc );
	$a  = new String( self::$golem, '', self::$enc );

	$t = $s->prepend( $a );

	$this->assertEquals( 'abc', $s->raw() );
	$this->assertEquals( ''   , $a->raw() );
	$this->assertTrue  ( $s === $t           );


	// Standard usage
	//
	$s  = new String( self::$golem, 'abc', self::$enc );
	$a  = new String( self::$golem, 'def', self::$enc );

	$t = $s->prepend( $a );

	$this->assertEquals( 'defabc', $s->raw() );
	$this->assertEquals( 'def'   , $a->raw() );
	$this->assertTrue  ( $s === $t           );


	// Combine unicode with ascii
	//
	$s  = new String( self::$golem, 'abc', [ 'encoding' => 'ASCII' ] );
	$a  = new String( self::$golem, 'ｶｷｸ', self::$enc                );

	$t = $s->prepend( $a );

	$this->assertEquals( '???abc', $s->raw() );
	$this->assertEquals( 'ｶｷｸ'   , $a->raw() );
	$this->assertTrue  ( $s === $t           );


	// Combine UTF-8 with UTF-32
	// Kind of assumes that the config encoding is UTF-8 because we compare to hardcoded values
	//
	$s  = new String( self::$golem, 'abc', self::$enc );
	$a  = new String( self::$golem, 'ｶｷｸ', self::$enc );

	$s->encoding( 'UTF-8'  );
	$a->encoding( 'UTF-32' );
	$t = $s->prepend( $a );

	$this->assertEquals( 'ｶｷｸabc', $s ->raw()                           );
	$this->assertEquals( 'ｶｷｸ'   , $a->encoding( self::$cfgEnc )->raw() );
	$this->assertTrue  ( $s === $t                                      );


	// Test Parameter validation (type and content)
	// Test appending strings with different encodings

	$this->markTestIncomplete();
}



public
function	testCurrent()
{
	$this->markTestIncomplete();
}



public
function	testKey()
{
	$this->markTestIncomplete();
}



public
function	testNext()
{
	$this->markTestIncomplete();
}



public
function	testRewind()
{
	$this->markTestIncomplete();
}



public
function	testValid()
{
	$this->markTestIncomplete();
}



public
function	testOffsetExists()
{
	$this->markTestIncomplete();
}



public
function	testOffsetGet()
{
	$this->markTestIncomplete();
}



public
function	testOffsetSet()
{
	$this->markTestIncomplete();
}



public
function	testOffsetUnset()
{
	$this->markTestIncomplete();
}



public
function	testSubstr()
{
	// Make sure that return value is a new String object
	// $this->assertFalse ( $s === $ss     );


	// Create empty string
	//
	$s  = new String( self::$golem, '', self::$enc );
	$ss = $s->substr( 0, 0 );

	$this->assertEquals( '', $s ->raw() );
	$this->assertEquals( '', $ss->raw() );
	$this->assertFalse ( $s === $ss     );


	// Test $offset only, should grab until end of string (from beginning)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 0 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( 'κόσμε', $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $offset only, should grab until end of string (from middle)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 2 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( 'σμε'  , $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $offset only, should grab until end of string (from end)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 5 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( ''     , $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $length (0)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 0, 0 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( ''     , $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $length (0)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 2, 0 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( ''     , $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $length (1)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 0, 1 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( 'κ'    , $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $length (1)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 2, 1 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( 'σ'    , $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $length (1)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 5, 1 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( ''     , $ss->raw() );
	$this->assertFalse ( $s === $ss          );


	// Test $length (3)
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$ss = $s->substr( 2, 3 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( 'σμε'  , $ss->raw() );
	$this->assertFalse ( $s === $ss          );



	// Test Parameter validation (type and content)

	$this->markTestIncomplete();
}



public
function	testSplice()
{

	// Deleting characters
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$s1 = $s->splice( 0, 1 );
	$s2 = $s->splice( 0, 2 );
	$s3 = $s->splice( 2, 1 );
	$s4 = $s->splice( 2, 2 );

	$this->assertEquals( 'κόσμε', $s ->raw() );
	$this->assertEquals( 'όσμε' , $s1->raw() );
	$this->assertEquals( 'σμε'  , $s2->raw() );
	$this->assertEquals( 'κόμε' , $s3->raw() );
	$this->assertEquals( 'κόε'  , $s4->raw() );
	$this->assertFalse ( $s === $s1          );


	// Inserting characters
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$i  = new String( self::$golem, 'ｶｷｸ'  , self::$enc );
	$s1 = $s->splice( 0, 0, $i );
	$s2 = $s->splice( 2, 0, $i );
	$s3 = $s->splice( 5, 0, $i );

	$this->assertEquals( 'κόσμε'   , $s ->raw() );
	$this->assertEquals( 'ｶｷｸ'     , $i ->raw() );
	$this->assertEquals( 'ｶｷｸκόσμε', $s1->raw() );
	$this->assertEquals( 'κόｶｷｸσμε', $s2->raw() );
	$this->assertEquals( 'κόσμεｶｷｸ', $s3->raw() );
	$this->assertFalse ( $s === $s1             );


	// Inserting and deleting characters
	//
	$s  = new String( self::$golem, 'κόσμε', self::$enc );
	$i  = new String( self::$golem, 'ｶｷｸ'  , self::$enc );
	$s1 = $s->splice( 0, 1, $i );
	$s2 = $s->splice( 2, 1, $i );
	$s3 = $s->splice( 2, 3, $i );
	$s4 = $s->splice( 5, 1, $i );

	$this->assertEquals( 'κόσμε'   , $s ->raw() );
	$this->assertEquals( 'ｶｷｸ'     , $i ->raw() );
	$this->assertEquals( 'ｶｷｸόσμε' , $s1->raw() );
	$this->assertEquals( 'κόｶｷｸμε' , $s2->raw() );
	$this->assertEquals( 'κόｶｷｸ'   , $s3->raw() );
	$this->assertEquals( 'κόσμεｶｷｸ', $s4->raw() );
	$this->assertFalse ( $s === $s1             );



	// Test Parameter validation (type and content)

	$this->markTestIncomplete();
}
}
