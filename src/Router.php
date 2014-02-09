<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router;

/**
 * A path router.
 *
 * @since  1.0
 */
class Router
{
	/**
	 * An array of rules, each rule being an associative array('regex'=> $regex, 'vars' => $vars, 'controller' => $controller)
	 * for routing the request.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $maps = array();

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
			$this->addMaps($maps);
		}
	}

	/**
	 * Add a route map to the router. If the pattern already exists it will be overwritten.
	 *
	 * @param   string  $pattern     The route pattern to use for matching.
	 * @param   mixed   $controller  The controller to map to the given pattern.
	 * @parem   array   $rules       An array of regex rules keyed using the route variables.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function addMap($pattern, $controller, array $rules = array())
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
			if ($segment == '*')
			{
				// Match a splat with no variable.
				$regex[] = '.*';
			}
			elseif ($segment[0] == '*')
			{
				// Match a splat and capture the data to a named variable.
				$vars[] = substr($segment, 1);
				$regex[] = '(.*)';
			}
			elseif ($segment[0] == '\\' && $segment[1] == '*')
			{
				// Match an escaped splat segment.
				$regex[] = '\*' . preg_quote(substr($segment, 2));
			}
			elseif ($segment == ':')
			{
				// Match an unnamed variable without capture.
				$regex[] = '[^/]*';
			}
			elseif ($segment[0] == ':')
			{
				// Match a named variable and capture the data.
				$varName = substr($segment, 1);
				$vars[] = $varName;
				// Use the regex in the rules array if it has been defined.
				$regex[] = array_key_exists($varName, $rules) ? $rules[$varName] : '([^/]*)';
			}
			elseif ($segment[0] == '\\' && $segment[1] == ':')
			{
				// Match a segment with an escaped variable character prefix.
				$regex[] = preg_quote(substr($segment, 1));
			}
			else
			{
				// Match the standard segment.
				$regex[] = preg_quote($segment);
			}
		}

		$this->maps[] = array(
			'regex' => chr(1) . '^' . implode('/', $regex) . '$' . chr(1),
			'vars' => $vars,
			'controller' => $controller
		);

		return $this;
	}

	/**
	 * Add an array of route maps to the router.  If the pattern already exists it will be overwritten.
	 *
	 * @param   array  $maps  A list of route maps to add to the router as $pattern => $controller.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @throws  \UnexpectedValueException  If missing the `pattern` or `controller` keys from the map.
	 *
	 * @since   1.0
	 */
	public function addMaps($maps)
	{
		foreach ($maps as $map)
		{
			// Ensure a `pattern` key exists
			if (! array_key_exists('pattern', $map))
			{
				throw new \UnexpectedValueException('Route map must contain a pattern variable.');
			}

			// Ensure a `controller` key exists
			if (! array_key_exists('controller', $map))
			{
				throw new \UnexpectedValueException('Route map must contain a controller variable.');
			}

			// If rules have been specified, add them as well.
			$rules = array_key_exists('rules', $map) ? $map['rules'] : array();

			$this->addMap($map['pattern'], $map['controller'], $rules);
		}

		return $this;
	}

	/**
	 * Parse the given route and return the name of a controller mapped to the given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  array   An array containing the controller and the matched variables.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function parseRoute($route)
	{
		// Trim the query string off.
		$route = preg_replace('/([^?]*).*/u', '\1', $route);

		// Sanitize and explode the route.
		$route = trim(parse_url($route, PHP_URL_PATH), ' /');

		// Iterate through all of the known route maps looking for a match.
		foreach ($this->maps as $rule)
		{
			if (preg_match($rule['regex'], $route, $matches))
			{
				// If we have gotten this far then we have a positive match.
				$vars = array();

				foreach ($rule['vars'] as $i => $var)
				{
					$vars[$var] = $matches[$i + 1];
				}

				$vars['_rawRoute'] = $route;

				return array(
					'controller' => $rule['controller'],
					'vars' => $vars
				);
			}
		}

		throw new \InvalidArgumentException(sprintf('Unable to handle request for route `%s`.', $route), 404);
	}
}
