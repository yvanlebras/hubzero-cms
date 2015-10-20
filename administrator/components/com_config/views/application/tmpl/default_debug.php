<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="width-100">
	<fieldset class="adminform">
		<legend><span><?php echo JText::_('COM_CONFIG_DEBUG_SETTINGS'); ?></span></legend>

		<?php
		foreach ($this->form->getFieldset('debug') as $field):
		?>
			<div class="input-wrap">
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			</div>
		<?php
		endforeach;
		?>
	</fieldset>
</div>