<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router;

/**
 * RESTful Web application router class for the Joomla Framework.
 *
 * @since       1.0
 * @deprecated  2.0  Use the base Router class instead
 */
class RestRouter extends Router
{
	/**
	 * A boolean allowing to pass _method as parameter in POST requests
	 *
	 * @var    boolean
	 * @since  1.0
	 * @deprecated  2.0  Use the base Router class instead
	 */
	protected $methodInPostRequest = false;

	/**
	 * An array of HTTP Method => controller suffix pairs for routing the request.
	 *
	 * @var    array
	 * @since  1.0
	 * @deprecated  2.0  Use the base Router class instead
	 */
	protected $suffixMap = array(
		'GET'     => 'Get',
		'POST'    => 'Create',
		'PUT'     => 'Update',
		'PATCH'   => 'Update',
		'DELETE'  => 'Delete',
		'HEAD'    => 'Head',
		'OPTIONS' => 'Options',
	);

	/**
	 * Get the property to allow or not method in POST request
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @deprecated  2.0  Use the base Router class instead
	 */
	public function isMethodInPostRequest()
	{
		return $this->methodInPostRequest;
	}

	/**
	 * Set a controller class suffix for a given HTTP method.
	 *
	 * @param   string  $method  The HTTP method for which to set the class suffix.
	 * @param   string  $suffix  The class suffix to use when fetching the controller name for a given request.
	 *
	 * @return  Router  Returns itself to support chaining.
	 *
	 * @since   1.0
	 * @deprecated  2.0  Use the base Router class instead
	 */
	public function setHttpMethodSuffix($method, $suffix)
	{
		$this->suffixMap[strtoupper((string) $method)] = (string) $suffix;

		return $this;
	}

	/**
	 * Set to allow or not method in POST request
	 *
	 * @param   boolean  $value  A boolean to allow or not method in POST request
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @deprecated  2.0  Use the base Router class instead
	 */
	public function setMethodInPostRequest($value)
	{
		$this->methodInPostRequest = $value;
	}

	/**
	 * Get the controller class suffix string.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 * @deprecated  2.0  Use the base Router class instead
	 */
	protected function fetchControllerSuffix()
	{
		// Validate that we have a map to handle the given HTTP method.
		if (!isset($this->suffixMap[$this->input->getMethod()]))
		{
			throw new \RuntimeException(sprintf('Unable to support the HTTP method `%s`.', $this->input->getMethod()), 404);
		}

		// Check if request method is POST
		if ($this->methodInPostRequest == true && strcmp(strtoupper($this->input->server->getMethod()), 'POST') === 0)
		{
			// Get the method from input
			$postMethod = $this->input->get->getWord('_method');

			// Validate that we have a map to handle the given HTTP method from input
			if ($postMethod && isset($this->suffixMap[strtoupper($postMethod)]))
			{
				return ucfirst($this->suffixMap[strtoupper($postMethod)]);
			}
		}

		return ucfirst($this->suffixMap[$this->input->getMethod()]);
	}

	/**
	 * Parse the given route and return the name of a controller mapped to the given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  string  The controller name for the given route excluding prefix.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 * @deprecated  2.0  Use the base Router class instead
	 */
	protected function parseRoute($route)
	{
		$name = parent::parseRoute($route);

		// Append the HTTP method based suffix.
		$name .= $this->fetchControllerSuffix();

		return $name;
	}
}
