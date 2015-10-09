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

require_once __DIR__ . '/Reference/Traits/Autoload.php';

use

	  Golem\Reference\Logger
	, Golem\Reference\Randomizer
   , Golem\Reference\Util
   , Golem\Reference\Encoder
   , Golem\Reference\Sanitizer
   , Golem\Reference\Validator

   , Golem\Reference\Data\File
   , Golem\Reference\Data\String

   , Golem\Reference\Traits\Seal
   , Golem\Reference\Traits\HasOptions

   , \Exception
   , \InvalidArgumentException
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
	 * @param array|string|Golem/Golem $options Filename for an options
	 *        file or array or Golem/Golem with values to override defaults.
	 *
	 * @throws \Exception When the input parameter is of wrong type.
	 * @throws \Exception When the filename passed is no existing file.
	 * @throws \Exception When the file passed does not parse correctly.
	 *
	 */
	public
	function __construct( $options = [] )
	{
		$defaultsFile   = new File( $this, self::DEFAULT_OPTIONS_FILE );
		$defaults = $defaultsFile->parse();

		switch( Util::getType( $options ) )
		{
			case 'string'                           : $options = ( new File( $this, $options ) )->parse();
			case 'array'                            : break;

			case 'Golem\Golem'                      : $options = $options->options;
			                                          break;

			default: throw new InvalidArgumentException( "Cannot get valid options from a: " . Util::getType( $options ) );
		}


		$this->setupOptions( $defaults, $options );
	}



	/**
	 * Get a \Golem\Reference\logger.
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
	 * @return \Golem\Reference\logger The logger with the given name, if it doesn't exist, it will be created.
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
			$options[ 'name' ] = $name;
			$this->loggers[ $name ] = new Logger( $this, $options );
		}


		elseif( ! empty( $options ) )

			$this->logger()->exception( "Cannot override options on existing logger: [$name]" );


		return $this->loggers[ $name ];
	}



	/**
	 * Get a \Golem\Reference\Randomizer.
	 *
	 * @return \Golem\Reference\Randomizer
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
	 * Get a \Golem\Reference\Encoder.
	 *
	 * @return \Golem\Reference\Encoder
	 *
	 * @api
	 *
	 */
	public
	function encoder( $options = [] )
	{
		if( ! $this->encoder )

			$this->encoder = new Encoder( $this, $options );


		return $this->encoder;
	}



	/**
	 * Get a \Golem\Reference\Sanitizer.
	 *
	 * @return \Golem\Reference\Sanitizer
	 *
	 * @api
	 *
	 */
	public
	function sanitizer( $options = [] )
	{
		if( ! $this->sanitizer )

			$this->sanitizer = new Sanitizer( $this, $options );


		return $this->sanitizer;
	}



	/**
	 * Get a \Golem\Reference\Validator.
	 *
	 * @return \Golem\Reference\Validator
	 *
	 * @api
	 *
	 */
	public
	function validator( $options = [] )
	{
		if( ! $this->validator )

			$this->validator = new Validator( $this, $options );


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
}
