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

jimport( 'joomla.application.component.controller' );

/**
 * JomSocial Component Controller
 */
class CommunityControllerThemeevents extends CommunityController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function display( $cachable = false, $urlparams = array() )
    {
        CommunityLicenseHelper::_();
        $jinput = JFactory::getApplication()->input;

        $viewName	= $jinput->get( 'view' , 'community' );

        // Set the default layout and view name
        $layout		= $jinput->get( 'layout' , 'default' );

        // Get the document object
        $document	= JFactory::getDocument();

        // Get the view type
        $viewType	= $document->getType();

        // Get the view
        $view		= $this->getView( $viewName , $viewType );

        //$profiles = $this->getModel( 'Groups');
        //$view->setModel($profiles  , false );

        // Set the layout
        $view->setLayout( $layout );

        // Display the view
        $view->display();
    }

    /**
     *  Save the profile information
     */
    public function apply()
    {
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if( JString::strtoupper($jinput->getMethod()) != 'POST')
        {
            return $this->setRedirect( 'index.php?option=com_community&view=themeevents' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
        }

        // save the config first
        $configs = $jinput->post->get('config', null, 'array');
        $model	= $this->getModel( 'Configuration' );
        $helper = new CommunityThemeHelper();

        $settings = array();
        $images['default-cover-event']       = $jinput->files->get('default-cover-event' , '', 'NONE');

        foreach($images as $key => $image) {
            if (!empty($image['tmp_name']) && isset($image['name']) && !empty($image['name'])) {

                try {
                    CImageHelper::autorotate($image['tmp_name']);
                } catch(Exception $e){};

                $imagePath = COMMUNITY_PATH_ASSETS; // same as the image path

                //check the file extension first and only allow jpg or png
                $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, array('jpg', 'jpeg', 'png')) || ($image['type'] != 'image/png' && $image['type'] != 'image/jpeg')) {
                    return $this->setRedirect('index.php?option=com_community&view=themegroups', JText::_('COM_COMMUNITY_THEME_IMAGE_ERROR'), 'error');
                }

                $imageJpg = $imagePath . '/' . $key.'.jpg';
                $imagePng = $imagePath . '/' . $key.'.png';

                //check if existing image exist, if yes, delete it
                if (file_exists($imageJpg)) unlink($imageJpg);
                if (file_exists($imagePng)) unlink($imagePng);

                //let move the tmp image to the actual path
                $finalPath = $imagePath . $key . '.' . $ext;
                $finalPathThumb = $imagePath . $key . '-thumb.' . $ext;
                move_uploaded_file($image['tmp_name'], $finalPath);

                require_once(JPATH_ROOT."/components/com_community/helpers/image.php");

                if(strstr($key,'avatar')) {
                    //avatars

                    // Check 1:1
                    $size = CImageHelper::getSize($finalPath);
                    if($size->height != $size->width) $message = JTEXT::_('COM_COMMUNITY_THEME_AVATAR_RESIZED');

                    CImageHelper::resize($finalPath, $finalPath, "image/$ext", 160, 160);

                    // thumb
                    CImageHelper::resize($finalPath, $finalPathThumb, "image/$ext", 64, 64);

                } else {
                    // other images
                    CImageHelper::resizeProportional($finalPath, $finalPath, "image/$ext", 1000, 1000);
                }
                $settings[$key]=$ext;
            }
        }

        // Parse the rest of the settings afterwards
        $helper->parseSettings($settings, 'event');

        if(!empty($configs)){
            $model->save($configs);
        }


        // There isn't much that can go wrong, no validation required
        if(!$message) $message = JText::_( 'COM_COMMUNITY_THEME_PROFILE_UPDATED' );
        return $this->setRedirect( 'index.php?option=com_community&view=themeevents' , $message, 'message' );
    }
}