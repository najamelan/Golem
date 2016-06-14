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

private static $golem;
private static $testDir;


public
static
function setUpBeforeClass()
{
	self::$golem   = new Golem;
	self::$testDir = self::$golem->file( __DIR__ . '/../TestData/FileRuleTests' );

	self::$testDir->rm();
	self::$testDir->mkdir();
}



public
static
function tearDownAfterClass()
{
	self::$testDir->rm();
}


/**
 *
 *
 */
public
function	testConstructor()
{
	$rule = self::$golem->fileRule();

	$this->assertNull ( $rule->options( 'exists'    ) );
	$this->assertNull ( $rule->options( 'isDir'     ) );

	$this->assertFalse( $rule->options( 'allowNull' ) );
}



/**
 *
 */
public
function	testValidateOptionExists()
{
	$rule = self::$golem->fileRule( [ 'exists' => true  ] );
	$this->assertTrue( $rule->exists() );

	$rule = self::$golem->fileRule( [ 'exists' => false ] );
	$this->assertFalse( $rule->exists() );
}



public
function	testValidateOptionIsDir()
{
	$rule = self::$golem->fileRule( [ 'isDir' => true  ] );
	$this->assertTrue( $rule->isDir() );

	$rule = self::$golem->fileRule( [ 'isDir' => false ] );
	$this->assertFalse( $rule->isDir() );
}


/**
 * @dataProvider      invalidBools
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionExists( $exists )
{
	$rule = self::$golem->fileRule( [ 'exists' => $exists ] );
}


/**
 * @dataProvider      invalidBools
 * @expectedException InvalidArgumentException
 */
public
function	testInvalidOptionIsDir( $isDir )
{
	$rule = self::$golem->fileRule( [ 'isDir' => $isDir ] );
}



public
function invalidBools()
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
 * Test Getter/setters
 */
public
function testGetterExists()
{
	$rule = self::$golem->fileRule();

	$this->assertNull( $rule->exists() );

	$rule->exists( true );

	$this->assertTrue( $rule->exists() );
}



public
function testGetterIsDir()
{
	$rule = self::$golem->fileRule();

	$this->assertNull( $rule->isDir() );

	$rule->isDir( true );

	$this->assertTrue( $rule->isDir() );
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
		, $rule->validate( self::$golem->file( self::$testDir . '/doesnotexists' ), 'Unit Testing' )
	);
}



/**
 * @expectedException Golem\Errors\ValidationException
 */
public
function	testExistingFileInvalidate()
{
	$rule = self::$golem->fileRule( [ 'exists' => false ] );

	$rule->validate( self::$golem->file( self::$testDir . '/../someData' ), 'Unit Testing' );
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
		, $rule->validate( self::$golem->file( self::$testDir . '/../someData' ), 'Unit Testing' )
	);
}






/**
 * Test sanitizing files
 *
 * - creating and deleting
 *
 */
public
function	testUnexistingFileSanitizeCreate()
{
	$file = self::$golem->file( self::$testDir . '/doesNotExistCreateFile' );

	$file = self::$golem->fileRule()

		->exists  ( true                  )
		->sanitize( $file, 'Unit Testing' )
	;

	$this->assertInstanceOf( 'Golem\Data\File', $file );
	$this->assertTrue      ( $file->exists()          );

	return $file;
}



/**
 * @depends testUnexistingFileSanitizeCreate
 */
public
function	testExistingFileSanitize( File $file )
{
	$file = self::$golem->fileRule()

		->exists  ( true                  )
		->sanitize( $file, 'Unit Testing' )
	;

	$this->assertInstanceOf( 'Golem\Data\File', $file );
	$this->assertTrue      ( $file->exists()          );
	$this->assertFalse     ( $file->isDir ()          );

	return $file;
}



/**
 * @depends testExistingFileSanitize
 *
 */
public
function	testExistingFileSanitizeDelete( File $file )
{
	$file = self::$golem->fileRule()

		->exists  ( false                 )
		->sanitize( $file, 'Unit Testing' )
	;

	$this->assertInstanceOf( 'Golem\Data\File', $file );
	$this->assertFalse     ( $file->exists()          );
	$this->assertFalse     ( $file->isDir ()          );
}



/**
 * Test passing sanitation on an unexisting file.
 * It shouldn't exist afterwards, instanceof should be Golem\File
 */
public
function	testUnexistingFileSanitize()
{
	$file = self::$golem->file( self::$testDir . '/doesNotExist' );
	$this->assertFalse( $file->exists() );


		$file = self::$golem->fileRule()

			->exists  ( false                 )
			->sanitize( $file, 'Unit Testing' )
		;


	$this->assertInstanceOf( 'Golem\Data\File', $file );
	$this->assertFalse     ( $file->exists()          );
	$this->assertFalse     ( $file->isDir ()          );
}



public
function	testUnexistingFileSanitizeCreateDir()
{

	$dir = self::$golem->file( self::$testDir . '/doesNotExistCreateDirectory' );

	$dir = self::$golem->fileRule()

		->exists( true )
		->isDir ( true )

		->sanitize( $dir, 'Unit Testing' )
	;


	$this->assertInstanceOf( 'Golem\Data\File', $dir );
	$this->assertTrue      ( $dir->exists()          );
	$this->assertTrue      ( $dir->isDir ()          );
}




}
