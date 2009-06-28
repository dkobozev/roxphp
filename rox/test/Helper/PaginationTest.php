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
 * @see Rox_Helper_Pagination
 */
require_once '../../Helper/Pagination.php';

/**
 * @see Rox_ActiveRecord_PaginationResult
 */
require_once '../../ActiveRecord/PaginationResult.php';

/**
 * Test case for Pagination Helper
 *
 * @package Rox_Test
 */
class Rox_Helper_PaginationTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests the Rox_Helper_Pagination::links() method
	 *
	 * @return void
	 */
	public function testLinks() {
		/*
		   collection    = empty Array
		   pages         = 3
		   current page  = 1
		   next page     = 2
		   previous page = 1
		 */
		$paginationResult = new Rox_ActiveRecord_PaginationResult(array(), 3, 1, 2, 1);
		$paginationHelper = new Rox_Helper_Pagination;

		$result   = $paginationHelper->links($paginationResult);
		$expected = '<div class="pagination"><span class="current">1</span> <a href="?page=2">2</a> <a href="?page=3">3</a> <a href="?page=2">Next &raquo;</a></div>';

		$this->assertEquals($result, $expected);
	}

	/**
	 * Tests the Rox_Helper_Pagination::links() method with options
	 *
	 * @return void
	 */
	public function testLinksWithOptions() {
		/*
		   collection    = empty Array
		   pages         = 20
		   current page  = 3
		   next page     = 4
		   previous page = 2
		 */
		$paginationResult = new Rox_ActiveRecord_PaginationResult(array(), 20, 3, 4, 2);
		$paginationHelper = new Rox_Helper_Pagination;

		$result = $paginationHelper->links($paginationResult, array(
			'class'          => 'my-custom-pagination-class',
			'previous_label' => 'My Prev Label',
			'next_label'     => 'My Next Label',
			'max_items'      => 10
		));

		$matcher = array(
			'tag' => 'div',
			'attributes' => array(
				'class' => 'my-custom-pagination-class'
			)
		);

		$this->assertTag($matcher, $result);
	
		// Next page link
		$matcher = array(
			'tag' => 'a',
			'parent' => array('tag' => 'div'),
			'content' => 'My Prev Label'
		);

		$this->assertTag($matcher, $result);

		// Previous page link
		$matcher = array(
			'tag' => 'a',
			'parent' => array('tag' => 'div'),
			'content' => 'My Next Label'
		);

		$this->assertTag($matcher, $result);

		// Last page link
		$matcher = array(
			'tag' => 'a',
			'parent' => array('tag' => 'div'),
			'content' => '20',
			'attributes' => array(
				'href' => '?page=20'
			)
		);

		$this->assertTag($matcher, $result);

		$matcher = array(
			'tag' => 'div',
			'child' => array(
				'tag'     => 'span',
				'content' => '...',
			)
		);

		$this->assertTag($matcher, $result);

		$matcher = array(
			'tag' => 'a',
			'content' => '11'
		);

		$this->assertNotTag($matcher, $result);
	}
}