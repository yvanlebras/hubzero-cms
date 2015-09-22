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

$canDo = WikiHelper::getActions('page');

JToolBarHelper::title(JText::_('Wiki'), 'wiki.png');
if ($canDo->get('core.admin')) 
{
	JToolBarHelper::preferences($this->option, '550');
	JToolBarHelper::spacer();
}
if ($canDo->get('core.create')) 
{
	JToolBarHelper::addNew();
}
if ($canDo->get('core.edit')) 
{
	JToolBarHelper::editList();
}
if ($canDo->get('core.delete')) 
{
	JToolBarHelper::deleteList();
}
?>
<script type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}
	// do field validation
	submitform(pressbutton);
}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="col width-40 fltlft">
			<label for="filter_search"><?php echo JText::_('Search'); ?>:</label>
			<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($this->filters['search']); ?>" />

			<input type="submit" value="<?php echo JText::_('Go'); ?>" />
		</div>
		<div class="col width-60 fltrt" style="text-align: right">
			<label for="filter_group"><?php echo JText::_('Group'); ?>:</label>
			<select name="group" id="filter_group" onchange="document.adminForm.submit( );">
				<option value=""><?php echo JText::_('- Select group -'); ?></option>
				<?php 
				if ($this->groups) {
					foreach ($this->groups as $group) {
				?>
				<option value="<?php echo $this->escape($group->cn); ?>"<?php if ($group->cn == $this->filters['group']) { echo ' selected="selected"'; } ?>><?php echo $this->escape(stripslashes($group->cn)); ?></option>
				<?php
					}
				}
				?>
			</select>

			<label for="filter_namespace"><?php echo JText::_('Namespace'); ?>:</label>
			<select name="namespace" id="filter_namespace" onchange="document.adminForm.submit( );">
				<option value="">- Select namespace -</option>
				<option value="Help"<?php if (strtolower($this->filters['namespace']) == 'help') { echo ' selected="selected"'; } ?>>Help</option>
				<option value="Template"<?php if (strtolower($this->filters['namespace']) == 'template') { echo ' selected="selected"'; } ?>>Template</option>
			</select>
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows);?>);" /></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'ID', 'id', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'Title', 'title', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JText::_('Mode'); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'State', 'state', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'Group', 'group', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'Revisions', 'revisions', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'Comments', 'comments', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8"><?php echo $this->pageNav->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
$k = 0;
$i = 0;
//for ($i=0, $n=count($this->rows); $i < $n; $i++)
foreach ($this->rows as $row)
{
	//$row = new WikiModelPage($this->rows[$i]);

	switch ($row->get('state'))
	{
		case 2:
			$color_access = 'style="color: #000;"';
			$class = 'trash';
			$task = '0';
			$alt = JText::_('Trashed');
		break;

		case 1:
			$color_access = 'style="color: red;"';
			$class = 'locked';
			$task = '0';
			$alt = JText::_('Locked');
		break;

		case 0:
		default:
			$color_access = 'style="color: green;"';
			$class = 'open';
			$task = '1';
			$alt = JText::_('Open');
		break;
	}
?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<input type="checkbox" name="id[]" id="cb<?php echo $i;?>" value="<?php echo $row->get('id'); ?>" onclick="isChecked(this.checked, this);" />
				</td>
				<td>
					<?php echo $row->get('id'); ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit')) { ?>
					<a href="index.php?option=<?php echo $this->option; ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=edit&amp;id[]=<?php echo $row->get('id'); ?>" title="<?php echo JText::_('Edit Page'); ?>">
						<?php echo $this->escape(stripslashes($row->get('title'))); ?>
					</a>
				<?php } else { ?>
					<span>
						<?php echo $this->escape(stripslashes($row->get('title'))); ?>
					</span>
				<?php } ?>
					<br /><?php if ($row->get('scope')) { ?><span style="color: #999; font-size: 90%"><?php echo $this->escape(stripslashes($row->get('scope'))); ?>/</span> &nbsp; <?php } ?><span style="color: #999; font-size: 90%"><?php echo $this->escape(stripslashes($row->get('pagename'))); ?></span>
				</td>
				<td>
					<?php echo $this->escape($row->param('mode')); ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit.state')) { ?>
					<a href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=state&amp;id=<?php echo $row->get('id'); ?>&amp;state=<?php echo $task; ?>&amp;<?php echo JUtility::getToken(); ?>=1" <?php echo $color_access; ?> title="<?php echo JText::_('Change State'); ?>">
						<?php echo $alt; ?>
					</a>
				<?php } else { ?>
					<span <?php echo $color_access; ?>>
						<?php echo $alt; ?>
					</span>
				<?php } ?>
				</td>
				<td>
					<span class="group">
						<span><?php echo $this->escape($row->get('group_cn')); ?></span>
					</span>
				</td>
			<?php if ($row->get('revisions') > 0) { ?>
				<td>
					<a class="revisions" href="index.php?option=<?php echo $this->option ?>&amp;controller=revisions&amp;pageid=<?php echo $row->get('id'); ?>" title="<?php echo JText::_('VIEW_ARTICLES_FOR_CATEGORY'); ?>">
						<span><?php echo $this->escape($row->get('revisions')) . ' ' . JText::_('revisions'); ?></span>
					</a>
				</td>
			<?php } else { ?>
				<td>
					<span class="revisions">
						<span><?php echo $this->escape($row->get('revisions')); ?></span>
					</span>
				</td>
			<?php } ?>
				<!-- <td>
					<span class="hits">
						<span><?php echo $this->escape($row->get('hits')); ?></span>
					</span>
				</td> -->
				<td>
				<?php if ($canDo->get('core.edit')) { ?>
					<a class="comment" href="index.php?option=<?php echo $this->option ?>&amp;controller=comments&amp;pageid=<?php echo $row->get('id'); ?>">
						<?php echo $row->comments('count') . ' ' . JText::_('comment(s)'); //$comment->getEntriesCount(array('pageid' => $row->id)) ?>
					</a>
				<?php } else { ?>
					<span class="comment">
						<?php echo $row->comments('count') . ' ' . JText::_('comment(s)'); ?>
					</span>
				<?php } ?>
				</td>
			</tr>
<?php
	$i++;
	$k = 1 - $k;
}
?>
		</tbody>
	</table>

	<input type="hidden" name="filter_order" value="<?php echo $this->filters['sort']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filters['sort_Dir']; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="<?php echo $this->task; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	
	<?php echo JHTML::_('form.token'); ?>
</form>