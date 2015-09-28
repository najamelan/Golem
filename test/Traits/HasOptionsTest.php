<?php
namespace Golem\Test;

use

	  Golem\Golem

;


class HasOptionsTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
	{
		self::$golem = new Golem;
	}


	public
	function	testSetupOptions()
	{
		// Test division between userset and defaults
		//
		$log = self::$golem->logger( 'testSetup', [ 'logfile' => 'echo' ] );

		$this->assertEquals( 'phplog', $log->defaults()[ 'logfile' ] );
		$this->assertEquals( 'echo'  , $log->options ()[ 'logfile' ] );
		$this->assertEquals( 'echo'  , $log->userset ()[ 'logfile' ] );


		// Test merging of userset and defaults
		//
		$golem = new Golem( [ 'Logger' => [ 'logfile' => [ 'echo', 'phplog', '/home/user/golem.log' ] ] ] );
		$log = $golem->logger( 'testSetup', [ 'logfile' => [ '/var/log/golem', '/var/log/golem2' ] ] );


		$this->assertEquals( [ 'echo'          , 'phplog', '/home/user/golem.log' ], $log->defaults()[ 'logfile' ] );
		$this->assertEquals( [ '/var/log/golem', '/var/log/golem2'                ], $log->options ()[ 'logfile' ] );
		$this->assertEquals( [ '/var/log/golem', '/var/log/golem2'                ], $log->userset ()[ 'logfile' ] );
	}


}

