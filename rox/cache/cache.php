<?php
/**
 * Cache
 *
 * This Software is released under the MIT License.
 * See license.txt for more details.
 *
 * @package	rox
 * @author Ramon Torres
 * @copyright Copyright (c) 2008 Ramon Torres
 * @license http://roxphp.com/static/license.html
 * @link http://roxphp.com 
 * @access public
 */
class Cache {

	const ADAPTER_FILE = 'Cache_FileAdapter';
	const ADAPTER_MEMCACHE = 'Cache_MemcacheAdapter';

	private static $adapter;

	/**
	 * Initializes the cache
	 * 
	 * @param string $adapter
	 * @param array $options
	 * @return void
	 */
	public static function init($adapter, array $options) {
		self::loadAdapter($adapter);
		self::$adapter = new $adapter($options);
	}

	/**
	 * Load cache adapter class
	 * 
	 * @param string $name
	 * @throws Exception
	 */
	protected static function loadAdapter($name) {
		switch ($name) {
			case self::ADAPTER_MEMCACHE:
				require(ROX . 'cache' . DS . 'adapters' . DS . 'memcache.php');
			break;

			case self::ADAPTER_FILE:
				require(ROX . 'cache' . DS . 'adapters' . DS . 'file.php');
			break;

			default: throw new Exception('Invalid Cache adapter');
		}
	}

	/**
	 * Cache::write()
	 * 
	 * @param mixed $key
	 * @param mixed $data
	 * @param mixed $expires
	 * @return mixed
	 */
	public static function write($key, $data, $expires) {
		return self::$adapter->write($key, $data, $expires);
	}

	/**
	 * Cache::read()
	 * 
	 * @param mixed $key
	 * @return boolean
	 */
	public static function read($key) {
		return self::$adapter->read($key);
	}

	/**
	 * Cache::delete()
	 * 
	 * @param mixed $key
	 * @return boolean
	 */
	public static function delete($key) {
		return self::$adapter->delete($key);
	}
}