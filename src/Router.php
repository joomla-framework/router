<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * A path router.
 *
 * @since  1.0
 */
class Router implements \Serializable
{
	/**
	 * An array of rules, each rule being an associative for routing the request.
	 *
	 * Example: array(
	 *              'regex' => $regex,
	 *              'vars' => $vars,
	 *              'controller' => $controller
	 *          )
	 *
	 * @var    array
	 * @since  1.0
	 */
	public $routes = array(
		'GET' => array(),
		'PUT' => array(),
		'POST' => array(),
		'DELETE' => array(),
		'HEAD' => array(),
		'OPTIONS' => array(),
		'TRACE' => array(),
		'PATCH' => array()
	);

	/**
	 * Constructor.
	 *
	 * @param   array  $maps  An optional array of route maps
	 *
	 * @since   1.0
	 */
	public function __construct(array $maps = array())
	{
		if (! empty($maps))
		{
			$this->addRoutes($maps);
		}
	}

	/**
	 * Add a route of the specified method to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $method      Request method to match. One of GET, POST, PUT, DELETE, HEAD, OPTIONS, TRACE or PATCH
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the named route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function addRoute($method, $pattern, $controller, array $rules = array())
	{
		list($regex, $vars) = $this->buildRegexAndVarList($pattern, $rules);

		$this->routes[strtoupper($method)][] = array(
			'regex' => $regex,
			'vars' => $vars,
			'controller' => $controller
		);

		return $this;
	}

	/**
	 * Parse the given pattern to extract the named variables and build
	 * a proper regular expression for use when parsing the routes.
	 *
	 * @param   string  $pattern  The route pattern to use for matching.
	 * @param   array   $rules    An array of regex rules keyed using the named route variables.
	 *
	 * @return  array
	 */
	protected function buildRegexAndVarList($pattern, array $rules = array())
	{
		// Sanitize and explode the pattern.
		$pattern = explode('/', trim(parse_url((string) $pattern, PHP_URL_PATH), ' /'));

		// Prepare the route variables
		$vars = array();

		// Initialize regular expression
		$regex = array();

		// Loop on each segment
		foreach ($pattern as $segment)
		{
			if ($segment[0] == ':')
			{
				// Match a named variable and capture the data.
				$varName = substr($segment, 1);
				$vars[] = $varName;
				// Use the regex in the rules array if it has been defined.
				$regex[] = array_key_exists($varName, $rules) ? '(' . $rules[$varName] . ')' : '([^/]*)';
			}
			else
			{
				// Match the standard segment.
				$regex[] = preg_quote($segment);
			}
		}

		return array(
			chr(1) . '^' . implode('/', $regex) . '$' . chr(1),
			$vars
		);
	}

	/**
	 * Add an array of route maps to the router.  If the pattern already exists it will be overwritten.
	 *
	 * @param   array  $routes  A list of route maps to add to the router as $pattern => $controller.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @throws  \UnexpectedValueException  If missing the `pattern` or `controller` keys from the map.
	 *
	 * @since   1.0
	 */
	public function addRoutes(array $routes)
	{
		foreach ($routes as $route)
		{
			// Ensure a `pattern` key exists
			if (! array_key_exists('pattern', $route))
			{
				throw new \UnexpectedValueException('Route map must contain a pattern variable.');
			}

			// Ensure a `controller` key exists
			if (! array_key_exists('controller', $route))
			{
				throw new \UnexpectedValueException('Route map must contain a controller variable.');
			}

			// If rules have been specified, add them as well.
			$rules = array_key_exists('rules', $route) ? $route['rules'] : array();
			$method = array_key_exists('method', $route) ? $route['method'] : 'GET';

			$this->addRoute($method, $route['pattern'], $route['controller'], $rules);
		}

		return $this;
	}

	/**
	 * Parse the given route and return the name of a controller mapped to the given route.
	 *
	 * @param   string  $route   The route string for which to find and execute a controller.
	 * @param   string  $method  Request method to match. One of GET, POST, PUT, DELETE, HEAD, OPTIONS, TRACE or PATCH
	 *
	 * @return  array   An array containing the controller and the matched variables.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function parseRoute($route, $method = 'GET')
	{
		$method = strtoupper($method);

		if (! array_key_exists($method, $this->routes))
		{
			throw new \InvalidArgumentException(sprintf('%s is not a valid HTTP method.', $method));
		}

		// Get the path from the route and remove and leading or trailing slash.
		$route = trim(parse_url($route, PHP_URL_PATH), ' /');

		// Iterate through all of the known routes looking for a match.
		foreach ($this->routes[$method] as $rule)
		{
			if (preg_match($rule['regex'], $route, $matches))
			{
				// If we have gotten this far then we have a positive match.
				$vars = array();

				foreach ($rule['vars'] as $i => $var)
				{
					$vars[$var] = $matches[$i + 1];
				}

				return array(
					'controller' => $rule['controller'],
					'vars' => $vars
				);
			}
		}

		throw new \InvalidArgumentException(sprintf('Unable to handle request for route `%s`.', $route), 404);
	}

	/**
	 * Add a GET route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function get($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('GET', $pattern, $controller, $rules);
	}

	/**
	 * Add a POST route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function post($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('POST', $pattern, $controller, $rules);
	}

	/**
	 * Add a PUT route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function put($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('PUT', $pattern, $controller, $rules);
	}

	/**
	 * Add a DELETE route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function delete($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('DELETE', $pattern, $controller, $rules);
	}

	/**
	 * Add a HEAD route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function head($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('HEAD', $pattern, $controller, $rules);
	}

	/**
	 * Add a OPTIONS route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function options($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('OPTIONS', $pattern, $controller, $rules);
	}

	/**
	 * Add a TRACE route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function trace($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('TRACE', $pattern, $controller, $rules);
	}

	/**
	 * Add a PATCH route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function patch($pattern, $controller, array $rules = array())
	{
		return $this->addRoute('PATCH', $pattern, $controller, $rules);
	}

	/**
	 * Add a UNIVERSAL (catchall) route to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @param   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function all($pattern, $controller, array $rules = array())
	{
		list($regex, $vars) = $this->buildRegexAndVarList($pattern, $rules);

		foreach ($this->routes as $method => $routes)
		{
			$this->routes[$method][] = array(
				'regex' => $regex,
				'vars' => $vars,
				'controller' => $controller
			);
		}

		return $this;
	}

	/**
	 * String representation of the Router object
	 *
	 * @link    http://php.net/manual/en/serializable.serialize.php
	 *
	 * @return  string  the string representation of the object or null
	 */
	public function serialize()
	{
		$routesCopy = $this->routes;

		foreach ($routesCopy as $httpRequestMethod => $routes)
		{
			foreach ($routes as $i => $route)
			{
				if ($route['controller'] instanceof \Closure)
				{
					$routesCopy[$httpRequestMethod][$i]['controller'] = new SerializableClosure($route['controller']);
				}
			}
		}

		return serialize($routesCopy);
	}

	/**
	 * Constructs the object from a serialized string
	 *
	 * @link    http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param   string  $serialized  The string representation of the object.
	 *
	 * @return  void
	 */
	public function unserialize($serialized)
	{
		$this->routes = unserialize($serialized);
	}
}
