<?php
/**
 * The main interface to the library.
 *
 */

/**
 * The toplevel namespace for the library.
 *
 */
namespace Golem;

require_once __DIR__ . '/Traits/Autoload.php';

use

	  Golem\Logger
	, Golem\Randomizer
   , Golem\Util
   , Golem\Encoder
   , Golem\Sanitizer
   , Golem\Validator

   , Golem\Codecs\ParseYAML

   , Golem\Validation\StringRule
   , Golem\Validation\NumberRule

   , Golem\Data\File
   , Golem\Data\String

   , Golem\Traits\Seal
   , Golem\Traits\HasOptions

   , Exception
   , RuntimeException
   , InvalidArgumentException
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
	use Seal, HasOptions;


	/**
	 * @var string The file with default options.
	 *
	 */
	const DEFAULT_OPTIONS_FILE = PHPDOCBUG;


	/**
	 * @var array Keep track of the loggers managed by this library object.
	 *
	 */
	protected $loggers     = [];
	protected $randomizer      ;
	protected $encoder         ;
	protected $validator       ;
	protected $sanitizer       ;


	/**
	 * Create a library object.
	 *
	 * You should usuall create one and pass it round in your application.
	 * You can seal this object to prevent changes to security configuration
	 * to be made by code included later in your application. See the documentation on sealing
	 * for limitations of this approach.
	 *
	 * @param array|string $options Filename for an options
	 *        file or array with values to override the defaults.
	 *
	 * @throws \Exception When the input parameter is of wrong type.
	 * @throws \Exception When the filename passed is no existing file.
	 * @throws \Exception When the file     passed does not parse correctly.
	 *
	 */
	public
	function __construct( $options = [] )
	{
		// parameter validation
		//
		if( ! file_exists( self::DEFAULT_OPTIONS_FILE ) )

			throw new RuntimeException( "Cannot open options file: " . self::DEFAULT_OPTIONS_FILE );


		if( is_string( $options ) && ! file_exists( $options ) )

			throw new RuntimeException( "Cannot open options file: " . $options );


		$parser   = new ParseYAML;
		$defaults = $parser->decode( file_get_contents( self::DEFAULT_OPTIONS_FILE ) );


		switch( Util::getType( $options ) )
		{
			case 'string' : $options = $parser->decode( file_get_contents( $options ) );
			case 'array'  : break;

			default: throw new InvalidArgumentException( "Cannot get valid options from a: " . Util::getType( $options ) );
		}


		$this->setupOptions( $defaults, $options );
	}



	/**
	 * Get a \Golem\logger.
	 *
	 * @param string $name    The name of the logger. If you request a logger with an existing name,
	 *                        a new logger will not be created, you will get the named logger.
	 *
	 * @param array  $options Options to override defaults for the logger. This parameter is not supported
	 *                        when asking for an existing logger by passing an existing name as $name.
	 *
	 * @todo figure out if it is a good idea to have named loggers. Even though the golem object needs
	 *       to be instantiated, this still resembles static access, just by having a name. This can be
	 *       a security issue because all code with access to the golem object necessarily has access
	 *       to all loggers of that golem object with a predictable name.
	 *
	 * @throws \Exception When trying to pass options for an existing logger.
	 *
	 * @return \Golem\logger The logger with the given name, if it doesn't exist, it will be created.
	 *
	 * @api
	 *
	 */
	public
	function logger( $name = null, array $options = [] )
	{
		if( $name === null )

			$name = $this->options[ 'Logger' ][ 'name' ];


		if( ! isset( $this->loggers[ $name ] ) )
		{
			$options[ 'name' ]      = $name;
			$this->loggers[ $name ] = new Logger( $this, $options );
		}


		elseif( $options )

			$this->logger()->exception( "Cannot override options on existing logger: [$name]" );


		return $this->loggers[ $name ];
	}



	/**
	 * Get a \Golem\Randomizer.
	 *
	 * @return \Golem\Randomizer
	 *
	 * @api
	 *
	 */
	public
	function randomizer()
	{
		if( ! $this->randomizer )

			$this->randomizer = new Randomizer( $this );


		return $this->randomizer;
	}



	/**
	 * Get a \Golem\Encoder.
	 *
	 * @return \Golem\Encoder
	 *
	 * @api
	 *
	 */
	public
	function encoder()
	{
		if( ! $this->encoder )

			$this->encoder = new Encoder( $this );


		return $this->encoder;
	}



	/**
	 * Get a \Golem\Validator.
	 *
	 * @return \Golem\Validator
	 *
	 * @api
	 *
	 */
	public
	function validator()
	{
		if( ! $this->validator )

			$this->validator = new Validator( $this );


		return $this->validator;
	}



	/**
	 * Get a \Golem\Data\String.
	 *
	 * @return \Golem\Data\String
	 *
	 * @api
	 *
	 */
	public
	function string( $content, $encoding = null )
	{
		if( $encoding === null )

			$encoding = $this->options( 'String', 'encoding' );


		return new String( $this, $content, [ 'encoding' => $encoding ] );
	}



	/**
	 * Get a \Golem\Data\File.
	 *
	 * @return \Golem\Data\File
	 *
	 * @api
	 *
	 */
	public
	function file( $path )
	{
		return new File( $this, $path );
	}


	public function stringRule( array $options = [] ){ return new StringRule( $this, $options ); }
	public function numberRule( array $options = [] ){ return new NumberRule( $this, $options ); }
}
