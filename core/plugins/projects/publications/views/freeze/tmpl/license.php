<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 HUBzero Foundation, LLC.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 HUBzero Foundation, LLC.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_HZEXEC_') or die();

// Get block properties
$complete = $this->pub->curation('blocks', $this->step, 'complete');
$required = $this->pub->curation('blocks', $this->step, 'required');

$elName = "licensePick";

$defaultText = $this->license ? $this->license->text : NULL;
$text = $this->pub->license_text ? $this->pub->license_text : $defaultText;

?>

<!-- Load content selection browser //-->
<div id="<?php echo $elName; ?>" class="blockelement<?php echo $required ? ' el-required' : ' el-optional';
echo $complete ? ' el-complete' : ' el-incomplete'; ?> freezeblock">
		<?php if ($this->license) {
			$info = $this->license->info;
			if ($this->license->url) {
				 $info .= ' <a href="' . $this->license->url . '" class="popup">' . Lang::txt('Read license terms') . ' &rsaquo;</a>';
			}
			?>
			<div class="chosenitem">
				<p class="item-title">
			<?php if ($this->license) { echo '<img src="' . $this->license->icon . '" alt="' . htmlentities($this->license->title) . '" />'; } ?><?php echo $this->license->title; ?>
					<span class="item-details"><?php echo $info; ?></span>
				</p>
				<?php if ($text) { ?>
				<pre><?php echo $text; ?></pre>	
				<?php } ?>
			</div>

		<?php } else { ?>
			<?php echo '<p class="nocontent">' . Lang::txt('PLG_PROJECTS_PUBLICATIONS_NONE') . '</p>'; ?>
		<?php } ?>
</div>