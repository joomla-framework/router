<?php
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router\Tests;

use Joomla\Router\Exception\RouteNotFoundException;
use Joomla\Router\Route;
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
		$this->assertAttributeEmpty(
			'routes',
			new Router,
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
			new Route(['GET'], 'login', 'login', [], []),
			new Route(['GET'], 'requests/:request_id', 'request', ['request_id' => '(\d+)'], []),
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
		$route = new Route(['GET'], 'foo', 'MyApplicationFoo', [], []);

		$this->instance->addRoute($route);

		$this->assertAttributeEquals(
			array(
				$route,
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
		$route = new Route(['GET'], 'foo', 'MyApplicationFoo', [], ['default1' => 'foo']);

		$this->instance->addRoute($route);

		$this->assertAttributeEquals(
			array(
				$route,
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
			new Route(['GET'], 'login', 'login', [], []),
			new Route(['GET'], 'user/:name/:id', 'UserController', ['id' => '(\d+)'], []),
			new Route(['GET'], 'requests/:request_id', 'request', ['request_id' => '(\d+)'], []),
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
			$this->expectException(RouteNotFoundException::class);
		}

		// Execute the route parsing.
		$actual = $this->instance->parseRoute($r);

		// Test the assertions.
		$this->assertSame($i['controller'], $actual->getController());
		$this->assertEquals($i['vars'], $actual->getRouteVariables());
	}

	/**
	 * @testdox  Ensure the Router handles a method not allowed error correctly.
	 *
	 * @covers   Joomla\Router\Router::parseRoute
	 * @uses     Joomla\Router\Router::get
	 *
	 * @expectedException  Joomla\Router\Exception\MethodNotAllowedException
	 * @expectedExceptionMessage  Route `test/foo/path/bar` does not support `POST` requests.
	 */
	public function testParseRouteWithMethodNotAllowedError()
	{
		$this->instance->get('test/foo/path/bar', 'TestController');

		// Execute the route parsing.
		$this->instance->parseRoute('test/foo/path/bar', 'POST');
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
