<?php

namespace Golem\Reference;


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
}


/*
 *
 ||  $codePoint === 0x00FFFF
 ||  $codePoint === 0x01FFFF
 ||  $codePoint === 0x02FFFF
 ||  $codePoint === 0x03FFFF
 ||  $codePoint === 0x04FFFF
 ||  $codePoint === 0x05FFFF
 ||  $codePoint === 0x06FFFF
 ||  $codePoint === 0x07FFFF
 ||  $codePoint === 0x08FFFF
 ||  $codePoint === 0x09FFFF
 ||  $codePoint === 0x0AFFFF
 ||  $codePoint === 0x0BFFFF
 ||  $codePoint === 0x0CFFFF
 ||  $codePoint === 0x0DFFFF
 ||  $codePoint === 0x0EFFFF
 ||  $codePoint === 0x0FFFFF
 ||  $codePoint === 0x10FFFF

     $codePoint === 0x00FFFE
 ||  $codePoint === 0x01FFFE
 ||  $codePoint === 0x02FFFE
 ||  $codePoint === 0x03FFFE
 ||  $codePoint === 0x04FFFE
 ||  $codePoint === 0x05FFFE
 ||  $codePoint === 0x06FFFE
 ||  $codePoint === 0x07FFFE
 ||  $codePoint === 0x08FFFE
 ||  $codePoint === 0x09FFFE
 ||  $codePoint === 0x0AFFFE
 ||  $codePoint === 0x0BFFFE
 ||  $codePoint === 0x0CFFFE
 ||  $codePoint === 0x0DFFFE
 ||  $codePoint === 0x0EFFFE
 ||  $codePoint === 0x0FFFFE
 ||  $codePoint === 0x10FFFE

 ||
 */
