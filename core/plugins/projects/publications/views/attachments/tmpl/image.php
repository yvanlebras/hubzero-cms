<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
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

$data = $this->data;
$pub  = $data->get('pub');

$details = $data->get('localPath');
$details.= $data->getSize() ? ' | ' . $data->getSize('formatted') : '';
if ($data->get('viewer') != 'freeze')
{
	$details.= !$data->exists() ? ' | ' . Lang::txt('PLG_PROJECTS_PUBLICATIONS_MISSING_FILE') : '';
}

// Get settings
$suffix = isset($this->config->params->thumbSuffix) && $this->config->params->thumbSuffix
		? $this->config->params->thumbSuffix : '-tn';

$format = isset($this->config->params->thumbFormat) && $this->config->params->thumbFormat
		? $this->config->params->thumbFormat : 'png';

$thumbName = \Components\Projects\Helpers\Html::createThumbName(basename($data->get('fpath')), $suffix, $format);

$filePath = Route::url($pub->link('versionid')) . '/Image:' . urlencode(basename($data->get('fpath')));
$thumbSrc = Route::url($pub->link('versionid')) . '/Image:' . urlencode($thumbName);

// Is this image used for publication thumbail?
$class = $data->get('pubThumb') == 1 ? ' starred' : '';
$over  = $data->get('pubThumb') == 1 ? ' title="' . Lang::txt('PLG_PROJECTS_PUBLICATIONS_IMAGE_DEFAULT') . '"' : '';

?>
	<li class="image-container">
		<span class="item-options">
			<?php if ($data->get('viewer') == 'edit') { ?>
			<span>
				<?php if (!$data->get('pubThumb')) { ?>
				<a href="<?php echo Route::url($pub->link('editversion') . '&action=saveitem&aid=' . $data->get('id') . '&p=' . $data->get('props') . '&makedefault=1'); ?>" class="item-default" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_IMAGE_MAKE_DEFAULT'); ?>">&nbsp;</a>
				<?php } ?>
				<a href="<?php echo Route::url($pub->link('editversion') . '&action=edititem&aid=' . $data->get('id') . '&p=' . $data->get('props')); ?>" class="showinbox item-edit" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_RELABEL'); ?>">&nbsp;</a>
				<a href="<?php echo Route::url($pub->link('editversion') . '&action=deleteitem&aid=' . $data->get('id') . '&p=' . $data->get('props')); ?>" class="item-remove" title="<?php echo Lang::txt('PLG_PROJECTS_PUBLICATIONS_REMOVE'); ?>">&nbsp;</a>
			</span>
			<?php } ?>
		</span>
		<span class="item-image<?php echo $class; ?>" <?php echo $over; ?>><a class="more-content" href="<?php echo $filePath; ?>"><img alt="" src="<?php echo $thumbSrc; ?>" /></a></span>
		<span class="item-title">
			<?php echo $data->get('title'); ?></span>
		<span class="item-details"><?php echo $details; ?></span>
	</li>