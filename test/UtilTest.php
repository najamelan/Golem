<?php
namespace Golem\Test;

use

	  Golem\Util
	, Golem\Golem

	, SplFileInfo

;


class UtilTest extends \PHPUnit_Framework_TestCase
{


/**
 * @dataProvider getTypeData
 *
 */
public
function	testGetType( $expect, $value )
{
	$this->assertEquals( $expect, Util::getType( $value ) );
}



public
function getTypeData()
{
	return
	[
		  // Test scalar types
		  //
		  [ 'integer', 0     ]
		, [ 'double' , 0.0   ]
		, [ 'string' , 'hai' ]
		, [ 'array'  , []    ]
		, [ 'boolean', true  ]

		  // Test objects
		  //
		, [ 'SplFileInfo', new SplFileInfo( 'text.txt' ) ]
		, [ 'Golem\Golem', new Golem                     ]
	];
}



public
function	testJoinAssociativeArray()
{
	// Test joining two empty arrays
	//
	$a = Util::joinAssociativeArray( [], [] );
	$this->assertEquals( [], $a );


	// Test joining on an empty array
	//
	$one  = [];
	$two  = [ 'ha' => 'hi' ];
	$join = Util::joinAssociativeArray( $one, $two );

	$this->assertEquals( $two, $join );


	// Test joining on an empty array
	//
	$one  = [];
	$two  = [ 'ha' => [ 'hi' => 'ho' ] ];
	$join = Util::joinAssociativeArray( $one, $two );

	$this->assertEquals( $two, $join );


	// Test joining an empty array
	//
	$one  = [];
	$two  = [ 'ha' => 'hi' ];
	$join = Util::joinAssociativeArray( $two, $one );

	$this->assertEquals( $two, $join );


	// Test joining an empty array
	//
	$one  = [];
	$two  = [ 'ha' => [ 'hi' => 'ho' ] ];
	$join = Util::joinAssociativeArray( $two, $one );

	$this->assertEquals( $two, $join );


	// Test joining without overlapping keys
	//
	$one  = [ 'he' => 'ha' ];
	$two  = [ 'hi' => 'ho' ];
	$join = Util::joinAssociativeArray( $one, $two );

	$this->assertEquals( [ 'he' => 'ha', 'hi' => 'ho' ], $join );


	// Test joining without overlapping keys
	//
	$one  = [ 'he' => [ 'ha' => 'hu' ] ];
	$two  = [ 'hi' => [ 'ho' => 'hu' ] ];
	$join = Util::joinAssociativeArray( $one, $two );

	$this->assertEquals( [ 'he' => [ 'ha' => 'hu' ], 'hi' => [ 'ho' => 'hu' ] ], $join );


	// Test joining overlapping keys
	//
	$one  = [ 'he' => 'ha' ];
	$two  = [ 'he' => 'ho' ];
	$join = Util::joinAssociativeArray( $one, $two );

	$this->assertEquals( [ 'he' => 'ho' ], $join );


	// Test joining overlapping keys
	//
	$one  = [ 'he' => [ 'ha' => 'hu' ] ];
	$two  = [ 'he' => [ 'ho' => 'hi' ] ];
	$join = Util::joinAssociativeArray( $one, $two );

	$this->assertEquals( [ 'he' => [ 'ha' => 'hu', 'ho' => 'hi' ] ], $join );
}



public
function	testDelTree()
{
	$this->markTestIncomplete();
}



}
