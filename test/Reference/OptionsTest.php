<?php
namespace Golem\Test;

use

	  Golem\Golem
	, Golem\Reference\Data\Options
;


class OptionsTest extends \PHPUnit_Framework_TestCase
{
	private static $golem;


	public static function setUpBeforeClass()
   {
   	self::$golem = new Golem;
   }


	/**
	 * @expectedException Exception
	 * @dataProvider      wrongConstructorParamsData
	 *
	 */
	public
	function	testWrongCostructorParams( $p1, $p2 )
	{
		if( $p1 === 'noparam' && $p2 === 'noparam' )

			$opt = new Options();


		elseif( $p2 === 'noparam' )

			$opt = new Options( $p1 );


		else

			$opt = new Options( $p1, $p2 );
	}


	public
	function	wrongConstructorParamsData()
	{
		return

			[
				  [ null     , null      ]
				, [ false    , false     ]
				, [ true     , true      ]
				, [ 'test'   , 'other'   ]
				, [ 0        , -5        ]
				, [ null     , 'noparam' ]
				, [ false    , 'noparam' ]
				, [ 1        , 'noparam' ]
				, [ 'test'   , 'noparam' ]
				, [ null     , 0         ]
				, [ 'test'   , 0         ]
			]
		;
	}



	/**
	 * @dataProvider goodConstructorParamsData
	 *
	 */
	public
	function	testGoodCostructorParams( $p1, $p2 )
	{
		if( $p1 === 'noparam' && $p2 === 'noparam' )

			$opt = new Options();


		elseif( $p2 === 'noparam' )

			$opt = new Options( $p1 );


		else

			$opt = new Options( $p1, $p2 );
	}


	public
	function	goodConstructorParamsData()
	{
		return

			[
				  [ 'noparam'           , 'noparam'            ]
				, [ []                  , []                   ]
				, [ new Options()       , new Options()        ]
				, [ []                  , 'noparam'            ]
				, [ []                  , new Options()        ]
				, [ new Options()       , 'noparam'            ]
				, [ new Options()       , []                   ]
				, [ [ 'name' => 'John' ], [ 'date' => '2015' ] ]
				, [ [ 'name' => 'John' ], [ 'name' => 'Will' ] ]
			]
		;

	}


	/**
	 *
	 */
	public
	function	testCostructor()
	{
		// Set a property
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$this->assertEquals( $opt[ 'name' ], 'John' );

		// Make sure a new object has a fresh start
		//
		$opt = new Options();
		$this->assertEquals( $opt[ 'name' ], null );

		// Override defaults
		//
		$opt = new Options( [ 'name' => 'John' ], [ 'name' => 'Will' ] );
		$this->assertEquals( $opt[ 'name' ], 'John' );

		// Combine defaults with options
		//
		$opt = new Options( [ 'name' => 'John' ], [ 'name' => 'Will', 'date' => '2015' ] );
		$this->assertEquals( $opt[ 'date' ], '2015' );
		$this->assertEquals( $opt[ 'name' ], 'John' );

		// Add a new property not present in defaults
		//
		$opt = new Options( [ 'name' => 'Will', 'date' => '2015' ], [ 'name' => 'John' ] );
		$this->assertEquals( $opt[ 'date' ], '2015' );
		$this->assertEquals( $opt[ 'name' ], 'Will' );

		// Test non string keys. We could decide that it's not supported, but if we don't
		// it should work. If we do, we should actually forbid it.
		//
		$opt = new Options( [ 12345 => 'Will' ] );
		$this->assertEquals( $opt[ 12345 ], 'Will' );
	}



	public
	function	testOverride()
	{
		// Override with an empty array
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$opt->override( [] );
		$this->assertEquals( $opt[ 'name' ], 'John' );

		// Override with an empty options object
		//
		$opt  = new Options( [ 'name' => 'John' ]   );
		$opt->override( new Options() );
		$this->assertEquals( $opt[ 'name' ], 'John' );

		// Override a property
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$opt->override( [ 'name' => 'Will' ] );
		$this->assertEquals( $opt[ 'name' ], 'Will' );

		// Override a property with Options object
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$opt->override( new Options( [ 'name' => 'Will' ] ) );
		$this->assertEquals( $opt[ 'name' ], 'Will' );

		// Override a property, save another
		//
		$opt = new Options( [ 'name' => 'John', 'date' => '2015' ] );
		$opt->override( [ 'name' => 'Will' ] );
		$this->assertEquals( $opt[ 'name' ], 'Will' );
		$this->assertEquals( $opt[ 'date' ], '2015' );

		// Override a property, save another with Options object
		//
		$opt = new Options( [ 'name' => 'John', 'date' => '2015' ] );
		$opt->override( new Options( [ 'name' => 'Will' ] ) );
		$this->assertEquals( $opt[ 'name' ], 'Will' );
		$this->assertEquals( $opt[ 'date' ], '2015' );
	}


	/**
	 * @expectedException Exception
	 *
	 */
	public
	function	testOverrideSealed()
	{
		// Override with an empty array
		//
		$opt = new Options( [ 'name' => 'John' ]   );
		$opt->seal();
		$opt->override( [] );
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
}
