<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router;

/**
 * An object representing a resolved route.
 *
 * @since  __DEPLOY_VERSION__
 */
class ResolvedRoute
{
	/**
	 * The controller which handles this route
	 *
	 * @var    mixed
	 * @since  __DEPLOY_VERSION__
	 */
	private $controller;

	/**
	 * The variables matched by the route
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $routeVariables;

	/**
	 * Constructor.
	 *
	 * @param   mixed  $controller      The controller which handles this route
	 * @param   array  $routeVariables  The variables matched by the route
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($controller, array $routeVariables)
	{
		$this->controller     = $controller;
		$this->routeVariables = $routeVariables;
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
	 * Retrieve the variables matched by the route
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRouteVariables(): array
	{
		return $this->routeVariables;
	}
}
