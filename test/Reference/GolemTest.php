<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Data\Options
;


class GolemTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }



	public
	function	testLogger()
	{
		// Create default logger
		//
		self::$golem->logger();
	}



}
