<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Joompush
 * @author     Weppsol <contact@weppsol.com>
 * @copyright  Copyright (c) 2017 Weppsol Technologies. All rights reserved.
 * @license    GNU GENERAL PUBLIC LICENSE V2 OR LATER.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Subscriber controller class.
 *
 * @since  1.6
 */
class JoompushControllerSubscriber extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'subscribers';
		parent::__construct();
	}
	
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'subscriber', $prefix = 'JoompushModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
	
	/**
	 * Method to send push notification to selected subscriber.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	function sendNotification()
	{
		$input = JFactory::getApplication()->input;
		
		$data = $input->get('jform', array(), 'array');
		
		$model = $this->getModel();
		
		$model->sendNotification($data);
	}
}
