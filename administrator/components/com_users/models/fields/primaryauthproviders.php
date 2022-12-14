<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2022 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Auth provider field.
 *
 * @since  3.10.7
 */
class JFormFieldPrimaryauthproviders extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.10.7
	 */
	protected $type = 'Primaryauthproviders';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects
	 *
	 * @since   3.10.7
	 */
	protected function getOptions()
	{
		// Build the filter options.
		$options = array();

		PluginHelper::importPlugin('authentication');
		$plugins = PluginHelper::getPlugin('authentication');

		foreach ($plugins as $plugin)
		{
			$className = 'plg' . $plugin->type . $plugin->name;

			if (!class_exists($className))
			{
				continue;
			}

			if (!is_subclass_of(
					$className,
					"Joomla\CMS\Authentication\ProviderAwareAuthenticationPluginInterface"
				)
				|| !$className::isPrimaryProvider())
			{
				continue;
			}

			$options[] = JHtml::_('select.option', $className::getProviderName());
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
