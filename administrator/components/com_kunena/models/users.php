<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Administrator
 * @subpackage      Models
 *
 * @copyright       Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.modellist');

/**
 * Users Model for Kunena
 *
 * @since  2.0
 */
class KunenaAdminModelUsers extends \Joomla\CMS\MVC\Model\ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      Kunena
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'username',
				'name',
				'rank',
				'email',
				'ip',
				'signature',
				'enabled',
				'banned',
				'moderator',
			);
		}

		$this->me = KunenaUserHelper::getMyself();

		parent::__construct($config);
	}

	/**
	 * Method to get User objects of data items.
	 *
	 * @return boolean|KunenaUser
	 *
	 * @throws Exception
	 * @since   3.0
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();

		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage());

			return false;
		}

		$ids = array();

		foreach ($items as $item)
		{
			$ids[] = $item->id;
		}

		$instances = KunenaUserHelper::loadUsers($ids);

		// Add the items to the internal cache.
		$this->cache[$store] = $instances;

		return $this->cache[$store];
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 * @since Kunena
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.username');
		$id .= ':' . $this->getState('filter.email');
		$id .= ':' . $this->getState('filter.ip');
		$id .= ':' . $this->getState('filter.rank');
		$id .= ':' . $this->getState('filter.signature');
		$id .= ':' . $this->getState('filter.block');
		$id .= ':' . $this->getState('filter.banned');
		$id .= ':' . $this->getState('filter.moderator');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get html list of Kunena categories
	 *
	 * @return  string
	 *
	 * @throws Exception
	 * @since  3.0
	 */
	public function getModcatslist()
	{
		$options = array();

		if ($this->me->isAdmin())
		{
			$options[] = HTMLHelper::_('select.option', 0, Text::_('COM_KUNENA_GLOBAL_MODERATOR'));
		}

		return HTMLHelper::_('kunenaforum.categorylist', 'catid[]', 0, $options, array('action' => 'admin'), 'class="input-block-level" multiple="multiple" size="5"', 'value', 'text', 0);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   null $ordering  ordering
	 * @param   null $direction direction
	 *
	 * @throws Exception
	 * @since Kunena
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$this->context = 'com_kunena.admin.users';

		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		$layout        = $app->input->get('layout');
		$this->context = 'com_kunena.admin.users';

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$filter_active = '';

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.username', 'filter_username', '', 'string');
		$this->setState('filter.username', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.email', 'filter_email', '', 'string');
		$this->setState('filter.email', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.ip', 'filter_ip', '', 'string');
		$this->setState('filter.ip', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.rank', 'filter_rank', '', 'string');
		$this->setState('filter.rank', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.signature', 'filter_signature', '', 'string');
		$this->setState('filter.signature', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.block', 'filter_block', '', 'string');
		$this->setState('filter.block', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.banned', 'filter_banned', '', 'string');
		$this->setState('filter.banned', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.moderator', 'filter_moderator', '', 'string');
		$this->setState('filter.moderator', $value);

		$this->setState('filter.active', !empty($filter_active));

		// List state information.
		parent::populateState('username', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 * @since Kunena
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id'
			)
		);
		$query->from('#__users AS a');

		// Join over the users for the linked user.
		$query->join('LEFT', '#__kunena_users AS ku ON a.id=ku.userid');

		// Filter by search.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.username LIKE ' . $search . ' OR a.name LIKE ' . $search . ' OR a.email LIKE ' . $search . ' OR ku.ip LIKE ' . $search . ' OR a.id LIKE ' . $search . ')');
			}
		}

		// Filter by username or name.
		$username = $this->getState('filter.username');

		if (!empty($username))
		{
			$username = $db->Quote('%' . $db->escape($username, true) . '%');
			$query->where('a.username LIKE ' . $username . ' OR a.name LIKE ' . $username);
		}

		// Filter by rank.
		$rank = $this->getState('filter.rank');

		if (!empty($rank))
		{
			$rank = $db->Quote('%' . $db->escape($rank, true) . '%');
			$query->where('ku.rank LIKE ' . $rank);
		}

		// Filter by email.
		$email = $this->getState('filter.email');

		if (!empty($email))
		{
			$email = $db->Quote('%' . $db->escape($email, true) . '%');
			$query->where('a.email LIKE ' . $email);
		}

		// Filter by IP.
		$ip = $this->getState('filter.ip');

		if (!empty($ip))
		{
			$ip = $db->Quote('%' . $db->escape($ip, true) . '%');
			$query->where('ku.ip LIKE ' . $ip);
		}

		// Filter by signature.
		$filter = $this->getState('filter.signature');

		if ($filter !== '' && !empty($search))
		{
			if ($filter)
			{
				$query->where("ku.signature!={$db->quote('')} AND ku.signature IS NOT NULL");
			}
			else
			{
				$query->where("ku.signature={$db->quote('')} OR ku.signature IS NULL");
			}
		}

		// Filter by block state.
		$filter = $this->getState('filter.block');

		if ($filter !== '')
		{
			$query->where('a.block=' . (int) $filter);
		}

		// Filter by banned state.
		$filter = $this->getState('filter.banned');

		if ($filter !== '')
		{
			$now = new \Joomla\CMS\Date\Date;

			if ($filter)
			{
				$query->where("ku.banned={$db->quote($db->getNullDate())} OR ku.banned>{$db->quote($now->toSql())}");
			}
			else
			{
				$query->where("ku.banned IS NULL OR (ku.banned>{$db->quote($db->getNullDate())} AND ku.banned<{$db->quote($now->toSql())})");
			}
		}

		// Filter by moderator state.
		$filter = $this->getState('filter.moderator');

		if ($filter !== '')
		{
			$query->where('ku.moderator =' . (int) $filter);
		}

		// Add the list ordering clause.
		$direction = strtoupper($this->state->get('list.direction'));

		switch ($this->state->get('list.ordering'))
		{
			case 'id':
				$query->order('a.id ' . $direction);
				break;
			case 'email':
				$query->order('a.email ' . $direction);
				break;
			case 'ip':
				$query->order('ku.ip ' . $direction);
				break;
			case 'rank':
				$query->order('ku.rank ' . $direction);
				break;
			case 'signature':
				$query->order('ku.signature ' . $direction);
				break;
			case 'enabled':
				$query->order('a.block ' . $direction);
				break;
			case 'banned':
				$query->order('ku.banned ' . $direction);
				break;
			case 'moderator':
				$query->order('ku.moderator ' . $direction);
				break;
			case 'name':
				$query->order('a.name ' . $direction);
				break;
			case 'username':
			default:
				$query->order('a.username ' . $direction);
		}

		$db->setQuery($query);

		return $query;
	}
}
