<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2014 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2014 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Plugins\Content\Formathtml\Macros;

use Plugins\Content\Formathtml\Macro;

/**
 * macro class for displaying a youtube video
 */
class Feed extends Macro
{
	/**
	 * Returns description of macro, use, and accepted arguments
	 *
	 * @return     array
	 */
	public function description()
	{
		$txt = array();
		$txt['wiki'] = 'Embeds a RSS Feed into the Page';
		$txt['html'] = '<p>Embeds a RSS feed into the page.</p>
						<p>Examples:</p>
						<ul>
							<li><code>[[Feed(http://rss.cnn.com/rss/cnn_topstories.rss)]]</code></li>
							<li><code>[[Feed(http://rss.cnn.com/rss/cnn_topstories.rss, 3)]] - show 3 feed items</code></li>
							<li><code>[[Feed(http://rss.cnn.com/rss/cnn_topstories.rss, class=cnn_feed)]] - feed with class "cnn_feed"</code></li>
						</ul>';

		return $txt['html'];
	}

	/**
	 * Generate macro output
	 *
	 * @return     string
	 */
	public function render()
	{
		//get the args passed in
		$args = explode(',', $this->args);

		// get feed url
		$url = $this->_getFeedUrl($args);

		// get feed details
		$limit = $this->_getFeedLimit($args, 5);
		$class = $this->_getFeedClass($args);

		// get feed
		jimport('simplepie.simplepie');
		$feed = new \SimplePie(null, PATH_APP . DS . 'app' . DS . 'cache' . DS . 'site', 0);
		$feed->set_feed_url($url);
		$feed->init();

		//var to hold html
		$html = '<div class="feed ' . $class . '">';

		// display title
		$title = $feed->get_title();
		$link  = $feed->get_permalink();
		if ($title)
		{
			$html .= '<h3><a rel="external" href="' . $link . '">' . $title . '</a></h3>';
		}

		// display description
		$desc = $feed->get_description();
		if ($desc)
		{
			$html .= '<p>' . $desc . '</p>';
		}

		// add each item
		foreach ($feed->get_items(0, $limit) as $item)
		{
			$html .= $this->_renderItem($item);
		}

		// close feed
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render an individual item
	 *
	 * @param  object   $item    Feed Item
	 * @return string
	 */
	private function _renderItem( $item )
	{
		$html  = '<div class="item">';
		$html .= '<h4>' . $item->get_title() . '</h4>';
		$html .= '<p>' . $item->get_description() . '</p>';
		$html .= '<a rel="external" href="' . $item->get_permalink() . '">Read More &rsaquo;</a>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * Pull Feed url from args passed in
	 *
	 * @param  array  $args  Macro Arguments
	 * @return mixed
	 */
	private function _getFeedUrl( &$args )
	{
		foreach ($args as $k => $arg)
		{
			if (filter_var($arg, FILTER_VALIDATE_URL))
			{
				$url = $arg;
				unset($args[$k]);
				return $url;
			}
		}
	}

	/**
	 * Get feed item limit
	 *
	 * @param  array  $args  Macro Arguments
	 * @return mixed
	 */
	private function _getFeedLimit( &$args, $default = 5 )
	{
		foreach ($args as $k => $arg)
		{
			if (is_numeric($arg) && $arg > 0 && $arg < 50)
			{
				$limit = $arg;
				unset($args[$k]);
				return $limit;
			}
		}

		// if we didnt find one return default
		return $default;
	}

	/**
	 * Get feed class
	 *
	 * @param  array  $args  Macro Arguments
	 * @return mixed
	 */
	private function _getFeedClass( &$args )
	{
		foreach ($args as $k => $arg)
		{
			if (preg_match('/class=([\w-]*)/', $arg, $matches))
			{
				$class = (isset($matches[1])) ? $matches[1] : '';
				unset($args[$k]);
				return $class;
			}
		}
	}
}
