<?php
/**
 * Cache_MemcacheAdapter
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
class Cache_MemcacheAdapter {

	protected $servers = array('localhost' => 11211);
	protected $Memcache;

	/**
	 * Constructor
	 *
	 * @param mixed $options
	 */
	public function __construct($options) {
		if (isset($options['servers'])) {
			$this->setServers($options['servers']);
		}

		$this->Memcache = new Memcache;
		$this->connect();
	}

	/**
	 * Cache_MemcacheAdapter::setServers()
	 *
	 * @param mixed $servers
	 */
	protected function setServers($servers) {
		$this->servers = $servers;
	}

	/**
	 * Cache_MemcacheAdapter::connect()
	 */
	protected function connect() {
		foreach($this->servers as $host => $port) {
			$this->Memcache->addServer($host, $port);
		}
	}

	/**
	 * Cache_MemcacheAdapter::write()
	 *
	 * @param mixed $key
	 * @param mixed $data
	 * @param mixed $expires
	 * @return
	 */
	public function write($key, &$data, $expires) {
		if (is_string($expires)) {
			$expires = strtotime($expires);
		} else {
			$expires = time()+$expires;
		}

		return $this->Memcache->set($key, $data, 0, $expires);
	}

	/**
	 * Cache_MemcacheAdapter::read()
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function read($key) {
		return $this->Memcache->get($key);
	}

	/**
	 * Cache_MemcacheAdapter::delete()
	 * 
	 * @param mixed $key
	 * @return boolean
	 */
	public function delete($key) {
		return $this->Memcache->delete($key);
	}
}