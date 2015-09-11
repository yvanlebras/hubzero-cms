<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Console;

use Hubzero\Console\Config;
use Hubzero\Console\Exception\UnsupportedCommandException;
use Hubzero\Console\Exception\UnsupportedTaskException;
use Hubzero\Console\Exception\InvalidPropertyException;

/**
 * Console arguments class
 **/
class Arguments
{
	/**
	 * Raw command line arguments (PHP $argv)
	 *
	 * @var  array
	 **/
	private $raw = NULL;

	/**
	 * Class name - command to execute
	 *
	 * @var  string
	 **/
	private $class = NULL;

	/**
	 * Task name - class method to execute
	 *
	 * @var  string
	 **/
	private $task = NULL;

	/**
	 * Array of additional options being passed to the command
	 *
	 * @var  array
	 **/
	private $opts = NULL;

	/**
	 * Constructor
	 *
	 * Set raw arguments
	 *
	 * @param   object  $arguments  The command arguments
	 * @return  void
	 **/
	public function __construct($arguments)
	{
		$this->raw = $arguments;
	}

	/**
	 * Simple getter for class properties
	 *
	 * Throws invalid property exception if property isn't found
	 *
	 * @param   string  $var  The property to retrieve
	 * @return  void
	 **/
	public function get($var)
	{
		if (isset($this->{$var}))
		{
			return $this->{$var};
		}
		else
		{
			throw new InvalidPropertyException("Property {$var} does not exists.");
		}
	}

	/**
	 * Getter for those additional options that a given command may use
	 *
	 * @param   string  $key      Option name to retieve value for
	 * @param   mixed   $default  Default value for option
	 * @return  void
	 **/
	public function getOpt($key, $default = false)
	{
		return (isset($this->opts[$key])) ? $this->opts[$key] : $default;
	}

	/**
	 * Get all opts
	 *
	 * @return  array
	 **/
	public function getOpts()
	{
		return $this->opts;
	}

	/**
	 * Setter for additional options for a given command
	 *
	 * @param   string  $key    The argument to set
	 * @param   mixed   $value  The argument value to give it
	 * @return  void
	 **/
	public function setOpt($key, $value)
	{
		$this->opts[$key] = $value;
	}

	/**
	 * Delete option
	 *
	 * @param   string  $key  The argument to remove
	 * @return  void
	 **/
	public function deleteOpt($key)
	{
		unset($this->opts[$key]);
	}

	/**
	 * Parse the raw arguments into command, task, and additional options
	 *
	 * @return  void
	 **/
	public function parse()
	{
		if (isset($this->raw) && count($this->raw) > 0)
		{
			$class = isset($this->raw[1]) ? $this->raw[1] : 'help';
			$task  = (isset($this->raw[2]) && substr($this->raw[2], 0, 1) != "-") ? $this->raw[2] : 'execute';

			$this->class = self::routeCommand($class);
			$this->task  = self::routeTask($class, $this->class, $task);

			// Parse the remaining args for command options/arguments
			for ($i = 2; $i < count($this->raw); $i++)
			{
				// Ignore the second arg if we used it above as task
				if ($i == 2 && substr($this->raw[$i], 0, 1) != "-")
				{
					continue;
				}

				// Args with an "=" will use the value before as key and the value after as value
				if (strpos($this->raw[$i], "=") !== false)
				{
					$parts = explode("=", $this->raw[$i], 2);
					$key   = preg_replace("/^([-]{1,2})/", "", $parts[0]);
					$value = ($parts[1]);

					if (isset($this->opts[$key]))
					{
						$this->opts[$key] = (array)$this->opts[$key];
						array_push($this->opts[$key], $value);
					}
					else
					{
						$this->opts[$key] = $value;
					}

					continue;
				}
				// Args with a dash but no equals sign will be considered TRUE if present
				elseif (substr($this->raw[$i], 0, 1) == '-')
				{
					// Try to catch clumped arguments (ex: -if as shorthand for -i -f)
					if (preg_match("/^-([[:alpha:]]{2,})/", $this->raw[$i], $matches))
					{
						if (isset($matches[1]))
						{
							foreach (str_split($matches[1], 1) as $k)
							{
								$this->opts[$k] = true;
							}
						}

						continue;
					}
					else
					{
						$key   = preg_replace("/^([-]{1,2})/", "", $this->raw[$i]);
						$value = true;
					}
				}
				// Otherwise, we'll just save the arg as a single word and individual commands may use them
				else
				{
					$key   = $i;
					$value = $this->raw[$i];
				}

				$this->opts[$key] = $value;
			}
		}
	}

	/**
	 * Routes command to the proper file based on the input given
	 *
	 * @param   string  $command  The command to route
	 * @return  void
	 **/
	public static function routeCommand($command = 'help')
	{
		// Aliases take precedence, so parse for them first
		if ($aliases = Config::get('aliases'))
		{
			if (array_key_exists($command, $aliases))
			{
				if (strpos($aliases->$command, '::') !== false)
				{
					$bits      = explode('::', $aliases->$command);
					$command   = $bits[0];
					$aliasTask = $bits[1];
				}
				else
				{
					$command = $aliases->$command;
				}
			}
		}

		// Check if we're targeting a namespaced command
		if (strpos($command, ':'))
		{
			$bits    = explode(':', $command);
			$command = '';
			foreach ($bits as $bit)
			{
				$command .= '\\' . ucfirst($bit);
			}
		}
		else
		{
			$command = '\\' . ucfirst($command);
		}

		$class = __NAMESPACE__ . '\\Command' . $command;

		// Make sure class exists
		if (!class_exists($class))
		{
			$notfound = true;

			// Also check to see if a command is available in the component itself
			$parts = explode('\\', ltrim($command, '\\'));

			$comPath = PATH_CORE . DS . 'components' . DS . 'com_' . strtolower($parts[0]);
			if (is_dir($comPath))
			{
				if (isset($parts[1]) && is_file($comPath . DS . 'cli' . DS . 'commands' . DS . $parts[1] . '.php'))
				{
					require_once $comPath . DS . 'cli' . DS . 'commands' . DS . $parts[1] . '.php';
					$notfound    = false;
					$class       = 'Components\\' . ucfirst($parts[0]) . '\\Cli\\Commands\\' . ucfirst($parts[1]);
				}
			}

			if ($notfound) throw new UnsupportedCommandException("Unknown command: {$command}.");
		}

		return $class;
	}

	/**
	 * Routes task to the proper method based on the input given
	 *
	 * @param   string  $command  The command to route
	 * @param   string  $class    The class deduced from routeCommand
	 * @param   string  $task     The task to route
	 * @return  void
	 **/
	public static function routeTask($command, $class, $task = 'execute')
	{
		// Aliases take precedence, so parse for them first
		if ($aliases = Config::get('aliases'))
		{
			if (array_key_exists($command, $aliases))
			{
				if (strpos($aliases->$command, '::') !== false)
				{
					$bits = explode('::', $aliases->$command);
					$task = $bits[1];
				}
			}
		}

		// Make sure task exists
		if (!method_exists($class, $task))
		{
			throw new UnsupportedTaskException("{$class} does not support the {$task} method.");
		}

		return $task;
	}
}