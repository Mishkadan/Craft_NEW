<?php
/**
 * Kunena Component
 * @package       Kunena.Framework
 * @subpackage    Forum.Message.Thankyou
 *
 * @copyright     Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Factory;

/**
 * Kunena Forum Message Thank You Helper Class
 * @since 2.0
 */
abstract class KunenaForumMessageThankyouHelper
{
	/**
	 * @var KunenaForumMessageThankyou[]
	 * @since Kunena
	 */
	protected static $_instances = array();

	/**
	 * Cleanup
	 *
	 * @since Kunena
	 * @return void
	 */
	public static function cleanup()
	{
		self::$_instances = array();
	}

	/**
	 * @param   int  $identifier The message to load - Can be only an integer.
	 * @param   bool $reload     reload
	 *
	 * @return KunenaForumMessageThankyou|void
	 * @throws Exception
	 * @since Kunena
	 */
	public static function get($identifier, $reload = false)
	{
		if ($identifier instanceof KunenaForumMessageThankyou)
		{
			return $identifier;
		}

		$id = intval($identifier);

		// TODO: why this returns null? Does it have side effect?
		if ($id < 1)
		{
			return;
		}

		if ($reload || empty(self::$_instances [$id]))
		{
			unset(self::$_instances [$id]);
			self::loadMessages(array($id));
		}

		return self::$_instances [$id];
	}

	/**
	 * Load users who have given thank you to listed messages.
	 *
	 * @param   array $ids ids
	 *
	 * @throws Exception
	 * @since Kunena
	 * @return void
	 */
	protected static function loadMessages(array $ids)
	{
		foreach ($ids as $i => $id)
		{
			$id = intval($id);

			if (!$id || isset(self::$_instances [$id]))
			{
				unset($ids[$i]);
			}
		}

		if (empty($ids))
		{
			return;
		}

		$idlist = implode(',', $ids);

		$db    = Factory::getDBO();
		$query = "SELECT *
				FROM #__kunena_thankyou
				WHERE postid IN ({$idlist})";
		$db->setQuery($query);

		try
		{
			$results = (array) $db->loadObjectList();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		foreach ($ids as $id)
		{
			self::$_instances [$id] = new KunenaForumMessageThankyou($id);
		}

		foreach ($results as $result)
		{
			self::$_instances [$result->postid]->_add($result->userid, $result->time);
		}

		unset($results);
	}

	/**
	 * Get total number of Thank yous.
	 *
	 * @param   int $starttime Starting time as unix timestamp.
	 * @param   int $endtime   Ending time as unix timestamp.
	 *
	 * @return integer
	 * @throws Exception
	 * @since Kunena
	 */
	public static function getTotal($starttime = null, $endtime = null)
	{
		$db    = Factory::getDBO();
		$where = array();

		if (!empty($starttime))
		{
			$where [] = "time >= UNIX_TIMESTAMP({$db->quote(intval($starttime))})";
		}

		if (!empty($endtime))
		{
			$where [] = "time <= UNIX_TIMESTAMP({$db->quote(intval($endtime))})";
		}

		$query = "SELECT COUNT(*) FROM #__kunena_thankyou";

		if (!empty($where))
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		$db->setQuery($query);

		try
		{
			$results = (int) $db->loadResult();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		return $results;
	}

	/**
	 * Get users with most thank yous received / given.
	 *
	 * @param   bool $target     target
	 * @param   int  $limitstart limitstart
	 * @param   int  $limit      limit
	 *
	 * @return array
	 * @throws Exception
	 * @since Kunena
	 */
	public static function getTopUsers($target = true, $limitstart = 0, $limit = 10)
	{
		$field = 'targetuserid';

		if (!$target)
		{
			$field = 'userid';
		}

		$db    = Factory::getDBO();
		$query = "SELECT s.userid, count(s.{$field}) AS countid, u.username
				FROM #__kunena_thankyou AS s
				INNER JOIN #__users AS u
				WHERE s.{$field}=u.id
				GROUP BY s.{$field}
				ORDER BY countid DESC";
		$db->setQuery($query, (int) $limitstart, (int) $limit);

		try
		{
			$results = (array) $db->loadObjectList();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		return $results;
	}

	/**
	 * Get messages with most thank yous given.
	 *
	 * @param   int $limitstart limitstart
	 * @param   int $limit      limit
	 *
	 * @return array
	 * @throws Exception
	 * @since Kunena
	 */
	public static function getTopMessages($limitstart = 0, $limit = 10)
	{
		$db         = Factory::getDBO();
		$categories = KunenaForumCategoryHelper::getCategories();
		$catlist    = implode(',', array_keys($categories));
		$query      = "SELECT s.postid, COUNT(*) AS countid, m.catid, m.thread, m.id, m.subject
				FROM #__kunena_thankyou AS s
				INNER JOIN #__kunena_messages AS m ON s.postid=m.id
				INNER JOIN #__kunena_topics AS tt ON m.thread=tt.id
				WHERE m.catid IN ({$catlist}) AND m.hold=0 AND tt.hold=0
				GROUP BY s.postid
				ORDER BY countid DESC";

		$db->setQuery($query, (int) $limitstart, (int) $limit);

		try
		{
			$results = (array) $db->loadObjectList();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		return $results;
	}

	/**
	 * Get messages where a user received / gave thank you.
	 *
	 * @param   int  $userid     userid
	 * @param   bool $target     target
	 * @param   int  $limitstart limitstart
	 * @param   int  $limit      limit
	 *
	 * @return array
	 * @throws Exception
	 * @since Kunena
	 */
	public static function getUserMessages($userid, $target = true, $limitstart = 0, $limit = 10)
	{
		$db    = Factory::getDBO();
		$field = 'targetuserid';

		if (!$target)
		{
			$field = 'userid';
		}

		$categories = KunenaForumCategoryHelper::getCategories();
		$catlist    = implode(',', array_keys($categories));
		$query      = "SELECT m.catid, m.thread, m.id
				FROM #__kunena_thankyou AS t
				INNER JOIN #__kunena_messages AS m ON m.id=t.postid
				INNER JOIN #__kunena_topics AS tt ON m.thread=tt.id
				WHERE m.catid IN ({$catlist}) AND m.hold=0 AND tt.hold=0 AND t.{$field}={$db->quote(intval($userid))}";
		$db->setQuery($query, (int) $limitstart, (int) $limit);

		try
		{
			$results = (array) $db->loadObjectList();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		return $results;
	}

	/**
	 * Recount thank yous is null.
	 *
	 * @return boolean|integer    Number of rows is successful, false on error.
	 *
	 * @throws Exception
	 * @since K2.0
	 */
	public static function recountThankyou()
	{
		$db = Factory::getDBO();

		// Users who have no thank yous, set thankyou count to 0
		$query = "UPDATE #__kunena_users AS u
			LEFT JOIN #__kunena_thankyou AS t ON t.targetuserid = u.userid
			SET u.thankyou = 0
			WHERE t.targetuserid IS NULL";
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			KunenaError::displayDatabaseError($e);

			return false;
		}

		$rows = $db->getAffectedRows();

		return $rows;
	}

	/**
	 * Recount thank yous.
	 *
	 * @return boolean|integer    Number of rows is successful, false on error.
	 * @throws Exception
	 * @since K2.0
	 */
	public static function recount()
	{
		$db = Factory::getDBO();

		// Update user thankyou count
		$query = "INSERT INTO #__kunena_users (userid, thankyou)
			SELECT targetuserid AS userid, COUNT(*) AS thankyou
			FROM #__kunena_thankyou
			GROUP BY targetuserid
			ON DUPLICATE KEY UPDATE thankyou=VALUES(thankyou)";
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			KunenaError::displayDatabaseError($e);

			return false;
		}

		$rows = $db->getAffectedRows();

		return $rows;
	}

	/**
	 * Return thank yous for the given messages.
	 *
	 * @param   bool|array|int $ids ids
	 *
	 * @return KunenaForumMessageThankyou[]
	 * @throws Exception
	 * @since Kunena
	 */
	public static function getByMessage($ids = false)
	{
		if ($ids === false)
		{
			return self::$_instances;
		}
		elseif (is_array($ids))
		{
			$ids2 = array();

			foreach ($ids as $id)
			{
				if ($id instanceof KunenaForumMessage)
				{
					$id = $id->id;
				}

				$ids2[(int) $id] = (int) $id;
			}

			$ids = $ids2;
		}
		else
		{
			$ids = array($ids);
		}

		self::loadMessages($ids);

		$list = array();

		foreach ($ids as $id)
		{
			if (!empty(self::$_instances [$id]))
			{
				$list[$id] = self::$_instances [$id];
			}
		}

		return $list;
	}
}
