<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Command;

use Joomla\Console\AbstractCommand;
use Joomla\Console\Helper\DescriptorHelper;

/**
 * Command listing all available commands.
 *
 * @since  __DEPLOY_VERSION__
 */
class ListCommand extends AbstractCommand
{
	/**
	 * Execute the command.
	 *
	 * @return  integer|void  An optional command code, if ommitted will be treated as a successful return (code 0)
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute()
	{
		$descriptor = new DescriptorHelper;

		$this->getHelperSet()->set($descriptor);

		$descriptor->describe($this->getApplication()->getConsoleOutput(), $this->getApplication());
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function initialise()
	{
		$this->setName('list');
		$this->setDescription("List the application's available commands");
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all of the application's commands:

  <info>php %command.full_name%</info>
EOF
		);
	}
}
