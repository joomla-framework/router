<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router\Tests;

use Joomla\Router\Router;
use Joomla\Test\TestHelper;

/**
 * Tests for the Joomla\Router\Router class.
 *
 * @since  1.0
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * An instance of the object to be tested.
	 *
	 * @var    Router
	 * @since  1.0
	 */
	protected $instance;

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new Router;
	}

	/**
	 * Tests the Joomla\Router\Router::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Router\Router::__construct
	 * @since   1.0
	 */
	public function test__construct()
	{
		$this->assertAttributeEmpty('maps', $this->instance);
	}

	/**
	 * Tests the Joomla\Router\Router::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Router\Router::__construct
	 * @since   1.0
	 */
	public function test__constructNotEmpty()
	{
		$maps = array(
			array(
				'pattern' => 'login',
				'controller' => 'login'
			),
			array(
				'pattern' => 'requests/:request_id',
				'controller' => 'request',
				'rules' => array(
					'request_id' => '(\d+)'
				)
			)
		);

		$rules = array(
			array(
				'regex' => chr(1) . '^login$' . chr(1),
				'vars' => array(),
				'controller' => 'login'
			),
			array(
				'regex' => chr(1) . '^requests/(\d+)$' . chr(1),
				'vars' => array('request_id'),
				'controller' => 'request'
			)
		);

		$router = new Router($maps);

		$this->assertAttributeEquals(
			 $rules,
				 'maps',
				 $router,
				 'When passing an array of routes when instantiating a Router, the maps property should be set accordingly.'
		);
	}

	/**
	 * Tests the Joomla\Router\Router::addMap method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Router\Router::addMap
	 * @since   1.0
	 */
	public function testAddMap()
	{
		$this->assertAttributeEmpty('maps', $this->instance);
		$this->instance->addMap('foo', 'MyApplicationFoo');
		$this->assertAttributeEquals(
			 array(
				 array(
					 'regex' => chr(1) . '^foo$' . chr(1),
					 'vars' => array(),
					 'controller' => 'MyApplicationFoo'
				 )
			 ),
				 'maps',
				 $this->instance
		);
	}

	/**
	 * Tests the Joomla\Router\Router::addMaps method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Router\Router::addMaps
	 * @since   1.0
	 */
	public function testAddMaps()
	{
		$maps = array(
			array(
				'pattern' => 'login',
				'controller' => 'login'
			),
			array(
				'pattern' => 'user/:name/:id',
				'controller' => 'UserController',
				'rules' => array(
					'id' => '(\d+)'
				)
			),
			array(
				'pattern' => 'requests/:request_id',
				'controller' => 'request',
				'rules' => array(
					'request_id' => '(\d+)'
				)
			)
		);

		$rules = array(
			array(
				'regex' => chr(1) . '^login$' . chr(1),
				'vars' => array(),
				'controller' => 'login'
			),
			array(
				'regex' => chr(1) . '^user/([^/]*)/(\d+)$' . chr(1),
				'vars' => array(
					'name',
					'id'
				),
				'controller' => 'UserController'
			),
			array(
				'regex' => chr(1) . '^requests/(\d+)$' . chr(1),
				'vars' => array('request_id'),
				'controller' => 'request'
			)
		);

		$this->assertAttributeEmpty('maps', $this->instance);
		$this->instance->addMaps($maps);
		$this->assertAttributeEquals($rules, 'maps', $this->instance);
	}

	/**
	 * Tests the Joomla\Router\Router::parseRoute method.
	 *
	 * @param   string   $r  The route to parse.
	 * @param   boolean  $e  True if an exception is expected.
	 * @param   array    $i  The expected return data.
	 * @param   integer  $m  The map set to use for setting up the router.
	 *
	 * @return  void
	 *
	 * @covers        Joomla\Router\Router::parseRoute
	 * @dataProvider  seedTestParseRoute
	 * @since         1.0
	 */
	public function testParseRoute($r, $e, $i, $m)
	{
		// Setup the router maps.
		$this->{'setMaps' . $m}();

		// If we should expect an exception set that up.
		if ($e)
		{
			$this->setExpectedException('InvalidArgumentException');
		}

		// Execute the route parsing.
		$actual = TestHelper::invoke($this->instance, 'parseRoute', $r);

		// Test the assertions.
		$this->assertEquals($i, $actual, 'Incorrect value returned.');
	}

	/**
	 * Provides test data for the testParseRoute method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function seedTestParseRoute()
	{
		// Route Pattern, Throws Exception, Return Data, MapSetup
		return array(
			array('', true, array(), 1),
			array('articles/4', true, array(), 1),
			array('', true, array(), 2),
			array('login', false, array('controller' => 'LoginController', 'vars' => array('_rawRoute' => 'login')), 2),
			array('articles', false, array('controller' => 'ArticlesController', 'vars' => array('_rawRoute' => 'articles')), 2),
			array('articles/4', false, array('controller' => 'ArticleController', 'vars' => array('article_id' => 4, '_rawRoute' => 'articles/4')), 2),
			array('articles/4/crap', true, array(), 2),
			array('test', true, array(), 2),
			array('test/foo', true, array(), 2),
			array('test/foo/path', true, array(), 2),
			array('test/foo/path/bar', false, array('controller' => 'TestController', 'vars' => array('seg1' => 'foo', 'seg2' => 'bar', '_rawRoute' => 'test/foo/path/bar')), 2),
			array('content/article-1/*', false, array('controller' => 'ContentController', 'vars' => array('_rawRoute' => 'content/article-1/*')), 2),
			array('content/cat-1/article-1', false,
				array('controller' => 'ArticleController', 'vars' => array('category' => 'cat-1', 'article' => 'article-1', '_rawRoute' => 'content/cat-1/article-1')), 2),
			array('content/cat-1/cat-2/article-1', false,
				array('controller' => 'ArticleController', 'vars' => array('category' => 'cat-1/cat-2', 'article' => 'article-1', '_rawRoute' => 'content/cat-1/cat-2/article-1')), 2),
			array('content/cat-1/cat-2/cat-3/article-1', false,
				array('controller' => 'ArticleController', 'vars' => array('category' => 'cat-1/cat-2/cat-3', 'article' => 'article-1', '_rawRoute' => 'content/cat-1/cat-2/cat-3/article-1')), 2)
		);
	}

	/**
	 * Setup the router maps to option 1.
	 *
	 * This has no routes but has a default controller for the home page.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setMaps1()
	{
		$this->instance->addMaps(array());
	}

	/**
	 * Setup the router maps to option 2.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setMaps2()
	{
		$this->instance->addMaps(
			array(
				array(
				   'pattern' => 'login',
				   'controller' => 'LoginController'
				),
				array(
				   'pattern' => 'logout',
				   'controller' => 'LogoutController'
				),
				array(
				   'pattern' => 'articles',
				   'controller' => 'ArticlesController'
				),
				array(
				   'pattern' => 'articles/:article_id',
				   'controller' => 'ArticleController'
				),
				array(
				   'pattern' => 'test/:seg1/path/:seg2',
				   'controller' => 'TestController'
				),
				array(
				   'pattern' => 'content/:/\*',
				   'controller' => 'ContentController'
				),
				array(
				   'pattern' => 'content/*category/:article',
				   'controller' => 'ArticleController'
				)
			)
		);
	}
}
