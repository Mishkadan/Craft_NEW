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
class CommunityControllerThemeprofile extends CommunityController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function display( $cachable = false, $urlparams = array() )
    {
        CommunityLicenseHelper::_();
        $jinput = JFactory::getApplication()->input;

        $viewName   = $jinput->get( 'view' , 'community' );

        // Set the default layout and view name
        $layout     = $jinput->get( 'layout' , 'default' );

        // Get the document object
        $document   = JFactory::getDocument();

        // Get the view type
        $viewType   = $document->getType();

        // Get the view
        $view       = $this->getView( $viewName , $viewType );

        $profiles = $this->getModel( 'Profiles');
        $view->setModel($profiles  , false );

        // Set the layout
        $view->setLayout( $layout );

        // Display the view
        $view->display();
    }

    public function reset(){
        $this->apply('reset');
    }

    /**
     *  Save the profile information
     */
    public function apply($action='')
    {
        CommunityLicenseHelper::_();

        JSession::checkToken() or jexit( JText::_( 'COM_COMMUNITY_INVALID_TOKEN' ) );

        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;

        if( JString::strtoupper($jinput->getMethod()) != 'POST')
        {
            return $this->setRedirect( 'index.php?option=com_community&view=themeprofile' , JText::_( 'COM_COMMUNITY_PERMISSION_DENIED' ) , 'error');
        }
        
        $imagePath = COMMUNITY_PATH_ASSETS; // same as the image path
        $groups = JHelperUsergroups::getInstance()->getAll();
        foreach ($groups as $group) {
            if ($jinput->post->get('remove-profile-group-badge-' . $group->id, NULL, 'NONE')) {
                $ext = $jinput->post->get('remove-profile-group-badge-' . $group->id, NULL, 'NONE');
                $removeBadge = $imagePath . '/' . 'profile-group-badge-' . $group->id . '.' . $ext;
                $removeBadgeThumb = $imagePath . '/' . 'profile-group-badge-' . $group->id . '-thumb.' . $ext;
                
                if (file_exists($removeBadge)) JFile::delete($removeBadge);
                if (file_exists($removeBadgeThumb)) JFile::delete($removeBadgeThumb);

                $message = JText::_('COM_COMMUNITY_THEME_PROFILE_UPDATED');
                return $this->setRedirect('index.php?option=com_community&view=themeprofile' , $message, 'message');
            }
        }

        
        $settings = $jinput->post->get('settings', array(), 'array');
        $configs = $jinput->post->get('config', null, 'array');

        // save the config first
        $model  = $this->getModel( 'Configuration' );

        if(!empty($configs)){
            // reset data
            if($action=='reset'){
                $configs['default_profile_tab'] = 0;
                $configs['use_gravatar'] = 0;
                $configs['show_profile_card'] = 1;
                $configs['activityLimit']= 5;
            }
            $model->save($configs);
        }

        $helper = new CommunityThemeHelper();
        // First, parse the Cover Info fields and unset the extra keys
        $settings = $helper->prepareCoverInfo($settings);

        // Handle image uploads
        #echo "<pre>";var_dump($post);die();

        foreach ($groups as $group) {
            $idname = 'profile-group-badge-' . $group->id;
            $images[$idname] = $jinput->files->get($idname, '', 'NONE');
        }

        $images['default-cover']           = $jinput->files->get('default-cover-new', '', 'NONE');
        $images['default-cover-male']      = $jinput->files->get('default-cover-male-new', '', 'NONE');
        $images['default-cover-female']    = $jinput->files->get('default-cover-female-new', '', 'NONE');
        $images['default-male-avatar']     = $jinput->files->get('default-male-avatar-new', '', 'NONE');
        $images['default-female-avatar']   = $jinput->files->get('default-female-avatar-new', '', 'NONE');
        $images['default-general-avatar']  = $jinput->files->get('default-general-avatar-new', '', 'NONE');

        foreach($images as $key => $image) {

            if($action=='reset'){
                $imageJpg = $imagePath . '/' . $key.'.jpg';
                $imagePng = $imagePath . '/' . $key.'.png';
                $imagePngThumb = $imagePath . '/' . $key.'-thumb.png';
                $imageJpgThumb = $imagePath . '/' . $key.'-thumb.jpg';
                
                if (file_exists($imageJpg)) JFile::delete($imageJpg);
                if (file_exists($imagePng)) JFile::delete($imagePng);
                if (file_exists($imagePngThumb)) JFile::delete($imagePngThumb);
                if (file_exists($imageJpgThumb)) JFile::delete($imageJpgThumb);

                //$settings[$key]='wwww';
            }else{
                if (!empty($image['tmp_name']) && isset($image['name']) && !empty($image['name'])) {

                    try {
                        CImageHelper::autorotate($image['tmp_name']);
                    } catch(Exception $e){};

                    //check the file extension first and only allow jpg or png
                    $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($ext, array('jpg', 'jpeg', 'png')) || ($image['type'] != 'image/png' && $image['type'] != 'image/jpeg')) {
                        return $this->setRedirect('index.php?option=com_community&view=themeprofile', JText::_('COM_COMMUNITY_THEME_IMAGE_ERROR'), 'error');
                    }

                    $imageJpg = $imagePath . '/' . $key.'.jpg';
                    $imagePng = $imagePath . '/' . $key.'.png';
                    $imageJpgThumb = $imagePath . '/' . $key.'-thumb.jpg';
                    $imagePngThumb = $imagePath . '/' . $key.'-thumb.png';

                    //check if existing image exist, if yes, delete it
                    if (file_exists($imageJpg)) JFile::delete($imageJpg);
                    if (file_exists($imagePng)) JFile::delete($imagePng);
                    if (file_exists($imageJpgThumb)) JFile::delete($imageJpgThumb);
                    if (file_exists($imagePngThumb)) JFile::delete($imagePngThumb);

                    //let move the tmp image to the actual path
                    $finalPath = $imagePath . $key . '.' . $ext;
                    $finalPathThumb = $imagePath . $key . '-thumb.' . $ext;
                    
                    move_uploaded_file($image['tmp_name'], $finalPath);

                    require_once(JPATH_ROOT."/components/com_community/helpers/image.php");

                    if(strstr($key,'avatar') || strstr($key,'badge')) {
                        // avatars check 1:1
                        $size = CImageHelper::getSize($finalPath);
                        if($size->height != $size->width && strstr($key,'avatar')) $message = JTEXT::_('COM_COMMUNITY_THEME_AVATAR_RESIZED');

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
        }

        // Parse the rest of the settings afterwards
        $helper->parseSettings($settings,'profile');

        // There isn't much that can go wrong, no validation required
        if(!$message) $message = JText::_( 'COM_COMMUNITY_THEME_PROFILE_UPDATED' );
        $this->setRedirect( 'index.php?option=com_community&view=themeprofile' , $message, 'message' );
    }
}