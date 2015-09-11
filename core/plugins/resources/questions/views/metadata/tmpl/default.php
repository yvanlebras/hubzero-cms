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

// No direct access
defined('_HZEXEC_') or die();
?>
<p class="answer">
	<?php if ($this->resource->alias) : ?>
		<a href="<?php echo Route::url('index.php?option=com_resources&alias=' . $this->resource->alias . '&active=questions'); ?>">
	<?php else : ?>
		<a href="<?php echo Route::url('index.php?option=com_resources&id=' . $this->resource->id . '&active=questions'); ?>">
	<?php endif; ?>
		<?php
			if ($this->count == 1)
			{
				echo Lang::txt('PLG_RESOURCES_QUESTIONS_NUM_QUESTION', $this->count);
			}
			else
			{
				echo Lang::txt('PLG_RESOURCES_QUESTIONS_NUM_QUESTIONS', $this->count);
			}
		?>
		</a>
	(<a href="<?php echo Route::url('index.php?option=com_resources&id=' . $this->resource->id . '&active=questions&action=new'); ?>"><?php echo Lang::txt('PLG_RESOURCES_QUESTIONS_ASK_A_QUESTION'); ?></a>)
</p>