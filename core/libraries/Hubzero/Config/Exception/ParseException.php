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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Config\Exception;

class ParseException extends \ErrorException
{
	/**
	 * Constructor
	 *
	 * @param   array  $error
	 * @return  void
	 */
	public function __construct(array $error)
	{
		$message   = $error['message'];
		$code      = isset($error['code']) ? $error['code'] : 0;
		$severity  = isset($error['type']) ? $error['type'] : 1;
		$filename  = isset($error['file']) ? $error['file'] : __FILE__;
		$lineno    = isset($error['line']) ? $error['line'] : __LINE__;
		$exception = isset($error['exception']) ? $error['exception'] : null;

		parent::__construct($message, $code, $severity, $filename, $lineno, $exception);
	}
}