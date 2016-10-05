<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/Kohana/Core'.EXT;

if (is_file(APPPATH.'classes/Kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/Kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/Kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Asia/Novosibirsk');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'ru_RU.utf-8');

/**
 * Enable Composer auto-loader.
 *
 * @link https://getcomposer.org/doc/00-intro.md#autoloading
 */
require __DIR__.'/../vendor/autoload.php';

/**
 * Enable the Kohana auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
$modules = [
  'application' => APPPATH,                         // Main application module
  'auth'        => $vendor_path.'kohana/auth',      // Basic authentication
  'cache'       => $vendor_path.'kohana/cache',     // Caching with multiple backends
  //'codebench'   => $vendor_path.'kohana/codebench', // Benchmarking tool
  'database'    => $vendor_path.'kohana/database',  // Database access
  //'image'       => $vendor_path.'kohana/image',     // Image manipulation
  'minion'      => $vendor_path.'kohana/minion',    // CLI Tasks
  'orm'         => $vendor_path.'kohana/orm',       // Object Relationship Mapping
  //'unittest'    => $vendor_path.'kohana/unittest',  // Unit testing
  'kostache'      => $vendor_path.'zombor/kostache', // Logic-less Mustache views
  'email'         => $vendor_path.'tscms/email',// Electronic mail class
  'debug-toolbar' => MODPATH.'debug-toolbar',     // Debug toolbar
  'config-writer' => MODPATH.'config-writer',     // Write to PHP configs
  'migrations'    => MODPATH.'migrations',        // SQL migrations
  'core'        => SYSPATH,                         // Core system
];
if (Kohana::$environment === Kohana::DEVELOPMENT)
{
  $modules['userguide'] = $vendor_path.'kohana/userguide'; // User guide and API documentation
}
Kohana::modules($modules);
unset($modules);
/**
 * Set the default language
 */
I18n::lang('ru');

if ( ! function_exists('__'))
{
  /**
   * I18n translate alias function.
   *
   * @deprecated 3.4 Use I18n::translate() instead
   */
  function __($string, array $values = NULL, $lang = 'en-us')
  {
    return I18n::translate($string, $values, $lang);
  }
}

if (isset($_SERVER['SERVER_PROTOCOL']))
{
	// Replace the default protocol.
	HTTP::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
  Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
  'base_url'   => '/',
  'index_file' => false,
  'errors'     => TRUE,
  'profile'    => (Kohana::$environment == Kohana::DEVELOPMENT), 
  'caching'    => (Kohana::$environment == Kohana::PRODUCTION) 
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

// Initialize modules
Kohana::init_modules();

/**
 * Set cookie salt (required)
 */
Cookie::$salt = 'YehsmJK:*$jel_@dj';

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('minion', 'minion(/<action>)', array('action' => '.+'))
  ->defaults(array( 
    'controller' => 'Minion',
  ));

Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++','message' => '.+'))
  ->defaults(array( 
    'controller' => 'Error',
  ));

Route::set('default', '(<controller>(/<action>(/<id>)(/page/<page>)))')
  ->defaults(array(
    'controller' => 'User',
    'action'     => 'signin',
  ));
