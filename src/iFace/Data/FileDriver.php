<?php
/**
 *
 */



namespace Golem\iFace\Data;

use

	  Golem\Golem
;

/**
 *
 */
interface FileDriver
{
const CAPABILITIES =
[
	  'parse'
	, 'readMeta'
	, 'writeMeta'
	, 'cleanMeta'
];

public function can( $capability );
}
