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
function	testUnexistingFile()
{
	$rule = self::$golem->fileRule( [ 'exists' => true ] );

	$rule->validate( self::$golem->file( 'doesnotexists' ), 'FileRuleTest::testUnexistingFile()' );
}

}
