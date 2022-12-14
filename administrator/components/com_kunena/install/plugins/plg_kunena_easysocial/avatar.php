<?php
/**
 * Kunena Plugin
 *
 * @package         Kunena.Plugins
 * @subpackage      Easysocial
 *
 * @copyright      Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @copyright      Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * EasySocial is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
defined('_JEXEC') or die('Unauthorized Access');

/**
 * Class KunenaAvatarEasySocial
 * @since       Kunena 5.0
 */
class KunenaAvatarEasySocial extends KunenaAvatar
{
	protected $params = null;

	/**
	 * KunenaAvatarEasySocial constructor.
	 *
	 * @param $params
	 *
	 * @since       Kunena 5.0
	 *
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * @param $userlist
	 *
	 * @since       Kunena 5.0
	 *
	 */
	public function load($userlist)
	{
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;

		if (class_exists('CFactory') && method_exists('CFactory', 'loadUsers'))
		{
			CFactory::loadUsers($userlist);
		}

		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;
	}

	/**
	 * @return mixed
	 * @since       Kunena 5.0
	 *
	 */
	public function getEditURL()
	{
		return FRoute::profile(array('layout' => 'edit'));
	}

	/**
	 * @param $user
	 * @param $sizex
	 * @param $sizey
	 *
	 * @return mixed
	 * @since       Kunena 5.0
	 * @throws Exception
	 */
	protected function _getURL($user, $sizex, $sizey)
	{
		$user = KunenaFactory::getUser($user);

		$user = FD::user($user->userid);

		$avatar = $user->getAvatar(SOCIAL_AVATAR_LARGE);

		return $avatar;
	}
}
