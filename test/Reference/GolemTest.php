<?php
namespace Golem\Test;

use

	  Golem\iFace\Logger       as iLogger
	, Golem\iFace\Data\Options as iOptions

	, Golem\Golem
	, Golem\Reference\Data\Options
	, Golem\Reference\Data\GolemOptions
	, Golem\Reference\Data\LogOptions

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
		$this->assertTrue( isset( $golem->options()[ 'logger' ][ 'prefix' ] ) );


		$overrides =
		[
			  __DIR__ . '/../TestData/testGolem.yml'                   // filename

			,                   [ 'logger' => [ 'prefix' => 'Olé' ] ]     // array
			, new Options     ( [ 'logger' => [ 'prefix' => 'Olé' ] ] )   // Options
			, new GolemOptions( [ 'logger' => [ 'prefix' => 'Olé' ] ] )   // GolemOptions
			, new Golem       ( [ 'logger' => [ 'prefix' => 'Olé' ] ] )   // Golem
		];


		foreach( $overrides as $options )
		{
			$golem = new Golem( $options );

			// Make sure it overrides
			//
			$this->assertTrue( $golem->options()            [ 'logger' ][ 'prefix' ] === 'Olé'   );
			$this->assertTrue( $golem->options()->userSet ()[ 'logger' ][ 'prefix' ] === 'Olé'   );
			$this->assertTrue( $golem->options()->defaults()[ 'logger' ][ 'prefix' ] === 'Golem' );

			// Make sure they are merged correctly
			//
			$this->assertTrue( $golem->options()[ 'logger' ][ 'name'   ] === 'General' );
		}
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


		// Create default logger with LogOptions
		//
		$this->assertTrue( self::$golem->logger( new LogOptions( self::$golem ) ) instanceof iLogger );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLoggerWrongParams()
	{
		// Create logger with wrong parameter type.
		//
		self::$golem->logger( 4 );
	}



	public
	function	testOpions()
	{
		$options = self::$golem->options();

		// Get the options.
		//
		$this->assertTrue( $options instanceof iOptions );

		// Make sure they're sealed.
		//
		$this->assertTrue( $options->sealed() );
	}

}
