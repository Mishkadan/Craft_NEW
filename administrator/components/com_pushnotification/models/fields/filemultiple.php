<?php
/*
* @package 		com_pushnotification - Push Notification
* @version		1.0.2
* @created		Jan 2020
* @author		ExtensionCoder.com
* @email		developer@extensioncoder.com
* @website		https://www.extensioncoder.com
* @support		https://www.extensioncoder.com/support.html
* @copyright	Copyright (C) 2019-2020 ExtensionCoder. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldFileMultiple extends \Joomla\CMS\Form\FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'file';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = '<input type="file" name="' . $this->name . '[]" multiple>';

		return $html;
	}
}
