<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router\Tests;

use Joomla\Router\Router;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Joomla\Router\Router class.
 */
class RouterTest extends TestCase
{
	/**
	 * An instance of the object to be tested.
	 *
	 * @var  Router
	 */
	protected $instance;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new Router;
	}

	/**
	 * @testdox  Ensure the Router is instantiated correctly with no injected routes.
	 *
	 * @covers   Joomla\Router\Router::__construct
	 */
	public function test__construct()
	{
		$emptyRoutes = array(
			'GET' => array(),
			'PUT' => array(),
			'POST' => array(),
			'DELETE' => array(),
			'HEAD' => array(),
			'OPTIONS' => array(),
			'TRACE' => array(),
			'PATCH' => array()
		);

		$router = new Router;

		$this->assertAttributeEquals(
			$emptyRoutes,
			'routes',
			$router,
			'A Router should have no known routes by default.'
		);
	}

	/**
	 * @testdox  Ensure the Router is instantiated correctly with injected routes.
	 *
	 * @covers   Joomla\Router\Router::__construct
	 * @uses     Joomla\Router\Router::addRoutes
	 */
	public function test__constructNotEmpty()
	{
		$routes = array(
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
			'GET' => array(
				array(
					'regex' => chr(1) . '^login$' . chr(1),
					'vars' => array(),
					'controller' => 'login',
					'defaults' => array()
				),
				array(
					'regex' => chr(1) . '^requests/((\d+))$' . chr(1),
					'vars' => array('request_id'),
					'controller' => 'request',
					'defaults' => array()
				)
			),
			'PUT' => array(),
			'POST' => array(),
			'DELETE' => array(),
			'HEAD' => array(),
			'OPTIONS' => array(),
			'TRACE' => array(),
			'PATCH' => array()
		);

		$router = new Router($routes);

		$this->assertAttributeEquals(
			$rules,
			'routes',
			$router,
			'When passing an array of routes when instantiating a Router, the maps property should be set accordingly.'
		);
	}

	/**
	 * @testdox  Ensure a route is added to the Router.
	 *
	 * @covers   Joomla\Router\Router::addRoute
	 * @uses     Joomla\Router\Router::buildRegexAndVarList
	 */
	public function testAddRoute()
	{
		$this->instance->addRoute('GET', 'foo', 'MyApplicationFoo');

		$this->assertAttributeEquals(
			array(
				'GET' => array(
					array(
						'regex' => chr(1) . '^foo$' . chr(1),
						'vars' => array(),
						'controller' => 'MyApplicationFoo',
						'defaults' => array()
					)
				),
				'PUT' => array(),
				'POST' => array(),
				'DELETE' => array(),
				'HEAD' => array(),
				'OPTIONS' => array(),
				'TRACE' => array(),
				'PATCH' => array()
			),
			'routes',
			$this->instance
		);
	}

	/**
	 * @testdox  Ensure a route is added to the Router.
	 *
	 * @covers   Joomla\Router\Router::addRoute
	 */
	public function testAddRouteWithDefaults()
	{
		$this->instance->addRoute('GET', 'foo', 'MyApplicationFoo', [], ['default1' => 'foo']);

		$this->assertAttributeEquals(
			array(
				'GET' => array(
					array(
						'regex' => chr(1) . '^foo$' . chr(1),
						'vars' => array(),
						'controller' => 'MyApplicationFoo',
						'defaults' => ['default1' => 'foo']
					)
				),
				'PUT' => array(),
				'POST' => array(),
				'DELETE' => array(),
				'HEAD' => array(),
				'OPTIONS' => array(),
				'TRACE' => array(),
				'PATCH' => array()
			),
			'routes',
			$this->instance
		);
	}

	/**
	 * @testdox  Ensure several routes are added to the Router.
	 *
	 * @covers   Joomla\Router\Router::addRoutes
	 * @uses     Joomla\Router\Router::addRoute
	 */
	public function testAddRoutes()
	{
		$routes = array(
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
			'GET' => array(
				array(
					'regex' => chr(1) . '^login$' . chr(1),
					'vars' => array(),
					'controller' => 'login',
					'defaults' => array()
				),
				array(
					'regex' => chr(1) . '^user/([^/]*)/((\d+))$' . chr(1),
					'vars' => array(
						'name',
						'id'
					),
					'controller' => 'UserController',
					'defaults' => array()
				),
				array(
					'regex' => chr(1) . '^requests/((\d+))$' . chr(1),
					'vars' => array('request_id'),
					'controller' => 'request',
					'defaults' => array()
				)
			),
			'PUT' => array(),
			'POST' => array(),
			'DELETE' => array(),
			'HEAD' => array(),
			'OPTIONS' => array(),
			'TRACE' => array(),
			'PATCH' => array()
		);

		$this->instance->addRoutes($routes);
		$this->assertAttributeEquals($rules, 'routes', $this->instance);
	}

	/**
	 * @testdox  Ensure the Router parses routes.
	 *
	 * @param   string   $r  The route to parse.
	 * @param   boolean  $e  True if an exception is expected.
	 * @param   array    $i  The expected return data.
	 * @param   boolean  $m  True if routes should be set up.
	 *
	 * @covers        Joomla\Router\Router::parseRoute
	 * @dataProvider  seedTestParseRoute
	 * @uses          Joomla\Router\Router::addRoutes
	 */
	public function testParseRoute($r, $e, $i, $m)
	{
		if ($m)
		{
			$this->setRoutes();
		}

		// If we should expect an exception set that up.
		if ($e)
		{
			// expectException was added in PHPUnit 5.2 and setExpectedException removed in 6.0
			if (method_exists($this, 'expectException'))
			{
				$this->expectException('InvalidArgumentException');
			}
			else
			{
				$this->setExpectedException('InvalidArgumentException');
			}
		}

		// Execute the route parsing.
		$actual = $this->instance->parseRoute($r);

		// Test the assertions.
		$this->assertEquals($i, $actual, 'Incorrect value returned.');
	}

	/**
	 * Provides test data for the testParseRoute method.
	 *
	 * @return  array
	 */
	public static function seedTestParseRoute()
	{
		// Route Pattern, Throws Exception, Return Data, MapSetup
		return array(
			array('', true, array(), false),
			array('articles/4', true, array(), false),
			array('', false, array('controller' => 'DefaultController', 'vars' => array()), true),
			array('login', false, array('controller' => 'LoginController', 'vars' => array()), true),
			array('articles', false, array('controller' => 'ArticlesController', 'vars' => array()), true),
			array('articles/4', false, array('controller' => 'ArticleController', 'vars' => array('article_id' => 4)), true),
			array('articles/4/crap', true, array(), true),
			array('test', true, array(), true),
			array('test/foo', true, array(), true),
			array('test/foo/path', true, array(), true),
			array('test/foo/path/bar', false, array('controller' => 'TestController', 'vars' => array('seg1' => 'foo', 'seg2' => 'bar')), true),
			array('content/article-1/*', false, array('controller' => 'ContentController', 'vars' => array()), true),
			array(
				'content/cat-1/article-1',
				false,
				array('controller' => 'ArticleController', 'vars' => array('category' => 'cat-1', 'article' => 'article-1')),
				true
			),
			array(
				'content/cat-1/cat-2/article-1',
				false,
				array('controller' => 'ArticleController', 'vars' => array('category' => 'cat-1/cat-2', 'article' => 'article-1')),
				true
			),
			array(
				'content/cat-1/cat-2/cat-3/article-1',
				false,
				array('controller' => 'ArticleController', 'vars' => array('category' => 'cat-1/cat-2/cat-3', 'article' => 'article-1')),
				true
			),
			array(
				'default_option/4',
				false,
				array('controller' => 'ArticleController', 'vars' => array('article_id' => 4, 'option' => 'content')),
				true
			),
			array(
				'overriden_option/article/4',
				false,
				array('controller' => 'ArticleController', 'vars' => array('id' => 4, 'option' => 'content', 'view' => 'article')),
				true
			),
		);
	}

	/**
	 * Setup the router with routes.
	 *
	 * @return  void
	 */
	protected function setRoutes()
	{
		$this->instance->addRoutes(
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
				),
				array(
					'pattern' => '/',
					'controller' => 'DefaultController'
				),
				array(
					'pattern' => 'default_option/:article_id',
					'controller' => 'ArticleController',
					'defaults' => [
						'option' => 'content'
					]
				),
				array(
					'pattern' => 'overriden_option/:view/:id',
					'controller' => 'ArticleController',
					'defaults' => [
						'option' => 'content',
						'view' => 'category'
					]
				),
			)
		);
	}
}
