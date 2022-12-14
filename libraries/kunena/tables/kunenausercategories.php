<?php
/**
 * Kunena Component
 * @package       Kunena.Framework
 * @subpackage    Tables
 *
 * @copyright     Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

require_once __DIR__ . '/kunena.php';

/**
 * Kunena User Categories Table
 * Provides access to the #__kunena_user_categories table
 * @since Kunena
 */
class TableKunenaUserCategories extends KunenaTable
{
	/**
	 * @var null
	 * @since Kunena
	 */
	public $user_id = null;

	/**
	 * @var null
	 * @since Kunena
	 */
	public $category_id = null;

	/**
	 * @var null
	 * @since Kunena
	 */
	public $role = null;

	/**
	 * @var null
	 * @since Kunena
	 */
	public $allreadtime = null;

	/**
	 * @var null
	 * @since Kunena
	 */
	public $subscribed = null;

	/**
	 * @var null
	 * @since Kunena
	 */
	public $params = null;

	/**
	 * @param   JDatabaseDriver $db Database driver
	 *
	 * @since Kunena
	 */
	public function __construct($db)
	{
		parent::__construct('#__kunena_user_categories', array('user_id', 'category_id'), $db);
	}

	/**
	 * @return boolean
	 * @throws Exception
	 * @since Kunena
	 */
	public function check()
	{
		$user = KunenaUserHelper::get($this->user_id);

		if (!$user->exists())
		{
			$this->setError(Text::sprintf('COM_KUNENA_LIB_TABLE_USERCATEGORIES_ERROR_USER_INVALID', (int) $user->userid));
		}

		$category = KunenaForumCategoryHelper::get($this->category_id);

		if ($this->category_id && !$category->exists())
		{
			$this->setError(Text::sprintf('COM_KUNENA_LIB_TABLE_USERCATEGORIES_ERROR_CATEGORY_INVALID', (int) $category->id));
		}

		return $this->getError() == '';
	}
}
