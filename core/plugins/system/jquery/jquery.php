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

// no direct access
defined('_HZEXEC_') or die();

/**
 * System plugin for adding jQuery to the document
 */
class plgSystemJquery extends \Hubzero\Plugin\Plugin
{
	/**
	 * Hook for after routing application
	 * 
	 * @return  void
	 */
	public function onAfterRoute()
	{
		if (!App::isAdmin() && !App::isSite())
		{
			return;
		}

		$client = 'Site';
		if (App::isAdmin())
		{
			$client = 'Admin';
			return;
		}

		// Check if active for this client (Site|Admin)
		if (!$this->params->get('activate' . $client) || Request::getVar('format') == 'pdf')
		{
			return;
		}

		Html::behavior('framework');

		if ($this->params->get('jqueryui'))
		{
			Html::behavior('framework', true);
		}

		if ($this->params->get('jqueryfb'))
		{
			Html::behavior('modal');
		}

		if ($this->params->get('noconflict' . $client))
		{
			Document::addScript(Request::root(true) . '/core/assets/js/jquery.noconflict.js');
		}
	}
}