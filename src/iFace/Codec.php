<?php


namespace Golem\iFace;


/**
 * Common functionality to classes that can encode/decode (eg. transform) data.
 *
 */
interface Codec
{
// public function __construct( mixed  $data   );
public function encode( $data );
public function decode( $data );
}
