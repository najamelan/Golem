<?php
/**
 *
 */



namespace Golem\iFace;

use

	  Golem\Golem

	, Golem\Reference\Traits\Seal
	, Golem\Reference\Traits\HasOptions
	, Golem\Reference\Traits\HasLog

	, Golem\Reference\Util
;

/**
 *
 */
interface ValidationRule
{
	const INVALID = 'INVALID';

	public function sanitize( $input, $context );
	public function validate( $input, $context );
}
