<?php
/**
 * @package     hubzero-cms
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
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
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$canDo = ForumHelper::getActions('section');

JToolBarHelper::title(JText::_('Forums'), 'forum.png');
if ($canDo->get('core.admin')) {
	JToolBarHelper::preferences($this->option, '550');
	JToolBarHelper::spacer();
}
if ($canDo->get('core.edit.state')) {
	JToolBarHelper::publishList();
	JToolBarHelper::unpublishList();
	JToolBarHelper::spacer();
}
if ($canDo->get('core.create')) {
	JToolBarHelper::addNew();
}
if ($canDo->get('core.delete')) {
	JToolBarHelper::deleteList();
}
?>
<script type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
	// do field validation
	submitform( pressbutton );
}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<label for="scopeinfo"><?php echo JText::_('Scope:'); ?></label> 
		<select name="scopeinfo" id="scopeinfo" style="max-width: 20em;" onchange="document.adminForm.submit();">
			<option value=""<?php if ($this->filters['scopeinfo'] == '') { echo ' selected="selected"'; } ?>><?php echo JText::_('Select...'); ?></option>
			<option value="site:0"<?php if ($this->filters['scopeinfo'] == 'site:0') { echo ' selected="selected"'; } ?>><?php echo JText::_('[ None ]'); ?></option>
			<?php
			$html = '';

			$list = array();

			foreach ($this->results as $result)
			{
				if ($result->get('scope') == 'site')
				{
					continue;
				}
				if (!isset($list[$result->get('scope')]))
				{
					$list[$result->get('scope')] = array();
				}
				$list[$result->get('scope')][$result->get('scope_id')] = $result;
			}

			foreach ($list as $label => $optgroup)
			{
				$html .= ' <optgroup label="' . $label . '">';
				foreach ($optgroup as $result)
				{
					$html .= ' <option value="' . $result->get('scope') . ':' . $result->get('scope_id') . '"';
					if ($this->filters['scopeinfo'] == $result->get('scope') . ':' . $result->get('scope_id')) 
					{
						$html .= ' selected="selected"';
					}
					$html .= '>' . $this->escape($result->adapter()->name());
					$html .= '</option>'."\n";
				}
				$html .= '</optgroup>'."\n";
			}

			echo $html;
			?>
		</select>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->results );?>);" /></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'ID', 'id', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'Title', 'title', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'State', 'state', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'Access', 'access', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'Scope', 'scope', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JText::_('Categories'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7"><?php echo $this->pageNav->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
if ($this->results)
{
	$k = 0;
	foreach ($this->results as $i => $row)
	{
		switch ($row->get('state')) 
		{
			case '2':
				$task = 'publish';
				$img = 'disabled.png';
				$alt = JText::_('Trashed');
				$cls = 'trash';
			break;
			case '1':
				$task = 'unpublish';
				$img = 'publish_g.png';
				$alt = JText::_('Published');
				$cls = 'publish';
			break;
			case '0':
			default:
				$task = 'publish';
				$img = 'publish_x.png';
				$alt = JText::_('Unpublished');
				$cls = 'unpublish';
			break;
		}
		
		switch ($row->get('access'))
		{
			case 0:
				$color_access = 'style="color: green;"';
				$task_access  = '1';
				$row->set('access_level', JText::_('Public'));
				break;
			case 1:
				$color_access = 'style="color: red;"';
				$task_access  = '2';
				$row->set('access_level', JText::_('Registered'));
				break;
			case 2:
				$color_access = 'style="color: black;"';
				$task_access  = '3';
				$row->set('access_level', JText::_('Special'));
				break;
			case 3:
				$color_access = 'style="color: blue;"';
				$task_access  = '4';
				$row->set('access_level', JText::_('Protected'));
				break;
			case 4:
				$color_access = 'style="color: red;"';
				$task_access  = '0';
				$row->set('access_level', JText::_('Private'));
				break;
		}

		$cat = $row->categories('count', array('state' => -1));
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
					<a href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=edit&amp;id[]=<?php echo $row->get('id'); ?>">
						<?php echo $this->escape(stripslashes($row->get('title'))); ?>
					</a>
				<?php } else { ?>
					<span>
						<?php echo $this->escape(stripslashes($row->get('title'))); ?>
					</span>
				<?php } ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit.state')) { ?>
					<a class="state <?php echo $cls; ?>" href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=<?php echo $task; ?>&amp;id[]=<?php echo $row->get('id'); ?>&amp;<?php echo JUtility::getToken(); ?>=1" title="Set this to <?php echo $task; ?>">
						<span><?php echo $alt; ?></span>
					</a>
				<?php } else { ?>
					<span class="state <?php echo $cls; ?>">
						<span><?php echo $alt; ?></span>
					</span>
				<?php } ?>
				</td>
				<td>
					<!-- <a href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=access&amp;access=<?php echo $task_access; ?>&amp;id[]=<?php echo $row->get('id'); ?>&amp;<?php echo JUtility::getToken(); ?>=1" <?php echo $color_access; ?> title="Change Access"> -->
						<span><?php echo $this->escape($row->get('access_level')); ?></span>
					<!-- </a> -->
				</td>
				<td>
					<span class="scope">
						<span><?php echo $this->escape($row->get('scope')) . ' (' . $this->escape($row->adapter()->name()) . ')'; ?></span>
					</span>
				</td>
				<td>
				<?php if ($cat > 0) { ?>
					<a class="glyph category" href="index.php?option=<?php echo $this->option ?>&amp;controller=categories&amp;section_id=<? echo $row->get('id'); ?>" title="<?php echo JText::_('View the categories for this section'); ?>">
						<span><?php echo $cat; ?></span>
					</a>
				<?php } else { ?>
					<span class="glyph category">
						<span><?php echo $cat; ?></span>
					</span>
				<?php } ?>
				</td>
			</tr>
<?php
		$k = 1 - $k;
	}
}
?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->filters['sort']); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->filters['sort_Dir']); ?>" />

	<?php echo JHTML::_('form.token'); ?>
</form>