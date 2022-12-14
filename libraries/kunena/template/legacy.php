<?php
/**
 * Kunena Component
 * @package       Kunena.Framework
 * @subpackage    Template
 *
 * @copyright     Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class KunenaTemplateLegacy is needed to load support for legacy templates
 * @since Kunena
 * 
 * @deprecated  5.2  Class will be removed into Kunena 6.0
 */
class KunenaTemplateLegacy
{
	/**
	 * @since Kunena
	 * @return void
	 */
	public static function load()
	{
	}
}

$app = Factory::getApplication('Site');

if (!defined('DS'))
{
	define('DS', '/');
}

// Default values
define('KUNENA_TEMPLATE_DEFAULT', 'crypsis');

// File system paths
define('KUNENA_COMPONENT_RELPATH', 'components/' . KUNENA_COMPONENT_NAME);

define('KUNENA_ROOT_PATH', JPATH_ROOT);
define('KUNENA_ROOT_PATH_ADMIN', JPATH_ADMINISTRATOR);

define('KUNENA_PATH', KUNENA_ROOT_PATH . '/' . KUNENA_COMPONENT_RELPATH);
define('KUNENA_PATH_TEMPLATE', KUNENA_PATH . '/template');
define('KUNENA_PATH_TEMPLATE_DEFAULT', KUNENA_PATH_TEMPLATE . '/' . KUNENA_TEMPLATE_DEFAULT);

define('KUNENA_PATH_ADMIN', KUNENA_ROOT_PATH_ADMIN . '/' . KUNENA_COMPONENT_RELPATH);

// Kunena uploaded files directory
define('KUNENA_RELPATH_UPLOADED', 'media/kunena/attachments');
define('KUNENA_PATH_UPLOADED', KUNENA_ROOT_PATH . '/' . KUNENA_RELPATH_UPLOADED);

// Kunena uploaded avatars directory
define('KUNENA_RELPATH_AVATAR_UPLOADED', '/media/kunena/avatars');
define('KUNENA_PATH_AVATAR_UPLOADED', KUNENA_ROOT_PATH . KUNENA_RELPATH_AVATAR_UPLOADED);

// Kunena legacy uploaded files directory
define('KUNENA_RELPATH_UPLOADED_LEGACY', '/images/fbfiles');
define('KUNENA_PATH_UPLOADED_LEGACY', KUNENA_ROOT_PATH . KUNENA_RELPATH_UPLOADED_LEGACY);

// Legacy version information
define('KUNENA_VERSION', KunenaForum::version());
define('KUNENA_VERSION_DATE', KunenaForum::versionDate());
define('KUNENA_VERSION_NAME', KunenaForum::versionName());
define('KUNENA_VERSION_BUILD', 0);

// Joomla URL
define('KUNENA_JLIVEURL', Uri::base(true) . '/');

// Joomla template dir
define('KUNENA_JTEMPLATEPATH', KUNENA_ROOT_PATH . "/templates/{$app->getTemplate()}");
define('KUNENA_JTEMPLATEURL', KUNENA_JLIVEURL . "templates/{$app->getTemplate()}");

// Kunena live URL
define('KUNENA_LIVEURL', KUNENA_JLIVEURL . 'index.php?option=com_kunena');
define('KUNENA_LIVEURLREL', 'index.php?option=com_kunena');

// Kunena files URL
define('KUNENA_DIRECTURL', KUNENA_JLIVEURL . 'components/com_kunena/');

// Template paths
define('KUNENA_RELTMPLTPATH', KunenaFactory::getTemplate()->name);
define('KUNENA_ABSTMPLTPATH', KUNENA_PATH_TEMPLATE . '/' . KUNENA_RELTMPLTPATH);

// Template URLs
define('KUNENA_TMPLTURL', KUNENA_DIRECTURL . 'template/' . KUNENA_RELTMPLTPATH . '/');
define('KUNENA_TMPLTMAINIMGURL', KUNENA_DIRECTURL . 'template/' . KUNENA_RELTMPLTPATH . '/');
define('KUNENA_TMPLTCSSURL', KUNENA_TMPLTURL . 'css/kunena.forum-min.css');

/**
 * Class CKunenaTools is legacy class from Kunena 1.6/1.7
 * @since Kunena
 * 
 * @deprecated  5.2  Class will be removed into Kunena 6.0
 */
class CKunenaTools
{
}
