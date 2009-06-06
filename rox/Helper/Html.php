<?php
/**
 * RoxPHP
 *
 * Copyright (C) 2008 - 2009 Ramon Torres
 *
 * This Software is released under the MIT License.
 * See license.txt for more details.
 *
 * @package Rox
 * @author Ramon Torres
 * @copyright Copyright (c) 2008 - 2009 Ramon Torres (http://roxphp.com)
 * @license http://roxphp.com/static/license.html
 * @version $Id$
 */

/**
 * HTML Helper
 *
 * @package Rox
 * @copyright Copyright (c) 2008 - 2009 Ramon Torres
 * @license http://roxphp.com/static/license.html
 */
class Rox_Helper_Html {

	/**
	 * Rox_Helper_Html::image()
	 *
	 * @param string $path
	 * @param string $alt
	 * @param array $attributes
	 * @return string
	 */
	public function image($path, $alt = '', $attributes = array()) {
		$result = sprintf('<img src="%s" alt="%s"%s />', Router::url('/img/' . $path), $alt,
			self::_makeAttributes($attributes));
		return $result;
	}

	/**
	 * Alias for Rox_Helper_Html::image()
	 *
	 * @param string $path
	 * @param string $alt
	 * @return string
	 */
	public function img($path, $alt = '', $attributes = array()) {
		return $this->image($path, $alt);
	}

	/**
	 * HtmlHelper::css()
	 *
	 * @param mixed $file
	 * @return string
	 */
	public function css($file, $media = 'all') {
		$output = sprintf('<link rel="stylesheet" type="text/css" href="%s" media="%s" />',
			Router::url('/css/' . $file . '.css'), $media);
		return $output;
	}

	/**
	 * undocumented function
	 *
	 * @param string $text 
	 * @param string $path 
	 * @param array $attributes 
	 * @return string
	 */
	public function link($text, $path, $attributes = array()) {
		$output = sprintf('<a href="%s"%s>%s</a>', Router::url($path), $text,
			self::_makeAttributes($attributes));
		return $output;
	}

	/**
	 * undocumented function
	 *
	 * @param array $attributes 
	 * @return string
	 */
	protected static function _makeAttributes(array $attributes) {
		$result = array();
		foreach ($attributes as $name => $value) {
			$result[] = ' ' . $name . '="' . $value . '"';
		}
		return implode('', $result);
	}
}