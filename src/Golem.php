<?php
/**
 * @ignore
 * The main interface to the library.
 *
 */

/**
 * The toplevel namespace for the library.
 *
 */
namespace Golem;

require_once __DIR__ . '/Traits/Autoload.php';

use   Golem\iFace\Data\Options          as iOptions
	 , Golem\iFace\Data\LogOptions       as iLogOptions

    , Golem\Reference\Logger
    , Golem\Reference\Data\GolemOptions
    , Golem\Reference\Data\LogOptions
    , Golem\Reference\Data\File

    , \Exception
;



/**
 * Phpdoc cannot deal with string concatenaton in class constants, so we
 * have to store this value here.
 *
 */
define( 'PHPDOCBUG', __DIR__ . '/GolemDefaults.yml' );


/**
 * The main library object
 *
 *
 *
 */
class Golem
{
	use Traits\Seal;

	const DEFAULT_OPTIONS_FILE = PHPDOCBUG;


	private $defaultOptions;
	private $clientOptions;
	private $options;


	/**
	 * Create a library object.
	 *
	 * You should usuall create one and pass it round in your application.
	 * You can seal this object to prevent changes to security configuration
	 * to made by code included later in your application.
	 *
	 * @param array|string|Golem\iFace\Data\Options $options Filename for an options
	 *        file or array or Golem\iFace\Data\Options with values to override defaults.
	 *
	 * @throws \Exception When the input parameter is of wrong type.
	 * @throws \Exception When the filename passed is no existing file.
	 * @throws \Exception When the file passed does not parse correctly.
	 *
	 */
	public
	function __construct( $options = [] )
	{
		// TODO: Defaults should be moved to options class, which already supports it btw.

		$optionFile = new File( self::DEFAULT_OPTIONS_FILE );

		$this->defaultOptions = new GolemOptions( $optionFile->parse() );
		$this->options        = clone $this->defaultOptions;


		switch( Util::getType( $options ) )
		{
			case 'string'                           : $options = ( new File( $options ) )->parse();
			case 'array'                            : // fallthrough
			case 'Golem\Golem'                      : // fallthrough
			case 'Golem\Reference\Data\Options'     : $options = new GolemOptions( $options );

			case 'Golem\Reference\Data\GolemOptions': $this->clientOptions = $options;
			                                          $this->options->override( $this->clientOptions );
			                                          return;


			default: throw new Exception( "Cannot get valid options from a: " . Util::getType( $options ) );
		}
	}



	/**
	 * Create a logger object.
	 *
	 * @param array|Golem\iFace\Data\Options $options to override the defaults
	 *
	 * @throws \Exceptions On wrong parameter type
	 *
	 * @return Reference\Logger
	 *
	 */
	public
	function logger( $options = [] )
	{
		if( is_array( $options ) )

			$options = new LogOptions( $this, $options );


		if( ! $options instanceof iLogOptions )

			throw new Exception( "Invalid parameter type given to Golem::logger(). Got: " . Util::getType( $options ) );


		return new Logger( $options );
	}



	/**
	 * Provides a read only copy of the options object for the library.
	 *
	 * @return iFace\Data\Options The currently loaded library configuration.
	 *
	 */
	public
	function options()
	{
		$value = clone $this->options;
		$value->seal();

		return $value;
	}
}
