<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * JomSocial Component Controller
 */
class CommunityControllerMailqueue extends CommunityController
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Remove mail queues
	 **/
	public function removequeue()
	{
		$mainframe	= JFactory::getApplication();
		$jinput 	= $mainframe->input;

		$ids	= $jinput->get('cid', array(), 'array');
		$count	= count($ids);

		$row		= JTable::getInstance('mailqueue', 'CommunityTable');

		foreach ($ids as $id) {
			if (!$row->delete($id)) {
				// If there are any error when deleting, we just stop and redirect user with error.
				$message	= JText::_('COM_COMMUNITY_MAILQUEUE_DELETE_ERROR');
				$mainframe->enqueueMessage($message);
				return $this->setRedirect('index.php?option=com_community&view=mailqueue');
			}
		}
		$message	= JText::sprintf('COM_COMMUNITY_MAILQUEUE_SUCCESSFULLY_REMOVE_COUNT', $count);
		$mainframe->enqueueMessage($message);
		$this->setRedirect('index.php?option=com_community&view=mailqueue');
	}

	/**
	 * Purge sent mail queues
	 **/
	public function purgequeue()
	{
		$mainframe	= JFactory::getApplication();

		$model		= $this->getModel('Mailqueue');
		$model->purge();

		$message	= JText::_('COM_COMMUNITY_MAILQUEUE_PURGED');
		$mainframe->enqueueMessage($message);
		$this->setRedirect('index.php?option=com_community&view=mailqueue');
	}

	/**
	 * Execute cron manually
	 */
	public function executeCron()
	{
		$mainframe	= JFactory::getApplication();
		$this->setRedirect(JUri::root(). 'index.php?option=com_community&task=cron');
	}
}
