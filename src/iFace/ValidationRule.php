<?php
/**
 *
 */



namespace Golem\iFace;

use

	  Golem\Golem

	, Golem\Traits\Seal
	, Golem\Traits\HasOptions
	, Golem\Traits\HasLog

	, Golem\Util
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
