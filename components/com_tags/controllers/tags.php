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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Controller class for tags
 */
class TagsControllerTags extends \Hubzero\Component\SiteController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->_authorize();

		$this->registerTask('feed.rss', 'feed');
		$this->registerTask('feedrss', 'feed');

		if (($tagstring = urldecode(trim(JRequest::getVar('tag', '', 'request', 'none', 2)))))
		{
			if (!JRequest::getVar('task', ''))
			{
				JRequest::setVar('task', 'view');
			}
		}

		parent::execute();
	}

	/**
	 * Display the main page for this component
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Set the page title
		$this->_buildTitle(null);

		// Set the pathway
		$this->_buildPathway(null);

		$this->view
			->set('cloud', new TagsModelCloud())
			->set('config', $this->config)
			->display();
	}

	/**
	 * View items tagged with this tag
	 *
	 * @return  void
	 */
	public function viewTask()
	{
		// Incoming
		$tagstring = urldecode(htmlspecialchars_decode(trim(JRequest::getVar('tag', '', 'request', 'none', 2))));

		$addtag = trim(JRequest::getVar('addtag', ''));

		// Ensure we were passed a tag
		if (!$tagstring && !$addtag)
		{
			if (JRequest::getWord('task', '', 'get'))
			{
				$this->setRedirect(
					JRoute::_('index.php?option=' . $this->_option)
				);
				return;
			}
			JError::raiseError(404, JText::_('COM_TAGS_NO_TAG'));
			return;
		}

		if ($tagstring)
		{
			// Break the string into individual tags
			$tgs = explode(',', $tagstring);
			$tgs = array_map('trim', $tgs);
		}
		else
		{
			$tgs = array();
		}

		// See if we're adding any tags to the search list
		if ($addtag && !in_array($addtag, $tgs))
		{
			$tgs[] = $addtag;
		}

		// Sanitize the tag
		$tags  = array();
		$added = array();
		$rt    = array();
		foreach ($tgs as $tag)
		{
			// Load the tag
			$tagobj = TagsModelTag::getInstance($tag);

			if (in_array($tagobj->get('tag'), $added))
			{
				continue;
			}

			$added[] = $tagobj->get('tag');

			// Ensure we loaded the tag's info from the database
			if ($tagobj->exists())
			{
				$tags[] = $tagobj;
				$rt[]   = $tagobj->get('raw_tag');
			}
		}

		// Ensure we loaded the tag's info from the database
		if (empty($tags))
		{
			JError::raiseError(404, JText::_('COM_TAGS_TAG_NOT_FOUND'));
			return;
		}

		// Load plugins
		JPluginHelper::importPlugin('tags');
		$dispatcher = JDispatcher::getInstance();

		// Get configuration
		$config = JFactory::getConfig();

		// Incoming paging vars
		$this->view->filters = array(
			'limit' => JRequest::getInt('limit', $config->getValue('config.list_limit')),
			'start' => JRequest::getInt('limitstart', 0),
			'sort'  => JRequest::getVar('sort', 'date')
		);
		if (!in_array($this->view->filters['sort'], array('date', 'title')))
		{
			throw new Exception(JText::sprintf('Invalid sort value of "%s".', $this->view->filters), 404);
		}

		// Get the active category
		$area = JRequest::getString('area', '');

		$this->view->categories = $dispatcher->trigger('onTagView', array(
			$tags,
			$this->view->filters['limit'],
			$this->view->filters['start'],
			$this->view->filters['sort'],
			$area
		));

		$this->view->total   = 0;
		$this->view->results = null;

		if (!$area)
		{
			$query = '';
			if ($this->view->categories)
			{
				$s = array();
				foreach ($this->view->categories as $response)
				{
					$this->view->total += $response['total'];

					if (is_array($response['sql']))
					{
						continue;
					}
					if (trim($response['sql']) != '')
					{
						$s[] = $response['sql'];
					}
					if (isset($response['children']))
					{
						foreach ($response['children'] as $sresponse)
						{
							//$this->view->total += $sresponse['total'];

							if (is_array($sresponse['sql']))
							{
								continue;
							}
							if (trim($sresponse['sql']) != '')
							{
								$s[] = $sresponse['sql'];
							}
						}
					}
				}
				$query .= "(";
				$query .= implode(") UNION (", $s);
				$query .= ") ORDER BY ";
				switch ($this->view->filters['sort'])
				{
					case 'title': $query .= 'title ASC, publish_up';  break;
					case 'id':    $query .= "id DESC";                break;
					case 'date':
					default:      $query .= 'publish_up DESC, title'; break;
				}
				if ($this->view->filters['limit'] != 'all'
				 && $this->view->filters['limit'] > 0)
				{
					$query .= " LIMIT " . $this->view->filters['start'] . "," . $this->view->filters['limit'];
				}
			}
			$this->database->setQuery($query);
			$this->view->results = $this->database->loadObjectList();
		}
		else
		{
			if ($this->view->categories)
			{
				foreach ($this->view->categories as $response)
				{
					$this->view->total += $response['total'];
				}
				foreach ($this->view->categories as $response)
				{
					//$this->view->total += $response['total'];

					if (is_array($response['results']))
					{
						$this->view->results = $response['results'];
						break;
					}

					if (isset($response['children']))
					{
						foreach ($response['children'] as $sresponse)
						{
							//$this->view->total += $sresponse['total'];

							if (is_array($sresponse['results']))
							{
								$this->view->results = $sresponse['results'];
								break 2;
							}
						}
					}
				}
			}
		}

		$related = null;
		if (count($tags) == 1)
		{
			$this->view->tagstring = $tags[0]->get('tag');
		}
		else
		{
			$tagstring = array();
			foreach ($tags as $tag)
			{
				$tagstring[] = $tag->get('tag');
			}
			$this->view->tagstring = implode('+', $tagstring);
		}

		// Set the pathway
		$this->_buildPathway($tags);

		// Set the page title
		$this->_buildTitle($tags);

		// Output HTML
		if (JRequest::getVar('format', '') == 'xml')
		{
			$this->view->setLayout('view_xml');
		}

		$this->view->tags   = $tags;
		$this->view->active = $area;
		$this->view->search = implode(', ', $rt);

		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		$this->view->display();
	}

	/**
	 * Returns results (JSON format) for a search string
	 * Used for autocompletion scripts called via AJAX
	 *
	 * @return  string  JSON
	 */
	public function autocompleteTask()
	{
		$filters = array(
			'limit'  => 20,
			'start'  => 0,
			'admin'  => 0,
			'search' => trim(JRequest::getString('value', ''))
		);

		// Create a Tag object
		$cloud = new TagsModelCloud();

		// Fetch results
		$rows = $cloud->tags('list', $filters);

		// Output search results in JSON format
		$json = array();
		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
				$name = str_replace("\n", '', stripslashes(trim($row->get('raw_tag'))));
				$name = str_replace("\r", '', $name);

				$item = array(
					'id'   => $row->get('tag'),
					'name' => $name
				);

				// Push exact matches to the front
				if ($row->get('tag') == $filters['search'])
				{
					array_unshift($json, $item);
				}
				else
				{
					$json[] = $item;
				}
			}
		}

		echo json_encode($json);
	}

	/**
	 * Generate an RSS feed
	 *
	 * @return  string  RSS
	 */
	public function feedTask()
	{
		// Incoming
		$tagstring = trim(JRequest::getVar('tag', '', 'request', 'none', 2));

		// Ensure we were passed a tag
		if (!$tagstring)
		{
			JError::raiseError(404, JText::_('COM_TAGS_NO_TAG'));
			return;
		}

		// Break the string into individual tags
		$tgs = explode(',', $tagstring);

		// Sanitize the tag
		$tags  = array();
		$added = array();
		foreach ($tgs as $tag)
		{
			// Load the tag
			$tagobj = TagsModelTag::getInstance($tag);

			if (in_array($tagobj->get('tag'), $added))
			{
				continue;
			}

			$added[] = $tagobj->get('tag');

			// Ensure we loaded the tag's info from the database
			if ($tagobj->exists())
			{
				$tags[] = $tagobj;
			}
		}

		// Get configuration
		$config = JFactory::getConfig();

		// Paging variables
		$limitstart = JRequest::getInt('limitstart', 0);
		$limit = JRequest::getInt('limit', $config->getValue('config.list_limit'));

		// Load plugins
		JPluginHelper::importPlugin('tags');
		$dispatcher = JDispatcher::getInstance();

		$areas = array();
		$searchareas = $dispatcher->trigger('onTagAreas');
		foreach ($searchareas as $area)
		{
			$areas = array_merge($areas, $area);
		}

		// Get the active category
		$area = JRequest::getVar('area', '');
		$sort = JRequest::getVar('sort', '');

		if ($area)
		{
			$activeareas = array($area);
		}
		else
		{
			$activeareas = $areas;
		}

		// Get the search results
		if (count($activeareas) > 1)
		{
			$sqls = $dispatcher->trigger('onTagView',
				array(
					$tags,
					$limit,
					$limitstart,
					$sort,
					$activeareas
				)
			);
			if ($sqls)
			{
				$s = array();
				foreach ($sqls as $sql)
				{
					if (!is_string($sql))
					{
						continue;
					}
					if (trim($sql) != '')
					{
						$s[] = $sql;
					}
				}
				$query  = "(";
				$query .= implode(") UNION (", $s);
				$query .= ") ORDER BY ";
				switch ($sort)
				{
					case 'title': $query .= 'title ASC, publish_up';  break;
					case 'id':    $query .= "id DESC";                break;
					case 'date':
					default:      $query .= 'publish_up DESC, title'; break;
				}
				$query .= ($limit != 'all' && $limit > 0) ? " LIMIT $limitstart, $limit" : "";
			}
			$this->database->setQuery($query);
			$results = array($this->database->loadObjectList());
		}
		else
		{
			$results = $dispatcher->trigger('onTagView',
				array(
					$tags,
					$limit,
					$limitstart,
					$sort,
					$activeareas
				)
			);
		}

		$jconfig = JFactory::getConfig();

		// Run through the array of arrays returned from plugins and find the one that returned results
		$rows = array();
		if ($results)
		{
			foreach ($results as $result)
			{
				if (is_array($result) && !empty($result))
				{
					$rows = $result;
					break;
				}
			}
		}

		// Build some basic RSS document information
		$title = JText::_(strtoupper($this->_option)) . ': ';
		for ($i=0, $n=count($tags); $i < $n; $i++)
		{
			if ($i > 0)
			{
				$title .= '+ ';
			}
			$title .= $tags[$i]->get('raw_tag') . ' ';
		}
		$title = trim($title);
		$title .= ': ' . $area;

		include_once(JPATH_ROOT . DS . 'libraries' . DS . 'joomla' . DS . 'document' . DS . 'feed' . DS . 'feed.php');

		// Set the mime encoding for the document
		$jdoc = JFactory::getDocument();
		$jdoc->setMimeEncoding('application/rss+xml');

		// Start a new feed object
		$doc = new JDocumentFeed;
		$doc->link        = JRoute::_('index.php?option=' . $this->_option);
		$doc->title       = $jconfig->getValue('config.sitename') . ' - ' . $title;
		$doc->description = JText::sprintf('COM_TAGS_RSS_DESCRIPTION', $jconfig->getValue('config.sitename'), $title);
		$doc->copyright   = JText::sprintf('COM_TAGS_RSS_COPYRIGHT', date("Y"), $jconfig->getValue('config.sitename'));
		$doc->category    = JText::_('COM_TAGS_RSS_CATEGORY');

		// Start outputing results if any found
		if (count($rows) > 0)
		{
			include_once(JPATH_ROOT . DS . 'components' . DS . 'com_resources' . DS . 'helpers' . DS . 'helper.php');

			foreach ($rows as $row)
			{
				// Prepare the title
				$title = strip_tags($row->title);
				$title = html_entity_decode($title);

				// Strip html from feed item description text
				$description = html_entity_decode(\Hubzero\Utility\String::truncate(\Hubzero\Utility\Sanitize::stripAll(stripslashes($row->ftext)),300));
				$author = '';
				@$date = ($row->publish_up ? date('r', strtotime($row->publish_up)) : '');

				if (isset($row->data3) || isset($row->rcount))
				{
					$resourceEx = new ResourcesHelper($row->id, $this->database);
					$resourceEx->getCitationsCount();
					$resourceEx->getLastCitationDate();
					$resourceEx->getContributors();

					$author = strip_tags($resourceEx->contributors);
				}

				// Load individual item creator class
				$item = new JFeedItem();
				$item->title       = $title;
				$item->link        = $row->href;
				$item->description = $description;
				$item->date        = $date;
				$item->category    = (isset($row->data1)) ? $row->data1 : '';
				$item->author      = $author;

				// Loads item info into rss array
				$doc->addItem($item);
			}
		}

		// Output the feed
		echo $doc->render();
	}

	/**
	 * Browse the list of tags
	 *
	 * @return  void
	 */
	public function browseTask()
	{
		// Instantiate a new view
		if (JRequest::getVar('format', '') == 'xml')
		{
			$this->view->setLayout('browse_xml');
		}

		// Get configuration
		$jconfig = JFactory::getConfig();
		$app = JFactory::getApplication();

		// Incoming
		$this->view->filters = array(
			'admin' => 0
		);
		$this->view->filters['start'] = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limitstart',
			'limitstart',
			0,
			'int'
		);
		$this->view->filters['search']       = urldecode($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.search',
			'search',
			''
		));

		// Fallback support for deprecated sorting option
		if ($sortby = JRequest::getVar('sortby'))
		{
			JRequest::setVar('sort', $sortby);
		}
		$this->view->filters['sort'] = urldecode($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sort',
			'sort',
			'raw_tag'
		));
		$this->view->filters['sort_Dir'] = strtolower($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sort_Dir',
			'sortdir',
			'asc'
		));
		if (!in_array($this->view->filters['sort'], array('raw_tag', 'total')))
		{
			$this->view->filters['sort'] = 'raw_tag';
		}
		if (!in_array($this->view->filters['sort_Dir'], array('asc', 'desc')))
		{
			$this->view->filters['sort_Dir'] = 'asc';
		}

		$this->view->total = 0;

		$t = new TagsModelCloud();

		$order = JRequest::getVar('order', '');
		if ($order == 'usage')
		{
			$limit = $app->getUserStateFromRequest(
				$this->_option . '.' . $this->_controller . '.limit',
				'limit',
				$jconfig->getValue('config.list_limit'),
				'int'
			);

			$this->view->rows = $t->tags('list', array(
				'limit'    => $limit,
				'admin'    => 0,
				'sort'     => 'total',
				'sort_Dir' => 'DESC',
				'by'       => 'user'
			));
		}
		else
		{
			// Record count
			$this->view->total = $t->tags('count', $this->view->filters);

			$this->view->filters['limit'] = $app->getUserStateFromRequest(
				$this->_option . '.' . $this->_controller . '.limit',
				'limit',
				$jconfig->getValue('config.list_limit'),
				'int'
			);

			// Get records
			$this->view->rows = $t->tags('list', $this->view->filters);

			// Initiate paging
			jimport('joomla.html.pagination');
			$this->view->pageNav = new JPagination(
				$this->view->total,
				$this->view->filters['start'],
				$this->view->filters['limit']
			);
		}

		// Set the pathway
		$this->_buildPathway();

		// Set the page title
		$this->_buildTitle();

		$this->view->config = $this->config;

		// Output HTML
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		$this->view->display();
	}

	/**
	 * Create a new tag
	 *
	 * @return  void
	 */
	public function createTask()
	{
		$this->editTask();
	}

	/**
	 * Show a form for editing a tag
	 *
	 * @param   object  $tag  TagsTableTag
	 * @return  void
	 */
	public function editTask($tag=NULL)
	{
		// Check that the user is authorized
		if (!$this->config->get('access-edit-tag'))
		{
			JError::raiseWarning(403, JText::_('ALERTNOTAUTH'));
			return;
		}

		// Load a tag object if one doesn't already exist
		if (is_object($tag))
		{
			$this->view->tag = $tag;
		}
		else
		{
			// Incoming
			$this->view->tag = new TagsModelTag(intval(JRequest::getInt('id', 0, 'request')));
		}

		$this->view->filters = array(
			'limit'    => JRequest::getInt('limit', 0),
			'start'    => JRequest::getInt('limitstart', 0),
			'sort'     => JRequest::getVar('sort', ''),
			'sort_Dir' => JRequest::getVar('sortdir', ''),
			'search'   => urldecode(JRequest::getString('search', ''))
		);

		// Set the pathway
		$this->_buildPathway();

		// Set the page title
		$this->_buildTitle();

		// Pass error messages to the view
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		$this->view
			->setLayout('edit')
			->display();
	}

	/**
	 * Cancel a task and redirect to the main listing
	 *
	 * @return  void
	 */
	public function cancelTask()
	{
		$return = JRequest::getVar('return', 'index.php?option=' . $this->_option . '&task=browse', 'get');

		$this->setRedirect(
			JRoute::_($return)
		);
	}

	/**
	 * Save a tag
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Check that the user is authorized
		if (!$this->config->get('access-edit-tag'))
		{
			JError::raiseWarning(403, JText::_('ALERTNOTAUTH'));
			return;
		}

		$tag = JRequest::getVar('fields', array(), 'post');

		// Bind incoming data
		$row = new TagsModelTag(intval($tag['id']));
		if (!$row->bind($tag))
		{
			$this->setError($row->getError());
			$this->editTask($row);
			return;
		}

		// Store new content
		if (!$row->store(true))
		{
			$this->setError($row->getError());
			$this->editTask($row);
			return;
		}

		$limit  = JRequest::getInt('limit', 0);
		$start  = JRequest::getInt('limitstart', 0);
		$sortby = JRequest::getInt('sortby', '');
		$search = urldecode(JRequest::getString('search', ''));

		// Redirect to main listing
		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->_option . '&task=browse&search=' . urlencode($search) . '&sortby=' . $sortby . '&limit=' . $limit . '&limitstart=' . $start)
		);
	}

	/**
	 * Delete one or more tags
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		// Check that the user is authorized
		if (!$this->config->get('access-delete-tag'))
		{
			JError::raiseWarning(403, JText::_('ALERTNOTAUTH'));
			return;
		}

		// Incoming
		$ids = JRequest::getVar('id', array());
		if (!is_array($ids))
		{
			$ids = array();
		}

		// Make sure we have an ID
		if (empty($ids))
		{
			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->_option . '&task=browse')
			);
			return;
		}

		// Get Tags plugins
		JPluginHelper::importPlugin('tags');
		$dispatcher = JDispatcher::getInstance();

		foreach ($ids as $id)
		{
			$id = intval($id);

			// Remove references to the tag
			$dispatcher->trigger('onTagDelete', array($id));

			// Remove the tag
			$tag = new TagsModelTag($id);
			$tag->delete();
		}

		$this->cleancacheTask(false);

		// Get the browse filters so we can go back to previous view
		$search = JRequest::getVar('search', '');
		$sortby = JRequest::getVar('sortby', '');
		$limit  = JRequest::getInt('limit', 25);
		$start  = JRequest::getInt('limitstart', 0);
		$count  = JRequest::getInt('count', 1);

		// Redirect back to browse mode
		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->_option . '&task=browse&search=' . $search . '&sortby=' . $sortby . '&limit=' . $limit . '&limitstart=' . $start . '#count' . $count)
		);
	}

	/**
	 * Clean cached tags data
	 *
	 * @param   boolean  $redirect  Redirect after?
	 * @return  void
	 */
	public function cleancacheTask($redirect=true)
	{
		$conf = JFactory::getConfig();

		$cache = JCache::getInstance('', array(
			'defaultgroup' => '',
			'storage'      => $conf->get('cache_handler', ''),
			'caching'      => true,
			'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache')
		));
		$cache->clean('tags');

		if (!$redirect)
		{
			return true;
		}

		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->_option . '&task=browse')
		);
	}

	/**
	 * Method to set the document path
	 *
	 * @param   array  $tags  Tags currently viewing
	 * @return  void
	 */
	protected function _buildPathway($tags=null)
	{
		$pathway = JFactory::getApplication()->getPathway();

		if (count($pathway->getPathWay()) <= 0)
		{
			$pathway->addItem(
				JText::_(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
		if ($this->_task && $this->_task != 'view' && $this->_task != 'display')
		{
			$pathway->addItem(
				JText::_(strtoupper($this->_option) . '_' . strtoupper($this->_task)),
				'index.php?option=' . $this->_option . '&task=' . $this->_task
			);
		}
		if (is_array($tags) && count($tags) > 0)
		{
			$t = array();
			$l = array();
			foreach ($tags as $tag)
			{
				$t[] = stripslashes($tag->get('raw_tag'));
				$l[] = $tag->get('tag');
			}

			$pathway->addItem(
				implode(' + ', $t),
				'index.php?option=' . $this->_option . '&tag=' . implode('+', $l)
			);
		}
	}

	/**
	 * Method to build and set the document title
	 *
	 * @param   array  $tags  Tags currently viewing
	 * @return  void
	 */
	protected function _buildTitle($tags=null)
	{
		$this->view->title = JText::_(strtoupper($this->_option));
		if ($this->_task && $this->_task != 'view' && $this->_task != 'display')
		{
			$this->view->title .= ': ' . JText::_(strtoupper($this->_option) . '_' . strtoupper($this->_task));
		}
		if (is_array($tags) && count($tags) > 0)
		{
			$t = array();
			foreach ($tags as $tag)
			{
				$t[] = stripslashes($tag->get('raw_tag'));
			}
			$this->view->title .= ': ' . implode(' + ', $t);
		}

		JFactory::getDocument()->setTitle($this->view->title);
	}

	/**
	 * Method to check admin access permission
	 *
	 * @return  boolean  True on success
	 */
	protected function _authorize($assetType='tag', $assetId=null)
	{
		$this->config->set('access-view-' . $assetType, true);

		if (!$this->juser->get('guest'))
		{
			$asset  = $this->_option;
			if ($assetId)
			{
				$asset .= ($assetType != 'component') ? '.' . $assetType : '';
				$asset .= ($assetId) ? '.' . $assetId : '';
			}

			$at = '';
			if ($assetType != 'component')
			{
				$at .= '.' . $assetType;
			}

			// Admin
			$this->config->set('access-admin-' . $assetType, $this->juser->authorise('core.admin', $asset));
			$this->config->set('access-manage-' . $assetType, $this->juser->authorise('core.manage', $asset));
			// Permissions
			$this->config->set('access-create-' . $assetType, $this->juser->authorise('core.create' . $at, $asset));
			$this->config->set('access-delete-' . $assetType, $this->juser->authorise('core.delete' . $at, $asset));
			$this->config->set('access-edit-' . $assetType, $this->juser->authorise('core.edit' . $at, $asset));
			$this->config->set('access-edit-state-' . $assetType, $this->juser->authorise('core.edit.state' . $at, $asset));
			$this->config->set('access-edit-own-' . $assetType, $this->juser->authorise('core.edit.own' . $at, $asset));
		}
	}
}
