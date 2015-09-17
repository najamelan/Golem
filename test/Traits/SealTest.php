<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Traits\Seal

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
		$copy = self::$golem->seal();
		$this->assertEquals( self::$golem, $copy );
	}


	public
	function	testSealed()
	{
		// Sealed should return false
		//
		$golem  = new Golem();
		$this->assertFalse( $golem->sealed() );


		// Sealed should return true
		//
		$golem  = new Golem();
		$golem->seal();
		$this->assertTrue( $golem->sealed() );
	}



	public
	function	testSealedBypassing()
	{
		// Try to get round sealing with reflection. This method works by directly accessing private data members.
		// You need to disable the ReflectionClass in you php.ini in order to protect yourself from this.
		//
		// see disable_classes ini option.
		//
		$this->markTestSkipped( "This requires the ReflectionClass to be disabled, but phpunit requires it." );

		if( class_exists( "ReflectionClass" ) )
		{
			$golem = new Golem();
			$golem->seal();

			$reflect = new ReflectionClass( 'Golem\Golem' );

			$prop = $reflect->getProperty( 'options' );
			$prop->setAccessible( true );
			$prop->setValue( $golem, 'HACKED' );
			$this->assertTrue( is_array( $golem->options() ), "Make sure the options are an array, but were: " . print_r( $golem->options(), true ) );
		}
	}
}

