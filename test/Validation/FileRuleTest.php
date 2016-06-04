<?php
namespace Golem\Test;

use

	  Golem\Golem

	, Golem\Data\File

	, stdClass

;


class   FileRuleTest
extends \PHPUnit_Framework_TestCase
{

private static $golem ;


public
static
function setUpBeforeClass()
{
	self::$golem = new Golem;
}



/**
 *
 *
 */
public
function	testConstructor()
{
	$rule = self::$golem->fileRule();

	$this->assertFalse ( isset( $rule->options()[ 'exists' ] ) );
	$this->assertEquals( false, $rule->options( 'allowNull' )  );
}



/**
 *
 */
public
function	testValidateOptionExists()
{
	$rule = self::$golem->fileRule( [ 'exists' => true ] );
	$this->assertEquals( true, $rule->exists() );

	$rule = self::$golem->fileRule( [ 'exists' => false ] );
	$this->assertEquals( false, $rule->exists() );
}



/**
 * @dataProvider      invalidExists
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionExists( $exists )
{
	$rule = self::$golem->fileRule( [ 'exists' => $exists ] );
}



public
function invalidExists()
{
	return
	[
		  [ -1           ]
		, [ 0            ]
		, [ 1            ]
		, [ '12'         ]
		, [ []           ]
		, [ new stdClass ]
	];
}



/**
 * @expectedException Golem\Errors\ValidationException
 */
public
function	testUnexistingFileInvalidate()
{
	$rule = self::$golem->fileRule( [ 'exists' => true ] );

	$rule->validate( self::$golem->file( 'doesnotexists' ), 'Unit Testing' );
}



/**
 *
 */
public
function	testUnexistingFileValidate()
{
	$rule = self::$golem->fileRule( [ 'exists' => false ] );

	$this->assertInstanceOf
	(
		  'Golem\Data\File'
		, $rule->validate( self::$golem->file( 'doesnotexists' ), 'Unit Testing' )
	);
}



/**
 * @expectedException Golem\Errors\ValidationException
 */
public
function	testExistingFileInvalidate()
{
	$rule = self::$golem->fileRule( [ 'exists' => false ] );

	$rule->validate( self::$golem->file( __DIR__ . '/../TestData/someData' ), 'Unit Testing' );
}



/**
 *
 */
public
function	testExistingFileValidate()
{
	$rule = self::$golem->fileRule( [ 'exists' => true ] );

	$this->assertInstanceOf
	(
		  'Golem\Data\File'
		, $rule->validate( self::$golem->file( __DIR__ . '/../TestData/someData' ), 'Unit Testing' )
	);
}






/**
 *
 */
public
function	testUnexistingFileSanitizeCreate()
{
	$rule = self::$golem->fileRule( [ 'exists' => true ] );
	$file = $rule->sanitize( self::$golem->file( 'doesnotexists' ), 'Unit Testing' );

	$this->assertInstanceOf( 'Golem\Data\File', $file );
	$this->assertTrue      ( $file->exists()          );

	$file->rm();
}



/**
 *
 */
public
function	testUnexistingFileSanitize()
{
	$rule = self::$golem->fileRule( [ 'exists' => false ] );

	$this->assertInstanceOf
	(
		  'Golem\Data\File'
		, $rule->sanitize( self::$golem->file( 'doesnotexists' ), 'Unit Testing' )
	);
}



/**
 *
 */
public
function	testExistingFileSanitizeDelete()
{
	$rule = self::$golem->fileRule( [ 'exists' => false ] );
	$file = self::$golem->file    ( __DIR__ . '/newFile'  );

	$file->touch();

	$rule->sanitize   ( $file, 'Unit Testing' );
	$this->assertFalse( $file->exists()       );

}



/**
 *
 */
public
function	testExistingFileSanitize()
{
	$rule = self::$golem->fileRule( [ 'exists' => true ] );

	$this->assertInstanceOf
	(
		  'Golem\Data\File'
		, $rule->sanitize( self::$golem->file( __DIR__ . '/../TestData/someData' ), 'Unit Testing' )
	);
}

}
