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

use Joomla\CMS\Factory;

jimport('joomla.application.component.modellist');

/**
 * Ranks Model for Kunena
 *
 * @since 3.0
 */
class KunenaAdminModelRanks extends \Joomla\CMS\MVC\Model\ListModel
{
	/**
	 * @param   array $config config
	 *
	 * @since Kunena
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title',
				'min',
				'special',
				'image',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string $ordering  ordering
	 * @param   string $direction direction
	 *
	 * @return    void
	 * @throws Exception
	 * @since Kunena
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$this->context = 'com_kunena.admin.ranks';

		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		$layout        = $app->input->get('layout');
		$this->context = 'com_kunena.admin.ranks';

		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		$filter_active = '';

		// List state information

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.title', 'filter_title', '', 'string');
		$this->setState('filter.title', $value);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.special', 'filter_special', '', 'string');
		$this->setState('filter.special', $value !== '' ? (int) $value : null);

		$filter_active .= $value = $this->getUserStateFromRequest($this->context . '.filter.min', 'filter_min', '', 'string');
		$this->setState('filter.min', $value !== '' ? (int) $value : null);

		$this->setState('filter.active', !empty($filter_active));

		// List state information.
		parent::populateState('id', 'asc');
	}

	/**
	 * @param   string $id id
	 *
	 * @return string
	 *
	 * @since Kunena
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.title');
		$id .= ':' . $this->getState('filter.special');
		$id .= ':' . $this->getState('filter.min');

		return parent::getStoreId($id);
	}

	/**
	 * @return JDatabaseQuery
	 * @since Kunena
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.rank_id, a.rank_title, a.rank_min, a.rank_special, a.rank_image'
			)
		);

		$query->from('#__kunena_ranks AS a');

		// Filter by access level.
		$filter = $this->getState('filter.title');

		if (!empty($filter))
		{
			$title = $db->Quote('%' . $db->escape($filter, true) . '%');
			$query->where('(a.rank_title LIKE ' . $title . ')');
		}

		$filter = $this->getState('filter.special');

		if (is_numeric($filter))
		{
			$query->where('a.rank_special = ' . (int) $filter);
		}

		$filter = $this->getState('filter.min');

		if (is_numeric($filter))
		{
			$query->where('a.rank_min > ' . (int) $filter);
		}

		// Add the list ordering clause.
		$direction = strtoupper($this->state->get('list.direction'));

		switch ($this->state->get('list.ordering'))
		{
			case 'title':
				$query->order('a.rank_title ' . $direction);
				break;
			case 'min':
				$query->order('a.rank_min ' . $direction);
				break;
			case 'special':
				$query->order('a.rank_special ' . $direction);
				break;
			case 'image':
				$query->order('a.rank_image ' . $direction);
				break;
			default:
				$query->order('a.rank_id ' . $direction);
		}

		$db->setQuery($query);

		return $query;
	}
}
