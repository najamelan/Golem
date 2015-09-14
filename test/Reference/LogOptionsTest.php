<?php
namespace Golem\Test;

use

	  Golem\iFace\Logger as iLogger

	, Golem\Golem
	, Golem\Reference\Data\LogOptions

	, \LoggerLevel
;


class LogOptionsTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }


	public
	function	testConstructor()
	{
		// Test merging of default options from log and log4php
		//
		$lOpts = new LogOptions( self::$golem );
		$this->assertEquals( $lOpts->defaults()[ 'prefix' ], 'Golem' );
		$this->assertEquals( $lOpts->defaults()[ 'additivity' ], false );

		// Test overriding defaults
		//
		$lOpts = new LogOptions( self::$golem, [ 'prefix' => 5] );
		$this->assertEquals( $lOpts[ 'prefix' ], 5 );
		$this->assertEquals( $lOpts->defaults()[ 'prefix' ], 'Golem' );
	}


	public
	function	testName()
	{
		// Test defaults
		//
		$lOpts = new LogOptions( self::$golem );
		$this->assertEquals( $lOpts->name(), 'Golem.General' );

		// Test overriding default options
		//
		$lOpts = new LogOptions( self::$golem, [ 'name' => 'haha' ] );
		$this->assertEquals( $lOpts->name(), 'Golem.haha' );
	}


	public
	function	testAdditivity()
	{
		// Test defaults
		//
		$lOpts = new LogOptions( self::$golem );
		$this->assertEquals( $lOpts->additivity(), false );

		// Test overriding defaults
		//
		$lOpts = new LogOptions( self::$golem, [ 'additivity' => true ] );
		$this->assertEquals( $lOpts->additivity(), true );
	}


	public
	function	testLevel()
	{
		// Test defaults
		//
		$lOpts = new LogOptions( self::$golem );
		$this->assertEquals( $lOpts->level(), iLogger::ALL );

		// Test overriding defaults
		//
		$lOpts = new LogOptions( self::$golem, [ 'level' => 'INFO' ] );
		$this->assertEquals( $lOpts->level(), iLogger::INFO );

		// Test overriding defaults with integer
		//
		$lOpts = new LogOptions( self::$golem, [ 'level' => iLogger::ALL ] );
		$this->assertEquals( $lOpts->level(), iLogger::ALL );

		// Test reading from a sealed object
		//
		$lOpts = new LogOptions( self::$golem );
		$lOpts->seal();
		$this->assertEquals( $lOpts->level(), iLogger::ALL );

		// Test changing values
		//
		$lOpts = new LogOptions( self::$golem );
		$this->assertEquals( $lOpts->level( 'INFO'       ), iLogger::INFO );
		$this->assertEquals( $lOpts->level( iLogger::ALL ), iLogger::ALL  );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevelSealed()
	{
		// Try to change a sealed object
		//
		$lOpts = new LogOptions( self::$golem );
		$lOpts->seal();
		$lOpts->level( iLogger::ALL );
	}


	public
	function	testString2Level()
	{
		// Verify mappings
		//
		$this->assertEquals( LogOptions::string2level( 'ALL'       ), iLogger::ALL       );
		$this->assertEquals( LogOptions::string2level( 'DEBUG'     ), iLogger::DEBUG     );
		$this->assertEquals( LogOptions::string2level( 'INFO'      ), iLogger::INFO      );
		$this->assertEquals( LogOptions::string2level( 'NOTICE'    ), iLogger::NOTICE    );
		$this->assertEquals( LogOptions::string2level( 'WARNING'   ), iLogger::WARNING   );
		$this->assertEquals( LogOptions::string2level( 'ERROR'     ), iLogger::ERROR     );
		$this->assertEquals( LogOptions::string2level( 'CRITICAL'  ), iLogger::CRITICAL  );
		$this->assertEquals( LogOptions::string2level( 'ALERT'     ), iLogger::ALERT     );
		$this->assertEquals( LogOptions::string2level( 'EMERGENCY' ), iLogger::EMERGENCY );
		$this->assertEquals( LogOptions::string2level( 'OFF'       ), iLogger::OFF       );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testString2LevelInvalid()
	{
		// Send wrong parameter type.
		//
		LogOptions::string2level( 5 );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testString2LevelWrongLevel()
	{
		// Send wrong value.
		//
		LogOptions::string2level( 'Bazinga' );
	}


	public
	function	testLevel2String()
	{
		// Verify mappings
		//
		$this->assertEquals( LogOptions::level2string( iLogger::ALL       ), 'ALL'       );
		$this->assertEquals( LogOptions::level2string( iLogger::DEBUG     ), 'DEBUG'     );
		$this->assertEquals( LogOptions::level2string( iLogger::INFO      ), 'INFO'      );
		$this->assertEquals( LogOptions::level2string( iLogger::NOTICE    ), 'NOTICE'    );
		$this->assertEquals( LogOptions::level2string( iLogger::WARNING   ), 'WARNING'   );
		$this->assertEquals( LogOptions::level2string( iLogger::ERROR     ), 'ERROR'     );
		$this->assertEquals( LogOptions::level2string( iLogger::CRITICAL  ), 'CRITICAL'  );
		$this->assertEquals( LogOptions::level2string( iLogger::ALERT     ), 'ALERT'     );
		$this->assertEquals( LogOptions::level2string( iLogger::EMERGENCY ), 'EMERGENCY' );
		$this->assertEquals( LogOptions::level2string( iLogger::OFF       ), 'OFF'       );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevel2StringInvalid()
	{
		// Send wrong parameter type.
		//
		LogOptions::level2string( '5' );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevel2StringWrongLevel()
	{
		// Send wrong value.
		//
		LogOptions::level2string( 987654 );
	}


	public
	function	testLevel2log4php()
	{
		// Verify mappings
		//
		$this->assertEquals( LogOptions::level2log4php( iLogger::ALL       ), LoggerLevel::getLevelDebug() );
		$this->assertEquals( LogOptions::level2log4php( iLogger::DEBUG     ), LoggerLevel::getLevelDebug() );
		$this->assertEquals( LogOptions::level2log4php( iLogger::INFO      ), LoggerLevel::getLevelInfo () );
		$this->assertEquals( LogOptions::level2log4php( iLogger::NOTICE    ), LoggerLevel::getLevelInfo () );
		$this->assertEquals( LogOptions::level2log4php( iLogger::WARNING   ), LoggerLevel::getLevelWarn () );
		$this->assertEquals( LogOptions::level2log4php( iLogger::ERROR     ), LoggerLevel::getLevelError() );
		$this->assertEquals( LogOptions::level2log4php( iLogger::CRITICAL  ), LoggerLevel::getLevelFatal() );
		$this->assertEquals( LogOptions::level2log4php( iLogger::ALERT     ), LoggerLevel::getLevelFatal() );
		$this->assertEquals( LogOptions::level2log4php( iLogger::EMERGENCY ), LoggerLevel::getLevelFatal() );
		$this->assertEquals( LogOptions::level2log4php( iLogger::OFF       ), LoggerLevel::getLevelOff  () );

		$this->assertEquals( LogOptions::level2log4php( 'ALL'       ), LoggerLevel::getLevelDebug() );
		$this->assertEquals( LogOptions::level2log4php( 'DEBUG'     ), LoggerLevel::getLevelDebug() );
		$this->assertEquals( LogOptions::level2log4php( 'INFO'      ), LoggerLevel::getLevelInfo () );
		$this->assertEquals( LogOptions::level2log4php( 'NOTICE'    ), LoggerLevel::getLevelInfo () );
		$this->assertEquals( LogOptions::level2log4php( 'WARNING'   ), LoggerLevel::getLevelWarn () );
		$this->assertEquals( LogOptions::level2log4php( 'ERROR'     ), LoggerLevel::getLevelError() );
		$this->assertEquals( LogOptions::level2log4php( 'CRITICAL'  ), LoggerLevel::getLevelFatal() );
		$this->assertEquals( LogOptions::level2log4php( 'ALERT'     ), LoggerLevel::getLevelFatal() );
		$this->assertEquals( LogOptions::level2log4php( 'EMERGENCY' ), LoggerLevel::getLevelFatal() );
		$this->assertEquals( LogOptions::level2log4php( 'OFF'       ), LoggerLevel::getLevelOff  () );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevel2log4phpInvalid()
	{
		// Send wrong parameter type.
		//
		LogOptions::level2log4php( [] );
	}



	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testLevel2log4phpWrongLevel()
	{
		// Send wrong value.
		//
		LogOptions::level2log4php( 987654 );
	}



}
