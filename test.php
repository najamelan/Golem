<?php

function test( $p1 = [], $p2 = [] )
{
	var_dump( $p1 );
	var_dump( $p2 );
	echo "-----\n\n";
}

test();
test( null );
test( null, null );
test( "a", null );
