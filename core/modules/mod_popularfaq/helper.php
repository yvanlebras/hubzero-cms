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

namespace Modules\PopularFaq;

use Hubzero\Module\Module;
use Components\Kb\Models\Archive;

/**
 * Module class for displaying popular KB articles
 */
class Helper extends Module
{
	/**
	 * Get module contents
	 *
	 * @return  void
	 */
	public function run()
	{
		require_once(\Component::path('com_kb') . DS . 'models' . DS . 'archive.php');

		$a = new Archive();
		$this->rows = $a->articles(
			'popular',
			array('limit' => intval($this->params->get('limit', 5)))
		);

		$this->cssId    = $this->params->get('cssId');
		$this->cssClass = $this->params->get('cssClass');

		require $this->getLayoutPath();
	}

	/**
	 * Display module content
	 *
	 * @return  void
	 */
	public function display()
	{
		if ($content = $this->getCacheContent())
		{
			echo $content;
			return;
		}

		$this->run();
	}
}