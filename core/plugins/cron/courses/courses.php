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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * Cron plugin for courses
 */
class plgCronCourses extends \Hubzero\Plugin\Plugin
{
	/**
	 * Return a list of events
	 *
	 * @return  array
	 */
	public function onCronEvents()
	{
		$this->loadLanguage();

		$obj = new stdClass();
		$obj->plugin = $this->_name;
		$obj->events = array(
			array(
				'name'   => 'syncPassportBadgeStatus',
				'label'  => Lang::txt('PLG_CRON_COURSES_SYNC_PASSPORT_BADGE_STATUS'),
				'params' => ''
			),
			array(
				'name'   => 'emailInstructorDigest',
				'label'  => Lang::txt('PLG_CRON_COURSES_EMAIL_INSTRUCTOR_DIGEST'),
				'params' => 'emaildigest'
			),
		);

		return $obj;
	}

	/**
	 * Sync claimed/denied passport badges
	 *
	 * @param   object   $job  \Components\Cron\Models\Job
	 * @return  boolean
	 */
	public function syncPassportBadgeStatus(\Components\Cron\Models\Job $job)
	{
		$params = Component::params('com_courses');

		$badgesHandler  = new Hubzero\Badges\Wallet('passport', $params->get('badges_request_type'));
		$badgesProvider = $badgesHandler->getProvider();

		$creds = new \stdClass();
		$creds->consumer_key    = $params->get('passport_consumer_key');
		$creds->consumer_secret = $params->get('passport_consumer_secret');

		$badgesProvider->setCredentials($creds);

		require_once PATH_CORE . DS . 'components' . DS . 'com_courses' . DS . 'models' . DS . 'courses.php';
		require_once PATH_CORE . DS . 'components' . DS . 'com_courses' . DS . 'models' . DS . 'memberBadge.php';
		$coursesObj = new \Components\Courses\Models\Courses();
		$courses    = $coursesObj->courses();

		if (isset($courses) && count($courses) > 0)
		{
			foreach ($courses as $course)
			{
				if (!$course->isAvailable())
				{
					continue;
				}

				$students = $course->students();
				$emails   = array();

				if ($students && count($students) > 0)
				{
					foreach ($students as $student)
					{
						$emails[] = User::getInstance($student->get('user_id'))->get('email');
					}
				}

				if (count($emails) > 0)
				{
					$assertions = $badgesProvider->getAssertionsByEmailAddress($emails);

					if (isset($assertions) && count($assertions) > 0)
					{
						foreach ($assertions as $assertion)
						{
							$status = false;
							if ($assertion->IsPending)
							{
								$status = false;
							}
							else if ($assertion->IsAccepted)
							{
								$status = 'accept';
							}
							else
							{
								$status = 'deny';
							}

							if ($status)
							{
								preg_match('/validation\/([[:alnum:]-]{20})/', $assertion->EvidenceUrl, $match);

								if (isset($match[1]))
								{
									$badge = \Components\Courses\Models\MemberBadge::loadByToken($match[1]);

									if ($badge && !$badge->get('action'))
									{
										$badge->set('action', $status);
										$badge->set('action_on', Date::toSql());
										$badge->store();
									}
								}
							}
						}
					}
				}
			}
		}

		// Job is no longer active
		return true;
	}

	/**
	 * Email instructor course digest
	 *
	 * @param   object   $job  \Components\Cron\Models\Job
	 * @return  boolean
	 */
	public function emailInstructorDigest(\Components\Cron\Models\Job $job)
	{
		$database = \App::get('db');
		$cconfig  = Component::params('com_courses');

		Lang::load('com_courses') ||
		Lang::load('com_courses', PATH_CORE . DS . 'components' . DS . 'com_courses' . DS . 'site');

		$from = array(
			'name'  => Config::get('sitename') . ' ' . Lang::txt('COM_COURSES'),
			'email' => Config::get('mailfrom')
		);

		$subject = Lang::txt('COM_COURSES') . ': ' . Lang::txt('COM_COURSES_SUBJECT_EMAIL_DIGEST');

		require_once PATH_CORE . DS . 'components' . DS . 'com_courses' . DS . 'models' . DS . 'courses.php';

		$course_id = 0;

		$params = $job->get('params');
		if (isset($params) && is_object($params))
		{
			$course_id = $params->get('course');
		}

		$coursesObj = new \Components\Courses\Models\Courses();

		if ($course_id)
		{
			$courses = array($coursesObj->course($course_id));
		}
		else
		{
			$courses = $coursesObj->courses();
		}

		if (isset($courses) && count($courses) > 0)
		{
			foreach ($courses as $course)
			{
				if (!$course->isAvailable())
				{
					continue;
				}

				$mailed      = array();
				$managers    = $course->managers();
				$enrollments = $course->students(array('count'=>true));
				$offerings   = $course->offerings();

				if (isset($offerings) && count($offerings) > 0)
				{
					foreach ($offerings as $offering)
					{
						if (!$offering->isAvailable())
						{
							continue;
						}

						$offering->gradebook()->refresh();
						$passing = $offering->gradebook()->countPassing(false);
						$failing = $offering->gradebook()->countFailing(false);

						if (isset($managers) && count($managers) > 0)
						{
							foreach ($managers as $manager)
							{
								// Get the user's account
								$user = User::getInstance($manager->get('user_id'));
								if (!$user->get('id'))
								{
									continue;
								}

								// Try to ensure no duplicates
								if (in_array($user->get('username'), $mailed))
								{
									continue;
								}

								// Only mail instructors (i.e. not managers)
								if ($manager->get('role_alias') != 'instructor')
								{
									continue;
								}

								// Get discussion stats and posts
								require_once PATH_CORE . DS . 'components' . DS . 'com_forum' . DS . 'tables' . DS . 'post.php';

								$postsTbl  = new \Components\Forum\Tables\Post($database);
								$filters   = array(
									'scope'    => 'course',
									'scope_id' => $offering->get('id'),
									'state'    => 1,
									'sort'     => 'created',
									'sort_Dir' => 'DESC',
									'limit'    => 100
								);
								$posts      = $postsTbl->find($filters);
								$posts_cnt  = count($posts);
								$latest     = array();
								$latest_cnt = 0;

								if (isset($posts) && $posts_cnt > 0)
								{
									foreach ($posts as $post)
									{
										if (strtotime($post->created) > strtotime('-1 day'))
										{
											$latest[] = $post;
										}
										else
										{
											break;
										}
									}

									$latest_cnt = count($latest);
								}

								$eview = new \Hubzero\Component\View(array(
									'base_path' => PATH_CORE . DS . 'components' . DS . 'com_courses' . DS . 'site',
									'name'      => 'emails',
									'layout'    => 'digest_plain'
								));
								$eview->option      = 'com_courses';
								$eview->controller  = 'courses';
								$eview->delimiter   = '~!~!~!~!~!~!~!~!~!~!';
								$eview->course      = $course;
								$eview->enrollments = $enrollments;
								$eview->passing     = $passing;
								$eview->failing     = $failing;
								$eview->offering    = $offering;
								$eview->posts_cnt   = $posts_cnt;
								$eview->latest      = $latest;
								$eview->latest_cnt  = $latest_cnt;

								$plain = $eview->loadTemplate();
								$plain = str_replace("\n", "\r\n", $plain);

								// HTML
								$eview->setLayout('digest_html');

								$html = $eview->loadTemplate();
								$html = str_replace("\n", "\r\n", $html);

								// Build message
								$message = new \Hubzero\Mail\Message();
								$message->setSubject($subject)
										->addFrom($from['email'], $from['name'])
										->addTo($user->get('email'), $user->get('name'))
										->addHeader('X-Component', 'com_courses')
										->addHeader('X-Component-Object', 'courses_instructor_digest');

								$message->addPart($plain, 'text/plain');
								$message->addPart($html, 'text/html');

								// Send mail
								if (!$message->send())
								{
									$this->setError('Failed to mail %s', $user->get('email'));
								}

								$mailed[] = $user->get('username');
							}
						}
					}
				}
			}
		}

		return true;
	}
}