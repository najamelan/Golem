<?php
namespace Golem\Test;

use

	  Golem\iFace\Logger       as iLogger

	, Golem\Golem

	, \stdClass
;


class GolemTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }



	public
	function	testConstructor()
	{
		// Create default library
		//
		$golem = new Golem;
		$this->assertTrue( isset( $golem->options()[ 'Logger' ][ 'name' ] ) );


		// Override an option using an array
		//
		$golem = new Golem( [ 'Logger' => [ 'name' => 'Will' ] ] );
		$this->assertEquals( $golem->options()[ 'Logger' ][ 'name' ], 'Will' );


		// Override an option using another golem
		//
		$golem  = new Golem( [ 'Logger' => [ 'name' => 'Will' ] ] );
		$golem2 = new Golem( $golem );
		$this->assertEquals( $golem2->options()[ 'Logger' ][ 'name' ], 'Will' );


		// Override an option using a string filename
		//
		$golem  = new Golem( __DIR__ . '/../TestData/testGolem.yml' );
		$this->assertEquals( $golem->options()[ 'Logger' ][ 'name' ], 'Olé' );
	}



	/**
	 * @expectedException Exception
	 * @dataProvider      constructorWrongParams
	 *
	 */
	public
	function	testConstructorWrongParams( $options )
	{
		$golem = new Golem( $options );
	}


	public
	function	constructorWrongParams()
	{
		return
		[
			  [ 'olé'        ]  // invalid filename
			, [ __FILE__     ]  // valid filename that doesn't parse to options
			, [ 4            ]  // integer
			, [ null         ]
			, [ new stdClass ]  // Random object
		];
	}



	public
	function	testLogger()
	{
		// Create default logger
		//
		$this->assertTrue( self::$golem->logger() instanceof iLogger );


		// Make sure that two loggers with the same name are the same object
		//
		$logger  = self::$golem->logger( 'testLogger', [ 'logfile' => 'somethingrandom' ] );
		$logger2 = self::$golem->logger( 'testLogger' );

		$this->assertEquals( $logger, $logger2 );


		// Make sure that two loggers with a different name aren't equal
		//
		$logger  = self::$golem->logger( 'testLogger2' );
		$logger2 = self::$golem->logger( 'testLogger3' );

		$this->assertNotEquals( $logger, $logger2 );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLoggerOverrideExisting()
	{
		// Make sure that two loggers with the same name are the same object
		//
		$logger = self::$golem->logger( 'testLogger' );
		$logger = self::$golem->logger( 'testLogger', [ 'logfile' => 'somethingrandom' ] );
	}
}
