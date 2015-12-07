<?php

/**
 * This is the Options object.
 *
 * The data for the Options object should ideally come from
 * a place that is outside the document root, is readable but not
 * writable by the web server process.
 *
 */

namespace Golem\Codecs;

require_once __DIR__ . '/../../lib/sfYaml/Exception/ExceptionInterface.php';
require_once __DIR__ . '/../../lib/sfYaml/Exception/RuntimeException.php';
require_once __DIR__ . '/../../lib/sfYaml/Exception/ParseException.php';
require_once __DIR__ . '/../../lib/sfYaml/Inline.php';
require_once __DIR__ . '/../../lib/sfYaml/Parser.php';
require_once __DIR__ . '/../../lib/sfYaml/Unescaper.php';


use

	Symfony\Component\Yaml\Parser

;


class      ParseYAML
implements \Golem\iFace\Codec
{

public
function	decode( $data )
{
	$parser = new Parser();
	$parsed = $parser->parse( $data );


	return $parsed;
}


/**
 * @codeCoverageIgnore
 */
public function encode( $data )
{
}
}
