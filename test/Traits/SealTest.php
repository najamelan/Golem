<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Data\Options
	, Golem\Traits\Seal

	, \ReflectionClass
;


class SealTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
	{
		self::$golem = new Golem;
	}


	public
	function	testSealing()
	{
		// Seal should return $this
		//
		$opt  = new Options();
		$opt2 = $opt->seal();
		$this->assertEquals( $opt, $opt2 );


		// Seal an object that has options
		//
		$golem2 = self::$golem->seal();
		$this->assertEquals( self::$golem, $golem2 );
	}


	public
	function	testSealed()
	{
		// Sealed should return false
		//
		$opt  = new Options();
		$this->assertFalse( $opt->sealed() );


		// Sealed should return true
		//
		$opt  = new Options();
		$opt2 = $opt->seal();
		$this->assertTrue( $opt->sealed() );


		// Sealed should return false on an object that has options
		//
		self::$golem = new Golem;
		$this->assertFalse( self::$golem->sealed() );


		// Sealed should return true an object that has options
		//
		self::$golem = new Golem;
		self::$golem->seal();
		$this->assertTrue( self::$golem->sealed() );
	}



	public
	function	testSealedBypassing()
	{
		// Try to get round sealing with reflection.
		// Unfortunately, at this moment, it seems the only way is to recompile php with --disable-reflection.
		// The test will be skipped for now.
		//
		$this->markTestSkipped( 'For now can only work by recompiling PHP.' );

		$opt  = new Options( [ 'name' => 'John' ] );
		$opt->seal();


		$reflect = new ReflectionClass( 'Golem\Reference\Data\Options' );

		$prop = $reflect->getProperty( 'parsed' );
		$prop->setAccessible( true );
		$prop->setValue( $opt, [ 'name' => 'Will' ] );
		$this->assertEquals( $opt[ 'name' ], 'John' );
	}
}


class NoSeal
{
	use Seal;
}
