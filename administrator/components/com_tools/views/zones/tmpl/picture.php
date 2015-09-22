<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<div id="media">
	<form action="index.php" method="post" enctype="multipart/form-data" name="filelist" id="filelist">
		<table class="formed">
			<thead>
				<tr>
					<th><label for="image"><?php echo JText::_('UPLOAD'); ?> <?php echo JText::_('WILL_REPLACE_EXISTING_IMAGE'); ?></label></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
						<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">
						<input type="hidden" name="tmpl" value="component" />
						<input type="hidden" name="id" value="<?php echo $this->zone->get('id'); ?>" />
						<input type="hidden" name="task" value="upload" />
						
						<input type="file" name="upload" id="upload" size="17" />&nbsp;&nbsp;&nbsp;
						<input type="submit" value="<?php echo JText::_('UPLOAD'); ?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
		if ($this->getError()) 
		{
			echo '<p class="error">' . $this->getError() . '</p>';
		}
	?>
		<table class="formed">
			<thead>
				<tr>
					<th colspan="4"><label for="image"><?php echo JText::_('COM_TOOLS_LOGO'); ?></label></th>
				</tr>
			</thead>
			<tbody>
		<?php
			$k = 0;

			$path = $zone->logo('path');
			$file = $zone->get('picture');

			if ($file && file_exists($path . DS . $file)) 
			{
				$this_size = filesize($path . DS . $file);
				list($width, $height, $type, $attr) = getimagesize($path . DS . $file);
		?>
				<tr>
					<td rowspan="6">
						<img src="<?php echo '../' . substr($path, strlen(JPATH_ROOT . '/')) . DS . $file; ?>" alt="<?php echo JText::_('COM_TOOLS_LOGO'); ?>" id="conimage" />
					</td>
					<th><?php echo JText::_('FILE'); ?>:</th>
					<td><?php echo $file; ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('SIZE'); ?>:</th>
					<td><?php echo \Hubzero\Utility\Number::formatBytes($this_size); ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('WIDTH'); ?>:</th>
					<td><?php echo $width; ?> px</td>
				</tr>
				<tr>
					<th><?php echo JText::_('HEIGHT'); ?>:</th>
					<td><?php echo $height; ?> px</td>
				</tr>
				<tr>
					<td><input type="hidden" name="currentfile" value="<?php echo $file; ?>" /></td>
					<td><a href="index.php?option=<?php echo $this->option; ?>&amp;controller=<?php echo $this->controller; ?>&amp;tmpl=component&amp;task=removefile&amp;id=<?php echo $this->zone->get('id'); ?>&amp;<?php echo JUtility::getToken(); ?>=1">[ <?php echo JText::_('DELETE'); ?> ]</a></td>
				</tr>
		<?php } else { ?>
				<tr>
					<td colspan="4">
						<?php echo JText::_('COM_TOOLS_LOGO_NONE'); ?>
						<input type="hidden" name="currentfile" value="" />
					</td>
				</tr>
		<?php } ?>
			</tbody>
		</table>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>