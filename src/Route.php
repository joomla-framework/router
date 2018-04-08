<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * An object representing a route definition.
 *
 * @since  __DEPLOY_VERSION__
 */
class Route implements \Serializable
{
	/**
	 * The controller which handles this route
	 *
	 * @var    mixed
	 * @since  __DEPLOY_VERSION__
	 */
	private $controller;

	/**
	 * The default variables defined by the route
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $defaults = [];

	/**
	 * The HTTP method this route supports
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $method;

	/**
	 * The route pattern to use for matching
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $pattern;

	/**
	 * The path regex this route processes
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $regex;

	/**
	 * The variables defined by the route
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $routeVariables = [];

	/**
	 * An array of regex rules keyed using the route variables
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $rules = [];

	/**
	 * Constructor.
	 *
	 * @param   string  $method      The HTTP method this route supports
	 * @param   string  $pattern     The route pattern to use for matching
	 * @param   mixed   $controller  The controller which handles this route
	 * @param   array   $rules       An array of regex rules keyed using the route variables
	 * @param   array   $defaults    The default variables defined by the route
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $method, string $pattern, $controller, array $rules = [], array $defaults = [])
	{
		$this->setMethod($method);
		$this->setPattern($pattern);
		$this->setController($controller);
		$this->setRules($rules);
		$this->setDefaults($defaults);
	}

	/**
	 * Parse the route's pattern to extract the named variables and build a proper regular expression for use when parsing the routes.
	 *
	 * @param   string  $pattern  The route pattern to use for matching.
	 * @param   array   $rules    An array of regex rules keyed using the named route variables.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function buildRegexAndVarList()
	{
		// Sanitize and explode the pattern.
		$pattern = explode('/', trim(parse_url($this->getPattern(), PHP_URL_PATH), ' /'));

		// Prepare the route variables
		$vars = [];

		// Initialize regular expression
		$regex = [];

		// Loop on each segment
		foreach ($pattern as $segment)
		{
			if ($segment == '*')
			{
				// Match a splat with no variable.
				$regex[] = '.*';
			}
			elseif (isset($segment[0]) && $segment[0] == '*')
			{
				// Match a splat and capture the data to a named variable.
				$vars[]  = substr($segment, 1);
				$regex[] = '(.*)';
			}
			elseif (isset($segment[0]) && $segment[0] == '\\' && $segment[1] == '*')
			{
				// Match an escaped splat segment.
				$regex[] = '\*' . preg_quote(substr($segment, 2));
			}
			elseif ($segment == ':')
			{
				// Match an unnamed variable without capture.
				$regex[] = '([^/]*)';
			}
			elseif (isset($segment[0]) && $segment[0] == ':')
			{
				// Match a named variable and capture the data.
				$varName = substr($segment, 1);
				$vars[]  = $varName;

				// Use the regex in the rules array if it has been defined.
				$regex[] = array_key_exists($varName, $this->getRules()) ? '(' . $this->getRules()[$varName] . ')' : '([^/]*)';
			}
			elseif (isset($segment[0]) && $segment[0] == '\\' && $segment[1] == ':')
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

		$this->setRegex(chr(1) . '^' . implode('/', $regex) . '$' . chr(1));
		$this->setRouteVariables($vars);
	}

	/**
	 * Retrieve the controller which handles this route
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Retrieve the default variables defined by the route
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDefaults(): array
	{
		return $this->defaults;
	}

	/**
	 * Retrieve the HTTP method this route supports
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	 * Retrieve the route pattern to use for matching
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}

	/**
	 * Retrieve the path regex this route processes
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRegex(): string
	{
		if (!$this->regex)
		{
			$this->buildRegexAndVarList();
		}

		return $this->regex;
	}

	/**
	 * Retrieve the variables defined by the route
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRouteVariables(): array
	{
		if (!$this->regex)
		{
			$this->buildRegexAndVarList();
		}

		return $this->routeVariables;
	}

	/**
	 * Retrieve the regex rules keyed using the route variables
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	/**
	 * Set the controller which handles this route
	 *
	 * @param   mixed  $controller  The controller which handles this route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setController($controller): self
	{
		$this->controller = $controller;

		return $this;
	}

	/**
	 * Set the default variables defined by the route
	 *
	 * @param   array  $defaults  The default variables defined by the route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDefaults(array $defaults): self
	{
		$this->defaults = $defaults;

		return $this;
	}

	/**
	 * Set the HTTP method this route supports
	 *
	 * @param   string  $method  The HTTP method this route supports
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setMethod(string $method): self
	{
		$this->method = strtoupper($method);

		return $this;
	}

	/**
	 * Set the route pattern to use for matching
	 *
	 * @param   string  $pattern  The route pattern to use for matching
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setPattern(string $pattern): self
	{
		$this->pattern = $pattern;

		$this->setRegex('');
		$this->setRouteVariables([]);

		return $this;
	}

	/**
	 * Set the path regex this route processes
	 *
	 * @param   string  $regex  The path regex this route processes
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRegex(string $regex): self
	{
		$this->regex = $regex;

		return $this;
	}

	/**
	 * Set the variables defined by the route
	 *
	 * @param   array  $routeVariables  The variables defined by the route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRouteVariables(array $routeVariables): self
	{
		$this->routeVariables = $routeVariables;

		return $this;
	}

	/**
	 * Set the regex rules keyed using the route variables
	 *
	 * @param   array  $rules  The rules defined by the route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRules(array $rules): self
	{
		$this->rules = $rules;

		return $this;
	}

	/**
	 * String representation of the Router object
	 *
	 * @return  string  The string representation of the object or null
	 *
	 * @link    http://php.net/manual/en/serializable.serialize.php
	 * @since   __DEPLOY_VERSION__
	 */
	public function serialize()
	{
		$controller = $this->getController() instanceof \Closure ? new SerializableClosure($this->getController()) : $this->getController();

		return serialize(
			[
				'controller'     => $controller,
				'defaults'       => $this->getDefaults(),
				'method'         => $this->getMethod(),
				'pattern'        => $this->getPattern(),
				'regex'          => $this->getRegex(),
				'routeVariables' => $this->getRouteVariables(),
				'rules'          => $this->getRules(),
			]
		);
	}

	/**
	 * Constructs the object from a serialized string
	 *
	 * @param   string  $serialized  The string representation of the object.
	 *
	 * @return  void
	 *
	 * @link    http://php.net/manual/en/serializable.unserialize.php
	 * @since   __DEPLOY_VERSION__
	 */
	public function unserialize($serialized)
	{
		list (
			$this->controller,
			$this->defaults,
			$this->method,
			$this->pattern,
			$this->regex,
			$this->routeVariables,
			$this->rules
		) = unserialize($serialized);
	}
}
