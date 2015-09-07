<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Data\Options
;


class ArrayAccessTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }


	public
	function	testAssign()
	{
		// Assign null
		//
		$opt = new Options();
		$opt[ 'test' ] = null;
		$this->assertEquals( $opt[ 'test' ], null );

		// Assign a string
		//
		$opt = new Options();
		$opt[ 'test' ] = 'olé';
		$this->assertEquals( $opt[ 'test' ], 'olé' );

		// Override a value
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$opt[ 'name' ] = 'Will';
		$this->assertEquals( $opt[ 'name' ], 'Will' );

		// Override a property, save another
		//
		$opt = new Options( [ 'name' => 'John', 'date' => '2015' ] );
		$opt[ 'name' ] = 'Will';
		$this->assertEquals( $opt[ 'name' ], 'Will' );
		$this->assertEquals( $opt[ 'date' ], '2015' );
	}


	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testAssignSealed()
	{
		// Try to assign to a sealed object
		//
		$opt = new Options();
		$opt->seal();
		$opt[ 'name' ] = 'Will';
	}


	public
	function	testIsset()
	{
		// Should not be set
		//
		$opt = new Options();
		$this->assertFalse( isset( $opt[ 'test' ] ) );

		// Existing value should be set
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$this->assertTrue( isset( $opt[ 'name' ] ) );
	}


	public
	function	testUnset()
	{
		// Existing value should be set
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$this->assertTrue( isset( $opt[ 'name' ] ) );

		unset( $opt[ 'name' ] );
		$this->assertFalse( isset( $opt[ 'name' ] ) );
	}
}
