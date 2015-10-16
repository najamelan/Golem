<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Data\String

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

	$this->markTestIncomplete();
}



public
function	testFromUniCodePoint()
{

	$this->markTestIncomplete();
}



public
function	testEncoding()
{
	// Test it returns a the same encoding as set in constructor
	//


	// Test it returns a valid mbstring encoding on a valid string
	// Test it returns a valid encoding on an empty string
	$this->markTestIncomplete();
}



public
function	testConvert()
{
	$this->markTestIncomplete();

	// Test Parameter validation (type and content)
	// Check encoding gets set to the new encoding
	// Make sure conversion is in place (no new String object should be created)
	// Test actual conversion
}



public
function	testRaw()
{
	$this->markTestIncomplete();

	// Test Parameter validation (type and content)
	// Test getter functionality (is_string)
	// Test on empty strings

	// Test setter functionality
	// Test sanitation
	// Should return $this, not a new string object
}



public
function	testCopy()
{
	$this->markTestIncomplete();

	// Return type should be a new object
	// Test all properties are of the correct type (reference or copies)
}



public
function	testHex()
{
	$this->markTestIncomplete();

	// Test Parameter validation (type and content)
	// Test hex values for empty string, and some different encodings
	// Test prettify
	// Return type should be is_string in all cases
}



public
function	testLength()
{
	$this->markTestIncomplete();

	// Test empty string
	// Test different encodings
	// return type should be positive is_int
}



public
function	testSplit()
{
	$this->markTestIncomplete();

	// Test Parameter validation (type and content)
	// Return type should be array
	// Test empty string
	// Test different encodings
	// Test different chunk sizes
}



public
function	testUniCodePoint()
{
	$this->markTestIncomplete();

	// Result type should be array
	// Test empty string
	// Test some different encodings
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

	$this->assertEquals( 'abc???', $s ->raw() );
	$this->assertEquals( 'ｶｷｸ'   , $a->raw()  );
	$this->assertTrue  ( $s === $t            );


	// Combine UTF-8 with UTF-32
	// Kind of assumes that the config encoding is UTF-8 because we compare to hardcoded values
	//
	$s  = new String( self::$golem, 'abc', self::$enc );
	$a  = new String( self::$golem, 'ｶｷｸ', self::$enc );

	$s->convert( 'UTF-8'  );
	$a->convert( 'UTF-32' );
	$t = $s->append( $a );

	$this->assertEquals( 'abcｶｷｸ', $s ->raw() );
	$this->assertEquals( 'ｶｷｸ'   , $a->convert( self::$cfgEnc )->raw()  );
	$this->assertTrue  ( $s === $t            );


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

	$s->convert( 'UTF-8'  );
	$a->convert( 'UTF-32' );
	$t = $s->prepend( $a );

	$this->assertEquals( 'ｶｷｸabc', $s ->raw()                          );
	$this->assertEquals( 'ｶｷｸ'   , $a->convert( self::$cfgEnc )->raw() );
	$this->assertTrue  ( $s === $t                                     );


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
