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
	 * Constructor.
	 *
	 * @param   string  $method          The HTTP method this route supports
	 * @param   string  $regex           The path regex this route processes
	 * @param   mixed   $controller      The controller which handles this route
	 * @param   array   $routeVariables  The variables defined by the route
	 * @param   array   $defaults        The default variables defined by the route
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $method, string $regex, $controller, array $routeVariables = [], array $defaults = [])
	{
		$this->setMethod($method);
		$this->setRegex($regex);
		$this->setController($controller);
		$this->setRouteVariables($routeVariables);
		$this->setDefaults($defaults);
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
	 * Retrieve the path regex this route processes
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRegex(): string
	{
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
		return $this->routeVariables;
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
				'regex'          => $this->getRegex(),
				'routeVariables' => $this->getRouteVariables(),
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
		list ($this->controller, $this->defaults, $this->method, $this->regex, $this->routeVariables) = unserialize($serialized);
	}
}
