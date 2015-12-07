<?php

namespace Golem;


use

	  InvalidArgumentException
;



class Unicode
{

/*
 * Test if codePoint value is a control character.
 *
 * https://en.wikipedia.org/wiki/C0_and_C1_control_codes
 *
 */
public
static
function isControlChar( $codePoint )
{
	if
	(
		    $codePoint < 0x20                          // C0 control characters
		||  $codePoint > 0x7E  &&  $codePoint < 0xA0   // C1 control characters
	)
	{
		return true;
	}

	return false;
}



/*
 * Test if codePoint value is a noncharacter.
 *
 * Unicode Standard v8.0 section 23.7
 *
 */
public
static
function isNonChar( $codePoint )
{
	if
	(
		   ( $codePoint & 0xFFFF ) === 0xFFFF
		|| ( $codePoint & 0xFFFF ) === 0xFFFE

		|| $codePoint >= 0xFDD0  &&  $codePoint <= 0xFDEF
	)
	{
		return true;
	}

	return false;
}



/*
 * Test if codePoint value is a noncharacter.
 *
 * Unicode Standard v8.0 section: 2.4 Code Points and Characters
 *
 */
public
static
function isCodePoint( $codePoint )
{
	if( $codePoint >= 0  &&  $codePoint <= 0x10FFFF )

		return true;


	return false;
}
}


