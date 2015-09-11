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
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Oauth\Storage;

/**
 * Session token storage methods
 */
interface SessionTokenInterface
{
	/**
	 * Get session id from cookie
	 * 
	 * @return  bool  Result of test
	 */
	public function getSessionIdFromCookie();

	/**
	 * Get user id via session id
	 * 
	 * @param   string  $session_id  Session identifier
	 * @return  int     User identifier
	 */
	public function getUserIdFromSessionId($session_id);

	/**
	 * Loads client needed for internal requests
	 * 
	 * @return  mixed
	 */
	public function getInternalRequestClient();

	/**
	 * Create internal client. This avoids the issue if the client wasnt 
	 * created or was accidentally delete resulted in both session 
	 * and tool requests failing
	 * 
	 * @return  void
	 */
	public function createInternalRequestClient();
}