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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

function StoreBuildRoute(&$query)
{
	$segments = array();
	
	if (!empty($query['task'])) {
		$segments[] = $query['task'];
		unset($query['task']);
	}
	if (!empty($query['action'])) {
		$segments[] = $query['action'];
		unset($query['action']);
	}
	if (!empty($query['item'])) {
		$segments[] = $query['item'];
		unset($query['item']);
	}
	
	return $segments;
}

function StoreParseRoute($segments)
{
	$vars = array();

	if (empty($segments[0])) {
		return $vars;
	}
	
	if (isset($segments[0])) {
		$vars['task'] = $segments[0];
	}
	if (isset($segments[1])) {
		$vars['action'] = $segments[1];
	}
	if (isset($segments[2])) {
		$vars['item'] = $segments[2];
	}
	
	return $vars;
}
?>