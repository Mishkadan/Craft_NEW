<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunitySystemController extends CommunityBaseController {

    public function ajaxShowInvitationForm($friends, $callback, $cid, $displayFriends, $displayEmail, $type = '') {
        $displayFriends = (bool) $displayFriends;

        $config = CFactory::getConfig();
        $limit = $config->get('friendloadlimit', 8);

        $tmpl = new CTemplate();

        $tmpl->set('displayFriends', $displayFriends);
        $tmpl->set('displayEmail', $displayEmail);
        $tmpl->set('cid', $cid);
        $tmpl->set('callback', $callback);
        $tmpl->set('limit', $limit);
        $tmpl->set('type', $type);
        $html = $tmpl->fetch('ajax.showinvitation');
        
        if ($type == 'page') { 
            $title = JText::_('COM_COMMUNITY_EVENT_INVITE_PAGE_MEMBERS');
        } else if ($type == 'group') { 
            $title = JText::_('COM_COMMUNITY_EVENT_INVITE_GROUP_MEMBERS');
        } else {
            $title = JText::_('COM_COMMUNITY_INVITE_FRIENDS');
        }
        
        $json = array(
            'html'        => $html,
            'limit'       => 200, // $limit,
            'title'       => $title,
            'btnInvite'   => JText::_('COM_COMMUNITY_SEND_INVITATIONS'),
            'btnLoadMore' => JText::_('COM_COMMUNITY_INVITE_LOAD_MORE')
        );

        die( json_encode($json) );
    }

    public function ajaxShowFriendsForm($friends, $callback, $cid, $displayFriends) {
        $displayFriends = (bool) $displayFriends;

        $config = CFactory::getConfig();
        $limit = $config->get('friendloadlimit', 8);

        $tmpl = new CTemplate();
        $tmpl->set('displayFriends', $displayFriends);
        $tmpl->set('cid', $cid);
        $tmpl->set('callback', $callback);
        $tmpl->set('limit', $limit);
        $html = $tmpl->fetch('ajax.showfriends');

        $json = array(
            'html'        => $html,
            'limit'       => $limit,
            'title'       => JText::_('COM_COMMUNITY_SELECT_FRIENDS_CAPTION'),
            'btnSelect'   => JText::_('COM_COMMUNITY_SELECT_FRIENDS'),
            'btnLoadMore' => JText::_('COM_COMMUNITY_INVITE_LOAD_MORE')
        );

        die( json_encode($json) );
    }

    public function ajaxLoadFriendsList($namePrefix, $callback, $cid, $limitstart = 0, $limit = 1000) {
        // pending filter

        $objResponse = new JAXResponse();
        $filter = JFilterInput::getInstance();
        $callback = $filter->clean($callback, 'string');
        $cid = $filter->clean($cid, 'int');
        $namePrefix = $filter->clean($namePrefix, 'string');
        $my = CFactory::getUser();
        //get the handler
        $handlerName = '';

        $callbackOptions = explode(',', $callback);

        if (isset($callbackOptions[0])) {
            $handlerName = $callbackOptions[0];
        }

        $friends = '';
        $args = array();

        /*** 27.07.2022 Craft make search all members(not only friends)
         *
        * $handler = CFactory::getModel($handlerName);
        * $handlerFunc = 'getInviteListByName';
        * $friends = $handler->$handlerFunc($namePrefix, $my->id, $cid, $limitstart, $limit);
        ***/

        $handler = CFactory::getModel('chat');
        $handlerFunc = 'getCraftusersId';

        $friends = $handler->$handlerFunc($namePrefix);

        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->load($cid, $callback);

        $tmpl = new CTemplate();
        $tmpl->set('friends', $friends);

        $tmpl->set('selected', $invitation->getInvitedUsers());


        $tmplName = 'ajax.friend.list.' . $handlerName;


        $html = $tmpl->fetch($tmplName);
        //calculate pending friend list
        $loadedFriend = $limitstart + count($friends);
        if ($handler->total > $loadedFriend) {
            //update limitstart
            $limitstart = $limitstart + count($friends);
            $moreCount = $handler->total - $loadedFriend;
            //load more option
            $loadMore = '<a onClick="joms.friends.loadMoreFriend(\'' . $callback . '\',\'' . $cid . '\',\'' . $limitstart . '\',\'' . $limit . '\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_INVITE_LOAD_MORE') . '(' . $moreCount . ') </a>';
        } else {
            //nothing to load
            $loadMore = '';
        }

        $json = array(
            'html' => $html,
            'loadMore' => $loadMore ? true : false,
            'moreCount' => isset( $moreCount ) ? $moreCount : 0
        );


        die( json_encode($json) );
    }

    public function ajaxLoadGroupEventMembers($namePrefix, $cid, $limitstart = 0, $limit = 200)
    {
        // pending filter
        $objResponse = new JAXResponse();
        $filter = JFilterInput::getInstance();
        $callback = 'events,inviteUsers';
        $cid = $filter->clean($cid, 'int');
        $namePrefix = $filter->clean($namePrefix, 'string');
        $my = CFactory::getUser();
        //get the handler
        $handlerName = '';

        //load the event
        $event = JTable::getInstance('Event','CTable');
        $event->load($cid);

        //check permission here

        //get all the members of the group
        $groupid = $event->contentid;
        $groupsModel = CFactory::getModel('groups');
        $guestIds= $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, 0, false, false, false); //get a list of attending users
        $userids = array();
        foreach ($guestIds as $uid) {
            $userids[] = $uid->id;
        }
        $members = $groupsModel->getMembers($groupid, 0, true, false, SHOW_GROUP_ADMIN, true);
        $memberList = array();
        foreach($members as $member){
            if($member->id == $my->id || in_array($member->id, $userids)){
                continue; //exclude myself and those who already attending
            }
            $memberList[] = $member->id;
        }

        //calculate pending group list
        $results = CUserHelper::filterUserByName($memberList, $namePrefix, $limitstart, $limit);
        $memberList = $results['users'];

        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->load($callback, $cid);

        $tmpl = new CTemplate();
        $tmpl   ->set('friends', $memberList)
                ->set('selected', $invitation->getInvitedUsers());
        $html = $tmpl->fetch('ajax.friend.list.events');

        $loadedFriend = $limitstart + count($memberList);
        if ($results['total'] > $loadedFriend) {
            //update limitstart
            $limitstart = $limitstart + count($memberList);
            $moreCount = $results['total'] - $loadedFriend;
            //load more option
            $loadMore = '<a onClick="joms.friends.loadMoreFriend(\'' . $callback . '\',\'' . $cid . '\',\'' . $limitstart . '\',\'' . $limit . '\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_INVITE_LOAD_MORE') . '(' . $moreCount . ') </a>';
        } else {
            //nothing to load
            $loadMore = '';
        }

        $json = array(
            'html' => $html,
            'loadMore' => $loadMore ? true : false,
            'moreCount' => isset( $moreCount ) ? $moreCount : 0
        );

        die( json_encode($json) );

    }

    public function ajaxLoadPageEventMembers($namePrefix, $cid, $limitstart = 0, $limit = 200)
    {
        // pending filter
        $objResponse = new JAXResponse();
        $filter = JFilterInput::getInstance();
        $callback = 'events,inviteUsers';
        $cid = $filter->clean($cid, 'int');
        $namePrefix = $filter->clean($namePrefix, 'string');
        $my = CFactory::getUser();
        //get the handler
        $handlerName = '';

        //load the event
        $event = JTable::getInstance('Event','CTable');
        $event->load($cid);

        //check permission here

        //get all the members of the page
        $pageid = $event->contentid;
        $pagesModel = CFactory::getModel('pages');
        $guestIds= $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, 0, false, false, false); //get a list of attending users
        $userids = array();
        foreach ($guestIds as $uid) {
            $userids[] = $uid->id;
        }
        $members = $pagesModel->getMembers($pageid, 0, true, false, SHOW_GROUP_ADMIN, true);
        $memberList = array();
        foreach($members as $member){
            if($member->id == $my->id || in_array($member->id, $userids)){
                continue; //exclude myself and those who already attending
            }
            $memberList[] = $member->id;
        }

        //calculate pending group list
        $results = CUserHelper::filterUserByName($memberList, $namePrefix, $limitstart, $limit);
        $memberList = $results['users'];

        $invitation = JTable::getInstance('Invitation', 'CTable');
        $invitation->load($callback, $cid);

        $tmpl = new CTemplate();
        $tmpl   ->set('friends', $memberList)
                ->set('selected', $invitation->getInvitedUsers());
        $html = $tmpl->fetch('ajax.friend.list.events');

        $loadedFriend = $limitstart + count($memberList);
        if ($results['total'] > $loadedFriend) {
            //update limitstart
            $limitstart = $limitstart + count($memberList);
            $moreCount = $results['total'] - $loadedFriend;
            //load more option
            $loadMore = '<a onClick="joms.friends.loadMoreFriend(\'' . $callback . '\',\'' . $cid . '\',\'' . $limitstart . '\',\'' . $limit . '\');" href="javascript:void(0)">' . JText::_('COM_COMMUNITY_INVITE_LOAD_MORE') . '(' . $moreCount . ') </a>';
        } else {
            //nothing to load
            $loadMore = '';
        }

        $json = array(
            'html' => $html,
            'loadMore' => $loadMore ? true : false,
            'moreCount' => isset( $moreCount ) ? $moreCount : 0
        );

        die( json_encode($json) );

    }

    public function ajaxSubmitInvitation($callback, $cid, $values) {
        //CFactory::load( 'helpers' , 'validate' );
        $filter = JFilterInput::getInstance();
        $callback = $filter->clean($callback, 'string');
        $cid = $filter->clean($cid, 'int');
        $values = $filter->clean($values, 'array');
        $objResponse = new JAXResponse();
        $my = CFactory::getUser();
        $methods = explode(',', $callback);
        $emails = array();
        $recipients = array();
        $users = '';
        $message = $values['message'];
        $values['friends'] = isset($values['friends']) ? $values['friends'] : array();

        if (!is_array($values['friends'])) {
            $values['friends'] = array($values['friends']);
        }

        // This is where we process external email addresses
        if (!empty($values['emails'])) {
            $emails = explode(',', $values['emails']);
            foreach ($emails as $email) {
                if (!CValidateHelper::email($email)) {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::sprintf('COM_COMMUNITY_INVITE_EMAIL_INVALID', $email));
                    return $objResponse->sendResponse();
                }
                $recipients[] = $email;
            }
        }

        // This is where we process site members that are being invited
        if (!empty($values['friends'][0])) {
            $users = explode(',', $values['friends'][0]);

            foreach($users as $id) {
                $recipients[] = $id;
            }
        }

        if (!empty($recipients)) {
            $arguments = array($cid, $recipients, $emails, $message);

            if (is_array($methods) && $methods[0] != 'plugins') {
                $controller = CStringHelper::strtolower(basename($methods[0]));
                $function = $methods[1];
                require_once( JPATH_ROOT . '/components/com_community/controllers/controller.php' );
                $file = JPATH_ROOT . '/components/com_community/controllers' . '/' . $controller . '.php';


                if (JFile::exists($file)) {
                    require_once( $file );

                    $controller = CStringHelper::ucfirst($controller);
                    $controller = 'Community' . $controller . 'Controller';
                    $controller = new $controller();

                    if (method_exists($controller, $function)) {
                        $inviteMail = call_user_func_array(array($controller, $function), $arguments);
                    } else {
                        $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                        return $objResponse->sendResponse();
                    }
                } else {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                    return $objResponse->sendResponse();
                }
            } else if (is_array($methods) && $methods[0] == 'plugins') {
                // Load 3rd party applications
                $element = CStringHelper::strtolower(basename($methods[1]));
                $function = $methods[2];
                $file = CPluginHelper::getPluginPath('community', $element) . '/' . $element . '.php';

                if (JFile::exists($file)) {
                    require_once( $file );
                    $className = 'plgCommunity' . CStringHelper::ucfirst($element);


                    if (method_exists($controller, $function)) {
                        $inviteMail = call_user_func_array(array($className, $function), $arguments);
                    } else {
                        $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                        return $objResponse->sendResponse();
                    }
                } else {
                    $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR'));
                    return $objResponse->sendResponse();
                }
            }

            //CFactory::load( 'libraries' , 'invitation' );
            // If the responsible method returns a false value, we should know that they want to stop the invitation process.

            if ($inviteMail instanceof CInvitationMail) {
                if ($inviteMail->hasError()) {
                    $objResponse->addAssign('invitation-error', 'innerHTML', $inviteMail->getError());

                    return $objResponse->sendResponse();
                } else {
                    // Once stored, we need to store selected user so they wont be invited again
                    $invitation = JTable::getInstance('Invitation', 'CTable');
                    $invitation->load($callback, $cid);

                    if (!empty($values['friends'])) {
                        if (!$invitation->id) {
                            // If the record doesn't exists, we need add them into the
                            $invitation->cid = $cid;
                            $invitation->callback = $callback;
                        }
                        $invitation->users = empty($invitation->users) ? implode(',', $values['friends']) : $invitation->users . ',' . implode(',', $values['friends']);
                        $invitation->store();
                    }

                    // Add notification
                    //CFactory::load( 'libraries' , 'notification' );
                    CNotificationLibrary::add($inviteMail->getCommand(), $my->id, $recipients, $inviteMail->getTitle(), $inviteMail->getContent(), '', $inviteMail->getParams());
                }
            } else {
                $objResponse->addScriptCall(JText::_('COM_COMMUNITY_INVITE_INVALID_RETURN_TYPE'));
                return $objResponse->sendResponse();
            }
        } else {
            $objResponse->addAssign('invitation-error', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_NO_SELECTION'));
            return $objResponse->sendResponse();
        }

        $actions = '<input type="button" class="btn" onclick="cWindowHide();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';
        $html = JText::_('COM_COMMUNITY_INVITE_SENT');

        $objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));
        $objResponse->addScriptCall('cWindowAddContent', $html, $actions);

        return $objResponse->sendResponse();
    }

    public function ajaxReport() {
        $config = CFactory::getConfig();
        $reports = CStringHelper::trim($config->get('predefinedreports'));
        $reports = empty($reports) ? false : explode('\n', $reports);
        $tmpArray = array();

        $my = CFactory::getUser();
        if ( !$config->get('enablereporting') || ( ( $my->id == 0 ) && (!$config->get('enableguestreporting') ) ) ) {
            $json = array(
                'title' => JText::_('COM_COMMUNITY_REPORT_THIS'),
                'error' => JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN')
            );

            die( json_encode($json) );
        }

        foreach ($reports as $_report) {
            $tmp = explode("\n", $_report);
            foreach ($tmp as $_tmp) {
                $tmpArray[] = $_tmp;
            }
        }
        $reports = $tmpArray;

        $html = '';

        $argsCount = func_num_args();

        $argsData = '';

        if ($argsCount > 1) {

            for ($i = 2; $i < $argsCount; $i++) {
                $argsData .= "\'" . func_get_arg($i) . "\'";
                $argsData .= ( $i != ( $argsCount - 1) ) ? ',' : '';
            }
        }

        $tmpl = new CTemplate();
        $tmpl->set('reports', $reports);

        $json = array(
            'html'      => $tmpl->fetch('ajax.reporting'),
            'title'     => JText::_('COM_COMMUNITY_REPORT_THIS'),
            'btnSend'   => JText::_('COM_COMMUNITY_SEND_BUTTON'),
            'btnCancel' => JText::_('COM_COMMUNITY_CANCEL_BUTTON')
        );

        die( json_encode($json) );
    }

    public function ajaxSendReport() {
        $reportFunc = func_get_arg(0);
        $pageLink = func_get_arg(1);
        $message = func_get_arg(2);

        $argsCount = func_num_args();
        $method = explode(',', $reportFunc);

        $args = array();
        $args[] = $pageLink;
        $args[] = $message;

        for ($i = 3; $i < $argsCount; $i++) {
            $args[] = func_get_arg($i);
        }
        
        // Reporting should be session sensitive
        // Construct $output
        if ($reportFunc == 'activities,reportActivities' && strpos($pageLink, 'actid') === false) {
            $pageLink = $pageLink . '&actid=' . func_get_arg(3);
        }
        
        $uniqueString = md5($reportFunc . $pageLink);
        $session = JFactory::getSession();


        if ($session->has('action-report-' . $uniqueString)) {
            $output = JText::_('COM_COMMUNITY_REPORT_ALREADY_SENT');
        } else {
            if (is_array($method) && $method[0] != 'plugins') {
                $controller = CStringHelper::strtolower(basename($method[0]));

                require_once( JPATH_ROOT . '/components/com_community/controllers/controller.php' );
                require_once( JPATH_ROOT . '/components/com_community/controllers' . '/' . $controller . '.php' );

                $controller = CStringHelper::ucfirst($controller);
                $controller = 'Community' . $controller . 'Controller';
                $controller = new $controller();


                $output = call_user_func_array(array(&$controller, $method[1]), $args);
            } else if (is_array($method) && $method[0] == 'plugins') {
                // Application method calls
                $element = CStringHelper::strtolower($method[1]);
                require_once( CPluginHelper::getPluginPath('community', $element) . '/' . $element . '.php' );
                $className = 'plgCommunity' . CStringHelper::ucfirst($element);
                $output = call_user_func_array(array($className, $method[2]), $args);
            }
        }
        $session->set('action-report-' . $uniqueString, true);

        $json = array( 'message' => $output );

        die( json_encode($json) );
    }

    public function ajaxEditWall($wallId, $editableFunc) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');
        $editableFunc = $filter->clean($editableFunc, 'string');

        $objResponse = new JAXResponse();
        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        //CFactory::load( 'libraries' , 'wall' );
        $isEditable = CWall::isEditable($editableFunc, $wall->id);

        if (!$isEditable) {
            $objResponse->addAlert(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT'));
            return $objResponse->sendResponse();
        }

        //CFactory::load( 'libraries' , 'comment' );
        $tmpl = new CTemplate();
        $message = CComment::stripCommentData($wall->comment);
        $tmpl->set('message', $message);
        $tmpl->set('editableFunc', $editableFunc);
        $tmpl->set('id', $wall->id);

        $content = $tmpl->fetch('wall/edit');

        $objResponse->addScriptCall('joms.jQuery("#wall-message-' . $wallId . '").hide();');
        $objResponse->addScriptCall('joms.jQuery("#wall-edit-container-' . $wallId . '").show();');
        $objResponse->addScriptCall('joms.jQuery("#wall-edit-container-' . $wallId . '").find("textarea").val("' . str_replace(array("\r\n", "\r", "\n"), '\n', $message) . '");');
        $objResponse->addScriptCall('joms.jQuery("#wall_' . $wallId . '").find("[data-action=edit]").trigger("start");');

        return $objResponse->sendResponse();
    }

    public function ajaxUpdateWall($wallId, $message, $editableFunc, $photoId = 0) {
        $my = CFactory::getUser();
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');
        $editableFunc = $filter->clean($editableFunc, 'string');

        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);
        $objResponse = new JAXresponse();
        $json = array();

        if (empty($message)) {
            $json['error'] = JText::_('COM_COMMUNITY_EMPTY_MESSAGE');
            die( json_encode($json) );
        }

        $isEditable = ($my->authorise('community.postcommentedit', 'com_community') || ($wall->post_by == $my->id)) ? true : false;

        if (!$isEditable) {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT');
            die( json_encode($json) );
        }
        
        // We don't want to touch the comments data.
        $comments = CComment::getRawCommentsData($wall->comment);
        $wall->comment = $message;
        $wall->comment .= $comments;
        
        $data = CWallLibrary::saveWall($wall->contentid, $wall->comment, $wall->type, $my, false, $editableFunc, 'wall/content', $wall->id, $photoId);

        // update activity item if any
        $params = new CParameter($wall->params);
        $actId = $params->get('activityid', null);
        $activity = JTable::getInstance('Activity', 'CTable');
        
        if ($activity->load($actId)) {
            $activity->content = $wall->comment;
            $activity->store();
        }

        $wall->originalComment = $wall->comment;

        $CComment = new CComment();
        $wall->comment = $CComment->stripCommentData($wall->comment);
        $wall->comment = CStringHelper::autoLink($wall->comment);
        $wall->comment = nl2br($wall->comment);
        $wall->comment = CUserHelper::replaceAliasURL($wall->comment);
        $wall->comment = CStringHelper::getEmoticon($wall->comment);
        $wall->comment = CStringHelper::converttagtolink($wall->comment); // convert to hashtag

        $json['success'] = true;
        $json['comment'] = $wall->comment;
        $json['originalComment'] = $wall->originalComment;

        die( json_encode($json) );
    }

    public function ajaxRemoveWallPreview($wallId) {
        $filter = JFilterInput::getInstance();
        $wallId = $filter->clean($wallId, 'int');

        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($wallId);

        //make sure this item id belongs to the current user
        $my = CFactory::getUser();
        if ($my->id == $wall->post_by || COwnerHelper::isCommunityAdmin()) {
            $wall->params = '';
            $wall->store();
        }

        $json = array( 'success' => true );
        die( json_encode( $json ) );
    }

    public function ajaxGetOlderWalls($groupId, $discussionId, $limitStart) {
        $filter = JFilterInput::getInstance();
        $groupId = $filter->clean($groupId, 'int');
        $discussionId = $filter->clean($discussionId, 'int');
        $limitStart = $filter->clean($limitStart, 'int');

        $limitStart = max(0, $limitStart);
        $response = new JAXResponse();

        $app = JFactory::getApplication();
        $my = CFactory::getUser();
        //$jconfig  = JFactory::getConfig();

        $groupModel = CFactory::getModel('groups');
        $isGroupAdmin = $groupModel->isAdmin($my->id, $groupId);

        $html = CWall::getWallContents('discussions', $discussionId, $isGroupAdmin, JFactory::getConfig()->get('list_limit'), $limitStart, 'wall/content', 'groups,discussion', $groupId);

        // parse the user avatar
        $html = CStringHelper::replaceThumbnails($html);
        $html = CString::str_ireplace(array('{error}', '{warning}', '{info}'), '', $html);


        $config = CFactory::getConfig();
        $order = $config->get('group_discuss_order');

        if ($order == 'ASC') {
            // Append new data at Top.
            $response->addScriptCall('joms.walls.prepend', $html);
        } else {
            // Append new data at bottom.
            $response->addScriptCall('joms.walls.append', $html);
        }

        return $response->sendResponse();
    }

    public function ajaxRemoveCommentPreview($itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');

        $wall = JTable::getInstance('Wall', 'CTable');
        $wall->load($itemId);

        //make sure this item id belongs to the current user
        $my = CFactory::getUser();
        if ($my->id == $wall->post_by || COwnerHelper::isCommunityAdmin()) {
            $wall->params = '';
            $wall->store();
        }

        $json = array( 'success' => true );
        die( json_encode( $json ) );
    }

    /**
     * Like an item. Update ajax count
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId     Unique id to identify object item
     *
     */
    public function ajaxLike($element, $itemId, $reactId = 1) {
        
        $filter = JFilterInput::getInstance();
        $element = $filter->clean($element, 'string');
        $itemId = $filter->clean($itemId, 'int');
        $reactId = (int) $reactId;

        if (!$itemId || !$reactId) {
            die('like error');
        }

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $like = new CLike();

        if (!$like->enabled($element)) {
            // @todo: return proper ajax error
            return;
        }
        
        $my = CFactory::getUser();
        $doLike = $like->addLike($element, $itemId, $reactId);

        if ($doLike) {
            $act = new stdClass();
            $act->cmd = $element . '.like';
            $act->actor = $my->id;
            $act->target = 0;
            $act->title = '';
            $act->content = '';
            $act->app = $element . '.like';
            $act->cid = $itemId;

            if($element == 'album'){
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($itemId);
                if($album->type == 'event'){
                    $act->eventid=$album->eventid;
                }
            }elseif($element == 'photo'){
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($itemId);
                $album = JTable::getInstance('Album', 'CTable');
                $album->load($photo->albumid);
                if($album->type == 'event'){
                    $act->eventid=$album->eventid;
                }
            }elseif($element == 'videos'){
                $video = JTable::getInstance('Video', 'CTable');
                $video->load($itemId);
                $act->eventid = $video->eventid;
                $act->groupid = $video->groupid;
            }

            // load item-specific privacy settings, if available
            $elementTable = $element=='videos'?'video':$element;
            $table = JTable::getInstance($elementTable, 'CTable');

            if(is_object($table)) {

                $table->load($itemId);

                if (isset($table->permissions)) {
                    $act->access = $table->permissions;
                }
            }

            $params = new CParameter('');

            switch ($element) {
                case 'pages':
                    $page = JTable::getInstance('Page', 'CTable');
                    $page->load($itemId);
                    $act->pageid = $itemId;
                    $act->page_access = $page->approvals;

                    $statsModel = CFactory::getModel('stats');
                    $statsModel->addPageStats($itemId, 'like');
                    
                    $pageModel = CFactory::getModel('pages');
                    $isMember = $pageModel->isMember($my->id, $page->id);
                    
                    if (!$isMember) {
                        $member = JTable::getInstance('PageMembers', 'CTable');
                        $member->pageid = $page->id;
                        $member->memberid = $my->id;
                        $member->approved = 1;
                        $member->permissions = 0;
                        $member->store();
                    }

                    break;
                case 'groups':
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($itemId);
                    $act->groupid = $itemId;
                    $act->group_access = $group->approvals;

                    //@since 4.1 when a group is liked, dump the data into photo stats
                    $statsModel = CFactory::getModel('stats');
                    $statsModel->addGroupStats($itemId, 'like');
                    break;
                case 'discussion':
                    $discussion = JTable::getInstance('Discussion', 'CTable');
                    $discussion->load($itemId);
                    $group = JTable::getInstance('Group', 'CTable');
                    $group->load($discussion->groupid);
                    $act->groupid = $discussion->groupid;
                    $act->group_access = $group->approvals;
                    break;
                case 'events':
                    $act->eventid = $itemId;
                    $eventTable = JTable::getInstance('Event', 'CTable');
                    $eventTable->load($act->eventid);
                    $act->event_access = $eventTable->permission;

                    //@since 4.1 when an event is liked, dump the data into event stats
                    $statsModel = CFactory::getModel('stats');
                    $statsModel->addEventStats($itemId, 'like');
                    break;
            }

            $params->set('action', $element . '.like');
            
            // Add logging
            CActivityStream::addActor($act, $params->toString());

            $likeCount = $like->getLikeCount($element, $itemId);

            $config = CFactory::getConfig();
            $enableReaction = $config->get('enablereaction');

            $json = array();
            $json['success'] = true;
            if ($enableReaction && ($element === 'photo' || $element === 'videos' || $element === 'album')) {
                $json['html'] = $like->showWhoReacts($element, $itemId);
            } else {
                $json['likeCount'] = $likeCount;
            }

        } else {
            $json = array('error' => 'like error');
        }

        die( json_encode($json) );
    }

    /**
     * Dislike an item
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId     Unique id to identify object item
     *
     */
    public function ajaxDislike($element, $itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $dislike = new CLike();

        if (!$dislike->enabled($element)) {
            // @todo: return proper ajax error
            return;
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();


        $dislike->addDislike($element, $itemId);
        $html = $dislike->getHTML($element, $itemId, $my->id);

        $objResponse->addScriptCall('__callback', $html);

        return $objResponse->sendResponse();
    }

    /**
     * Unlike an item
     * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
     * @param mixed $itemId     Unique id to identify object item
     *
     */
    public function ajaxUnlike($element, $itemId) {
        $filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        $my = CFactory::getUser();
        $objResponse = new JAXResponse();

        // Load libraries
        $unlike = new CLike();

        if (!$unlike->enabled($element)) {
            return '';
        }

        $doUnlike = $unlike->unlike($element, $itemId);

        if ($doUnlike) {
            $act = new stdClass();
            $act->cmd = $element . '.like';
            $act->actor = $my->id;
            $act->target = 0;
            $act->title = '';
            $act->content = '';
            $act->app = $element . '.like';
            $act->cid = $itemId;

            $params = new CParameter('');

            switch ($element) {
                case 'pages':
                    $act->pageid = $itemId;
                    
                    $page = JTable::getInstance('Page', 'CTable');
                    $page->load($itemId);
                    
                    if ($page->ownerid != $my->id) {
                        $pageModel = CFactory::getModel('pages');

                        $data = new stdClass();
                        $data->pageid = $page->id;
                        $data->memberid = $my->id;

                        $pageModel->removeMember($data);

                        //delete invitation
                        $invitation = JTable::getInstance('Invitation', 'CTable');
                        $invitation->deleteInvitation($page->id, $my->id, 'pages,inviteUsers');
                    }

                    break;
                case 'groups':
                    $act->groupid = $itemId;
                    break;
                case 'events':
                    $act->eventid = $itemId;
                    break;
            }

            $params->set('action', $element . '.like');

            // Remove logging
            CActivityStream::removeActor($act, $params->toString());

            $config = CFactory::getConfig();
            $enableReaction = $config->get('enablereaction');

            $likeCount = $unlike->getLikeCount($element, $itemId);

            $json = array();
            $json['success'] = true;
            if ($enableReaction && ($element === 'photo' || $element === 'videos' || $element === 'album')) {
                $json['html'] = $unlike->showWhoReacts($element, $itemId);
            } else {
                $json['likeCount'] = $likeCount;
            }
        } else {
            $json = array( 'error' => 'unlike error');
        }

        die( json_encode($json) );
    }

    /**
     * Called by status box to add new stream data
     *
     * @param type $message
     * @param type $attachment
     * @return type
     */
    public function ajaxStreamAdd($message, $attachments, $streamFilter = FALSE, $statusattachments = false, $postTo = false) {

        $attachment = $statusattachments ? array_merge(json_decode($attachments, true), json_decode($statusattachments, true)) : json_decode($attachment, true);

        if($postTo && json_decode($postTo, true)) {
            $postIn = json_decode($postTo, true);
            $attachment['element'] = json_decode($postTo, true)['element'];
            $attachment['target'] = $postIn['id'] ? : $attachment['target'];
        }

        $streamHTML = '';

        // $attachment pending filter
        $cache = CFactory::getFastCache();
        $cache->clean(array('activities'));

        $my = CFactory::getUser();
        $userparams = $my->getParams();

        if (!COwnerHelper::isRegisteredUser()) {
            return $this->ajaxBlockUnregister();
        }

        //@rule: In case someone bypasses the status in the html, we enforce the character limit.
        $config = CFactory::getConfig();
        
        if ($attachment['type'] == 'message' && !empty($attachment['bgid'])) {
            if (CStringHelper::strlen($message) > 160) {
                $message = JHTML::_('string.truncate', $message, 160);
            }
        } else {
            if (CStringHelper::strlen($message) > $config->get('statusmaxchar')) {
                $message = JHTML::_('string.truncate', $message, $config->get('statusmaxchar'));
            }
        }

        $message = CStringHelper::trim($message);
        $objResponse = new JAXResponse();
        $rawMessage = $message;

        // @rule: Autolink hyperlinks
        // @rule: Autolink to users profile when message contains @username
        // $message     = CUserHelper::replaceAliasURL($message); // the processing is done on display side
        $emailMessage = CUserHelper::replaceAliasURL($rawMessage, true);

        // @rule: Spam checks
        if ($config->get('antispam_akismet_status')) {
            $filter = CSpamFilter::getFilter();
            $filter->setAuthor($my->getDisplayName());
            $filter->setMessage($message);
            $filter->setEmail($my->email);
            $filter->setURL(CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
            $filter->setType('message');
            $filter->setIP(CFactory::getClientIP());

            if ($filter->isSpam()) {
                $objResponse->addAlert(JText::_('COM_COMMUNITY_STATUS_MARKED_SPAM'));
                return $objResponse->sendResponse();
            }
        }
        
        switch ($attachment['type']) {
            case 'message':
                //if (!empty($message)) {
                switch ($attachment['element']) {

                    case 'profile':
                        //only update user status if share messgage is on his profile
                        if (COwnerHelper::isMine($my->id, $attachment['target'])) {

                            //save the message
                            $status = $this->getModel('status');
                            /* If no privacy in attachment than we apply default: Public */
                            if (empty($attachment['privacy']))
                                $attachment['privacy'] = COMMUNITY_STATUS_PRIVACY_PUBLIC;
                            $status->update($my->id, $rawMessage, $attachment['privacy']);

                            //set user status for current session.
                            $today = JDate::getInstance();
                            $message2 = (empty($message)) ? ' ' : $message;
                            $my->set('_status', $rawMessage);
                            $my->set('_posted_on', $today->toSql());

                            // Order of replacement
                            $order = array("\r\n", "\n", "\r");
                            $replace = '<br />';

                            // Processes \r\n's first so they aren't converted twice.
                            $messageDisplay = str_replace($order, $replace, $message);
                            $messageDisplay = CKses::kses($messageDisplay, CKses::allowed());

                            //update user status
                            $objResponse->addScriptCall("joms.jQuery('#profile-status span#profile-status-message').html('" . addslashes($messageDisplay) . "');");
                        }

                        //if actor posted something to target, the privacy should be under target's profile privacy settings
                        if (!COwnerHelper::isMine($my->id, $attachment['target']) && $attachment['target'] != '') {
                            $attachment['privacy'] = CFactory::getUser($attachment['target'])->getParams()->get('privacyProfileView');
                        }
                        
                        //push to activity stream
                        $act = new stdClass();
                        $act->cmd = 'profile.status.update';
                        $act->actor = $my->id;
                        $act->target = $attachment['target'];
                        $act->title = $message;
                        $act->content = '';
                        $act->app = $attachment['element'];
                        $act->cid = $my->id;
                        $act->access = $attachment['privacy'];
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'profile.status';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'profile.status';
                        $act->archived = 0;
                        $act->location = '';
                        $act->actors = '';

                        $activityParams = new CParameter('');

                        $activityParams->set('files', $attachment['id']);

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $headMeta = new CParameter('');

                        if (!empty($attachment['fetch'])) {
                            $headMeta->set('title', $attachment['fetch'][2]);
                            $headMeta->set('description', $attachment['fetch'][3]);
                            $headMeta->set('image', $attachment['fetch'][1]);
                            $headMeta->set('link', $attachment['fetch'][0]);

                            //do checking if this is a video link
                            $video = JTable::getInstance('Video', 'CTable');
                            $isValidVideo = @$video->init($attachment['fetch'][0]);
                            if ($isValidVideo) {
                                $headMeta->set('type', 'video');
                                $headMeta->set('video_provider', $video->type);
                                $headMeta->set('video_id', $video->getVideoId());
                                $headMeta->set('height', $video->getHeight());
                                $headMeta->set('width', $video->getWidth());
                            }

                            $activityParams->set('headMetas', $headMeta->toString());
                        }
                        
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        //Store status background in paramm
                        if (!empty($attachment['colorful']) && $attachment['colorful'] == true) {
                            $activityParams->set('bgid', $attachment['bgid']);
                        }

                        $act->params = $activityParams->toString();


                        //CActivityStream::add($act);
                        //check if the user points is enabled
                        if(CUserPoints::assignPoint('profile.status.update')){
                            /* Let use our new CApiStream */
                            $activityData = CApiActivities::add($act);
                            CTags::add($activityData);

                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);
                            $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                            $params->set('stream_url',CRoute::_('index.php?option=com_community&view=profile&userid='.$activityData->actor.'&actid='.$activityData->id));

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::sprintf('COM_COMMUNITY_FRIEND_WALL_POST', $my->getDisplayName()), '', 'wall.post', $params);

                            //email and add notification if user are tagged
                            CUserHelper::parseTaggedUserNotification($message, $my, $activityData, array('type' => 'post-comment'));
                        }

                        if(!empty($attachment['id'])) {
                            foreach ($attachment['id'] as $fileid) {
                                $fileTable = JTable::getInstance('File', 'CTable');
                                $fileTable->load($fileid);
                                $fileTable->actid = $activityData->id;
                                $fileTable->store();
                            }
                        }

                        break;
                    // Message posted from Page page
                    case 'pages':
                        //
                        $pageLib = new CPages();
                        $page = JTable::getInstance('Page', 'CTable');
                        $page->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if (!COwnerHelper::isCommunityAdmin() && !$page->isMember($my->id) && $config->get('lockpagewalls')) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        $act = new stdClass();
                        $act->cmd = 'pages.wall';
                        $act->actor = $my->id;
                        $act->target = 0;

                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'pages.wall';
                        $act->cid = $attachment['target'];
                        $act->pageid = $page->id;
                        $act->page_access = $page->approvals;
                        $act->groupid = 0;
                        $act->group_access = 0;
                        $act->eventid = 0;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'pages.wall';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'pages.wall';

                        $activityParams = new CParameter('');

                        $activityParams->set('files', $attachment['id']);

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $headMeta = new CParameter('');

                        if (!empty($attachment['fetch'])) {
                            $headMeta->set('title', $attachment['fetch'][2]);
                            $headMeta->set('description', $attachment['fetch'][3]);
                            $headMeta->set('image', $attachment['fetch'][1]);
                            $headMeta->set('link', $attachment['fetch'][0]);

                            //do checking if this is a video link
                            $video = JTable::getInstance('Video', 'CTable');
                            $isValidVideo = @$video->init($attachment['fetch'][0]);
                            if ($isValidVideo) {
                                $headMeta->set('type', 'video');
                                $headMeta->set('video_provider', $video->type);
                                $headMeta->set('video_id', $video->getVideoId());
                                $headMeta->set('height', $video->getHeight());
                                $headMeta->set('width', $video->getWidth());
                            }

                            $activityParams->set('headMetas', $headMeta->toString());
                        }

                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        //Store status background in paramm
                        if (!empty($attachment['colorful']) && $attachment['colorful'] == true) {
                            $activityParams->set('bgid', $attachment['bgid']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);

                        CTags::add($activityData);
                        CUserPoints::assignPoint('page.wall.create');

                        $recipient = CFactory::getUser($attachment['target']);
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('page', $page->name);
                        $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id, false));

                        //Get page member emails
                        $model = CFactory::getModel('Pages');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $pageParams = new CParameter($page->params);

                        if($pageParams->get('wallnotification')) {
                            CNotificationLibrary::add('pages_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $page->name), '', 'pages.post', $params);
                        }

                        //@since 4.1 when a there is a new post in page, dump the data into page stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addPageStats($page->id, 'post');
                        
                        $page->updateStats();
                        $page->store();

                        // Add custom stream
                        // Reload the stream with new stream data
                        $streamHTML = $pageLib->getStreamHTML($page, array('showLatestActivityOnTop'=>true));


                        if(!empty($attachment['id'])) {
                            foreach ($attachment['id'] as $fileid) {
                                $fileTable = JTable::getInstance('File', 'CTable');
                                $fileTable->load($fileid);
                                $fileTable->actid = $activityData->id;
                                $fileTable->store();
                            }
                        }
                        
                        break;

                    // Message posted from Group page
                    case 'groups':
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if (!COwnerHelper::isCommunityAdmin() && !$group->isMember($my->id) && $config->get('lockgroupwalls')) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        $act = new stdClass();
                        $act->cmd = 'groups.wall';
                        $act->actor = $my->id;
                        $act->target = 0;

                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'groups.wall';
                        $act->cid = $attachment['target'];
                        $act->groupid = $group->id;
                        $act->group_access = $group->approvals;
                        $act->eventid = 0;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'groups.wall';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'groups.wall';

                        $activityParams = new CParameter('');

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $headMeta = new CParameter('');

                        if (!empty($attachment['fetch'])) {
                            $headMeta->set('title', $attachment['fetch'][2]);
                            $headMeta->set('description', $attachment['fetch'][3]);
                            $headMeta->set('image', $attachment['fetch'][1]);
                            $headMeta->set('link', $attachment['fetch'][0]);

                            //do checking if this is a video link
                            $video = JTable::getInstance('Video', 'CTable');
                            $isValidVideo = @$video->init($attachment['fetch'][0]);
                            if ($isValidVideo) {
                                $headMeta->set('type', 'video');
                                $headMeta->set('video_provider', $video->type);
                                $headMeta->set('video_id', $video->getVideoId());
                                $headMeta->set('height', $video->getHeight());
                                $headMeta->set('width', $video->getWidth());
                            }

                            $activityParams->set('headMetas', $headMeta->toString());
                        }

                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        //Store status background in paramm
                        if (!empty($attachment['colorful']) && $attachment['colorful'] == true) {
                            $activityParams->set('bgid', $attachment['bgid']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);

                        CTags::add($activityData);
                        CUserPoints::assignPoint('group.wall.create');

                        $recipient = CFactory::getUser($attachment['target']);
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));

                        //Get group member emails
                        $model = CFactory::getModel('Groups');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $groupParams = new CParameter($group->params);

                        if($groupParams->get('wallnotification')) {
                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);
                        }

                        //@since 4.1 when a there is a new post in group, dump the data into group stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addGroupStats($group->id, 'post');

                        // Add custom stream
                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        break;

                    // Message posted from Event page
                    case 'events' :

                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if ((!COwnerHelper::isCommunityAdmin() && !$event->isMember($my->id) && $config->get('lockeventwalls'))) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        // If this is a group event, set the group object
                        $groupid = ($event->type == 'group') ? $event->contentid : 0;
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($groupid);

                        $act = new stdClass();
                        $act->cmd = 'events.wall';
                        $act->actor = $my->id;
                        $act->target = 0;
                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'events.wall';
                        $act->cid = $attachment['target'];
                        $act->groupid = ($event->type == 'group') ? $event->contentid : 0;
                        $act->group_access = $group->approvals;
                        $act->eventid = $event->id;
                        $act->event_access = $event->permission;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'events.wall';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'events.wall';

                        $activityParams = new CParameter('');

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $headMeta = new CParameter('');

                        if (!empty($attachment['fetch'])) {
                            $headMeta->set('title', $attachment['fetch'][2]);
                            $headMeta->set('description', $attachment['fetch'][3]);
                            $headMeta->set('image', $attachment['fetch'][1]);
                            $headMeta->set('link', $attachment['fetch'][0]);

                            //do checking if this is a video link
                            $video = JTable::getInstance('Video', 'CTable');
                            $isValidVideo = @$video->init($attachment['fetch'][0]);
                            if ($isValidVideo) {
                                $headMeta->set('type', 'video');
                                $headMeta->set('video_provider', $video->type);
                                $headMeta->set('video_id', $video->getVideoId());
                                $headMeta->set('height', $video->getHeight());
                                $headMeta->set('width', $video->getWidth());
                            }

                            $activityParams->set('headMetas', $headMeta->toString());
                        }

                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        //Store status background in paramm
                        if (!empty($attachment['colorful']) && $attachment['colorful'] == true) {
                            $activityParams->set('bgid', $attachment['bgid']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);
                        CTags::add($activityData);

                        // add points
                        CUserPoints::assignPoint('event.wall.create');

                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('event', $event->title);
                        $params->set('event_url', 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id, false));

                        //Get event member emails
                        $members = $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, 12, CC_RANDOMIZE);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }

                        CNotificationLibrary::add('events_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT_EVENTS', $my->getDisplayName(), $event->title), '', 'events.post', $params);

                        //@since 4.1 when a there is a new post in event, dump the data into event stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addEventStats($event->id, 'post');

                        // Reload the stream with new stream data
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));
                        break;
                }

                $objResponse->addScriptCall('__callback', '');
                // /}

                break;

            case 'photo':

                if (!isset($attachment['id'][0]) || $attachment['id'][0] <= 0) {
                    //$objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));
                    exit;
                }

                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($attachment['id'][0]);
                $photoParams = new CParameter($photo->params);

                //before anything else, lets check if this photo is a gif type, if it is, we will automatically assign the album id to it
                if($photoParams->get('animated_gif') != ''){
                    $attachment['album_id'] = $photo->albumid;
                }

                switch ($attachment['element']) {

                    case 'profile':
                        $photoIds = $attachment['id'];
                        //use User Preference for Privacy
                        //$privacy = $userparams->get('privacyPhotoView'); //$privacy = $attachment['privacy'];

                        $photo = JTable::getInstance('Photo', 'CTable');

                        //always get album id from the photo itself, do not let it assign by params from user post data
                        $photoModel = CFactory::getModel('photos');
                        $photo = $photoModel->getPhoto($photoIds[0]);
                        /* OK ! If album_id is not provided than we use album id from photo ( it should be default album id ) */
                        $albumid = (!empty($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;

                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        $privacy = $album->permissions;

                        $params = array();
                        foreach ($photoIds as $key => $photoId) {
                            if (CLimitsLibrary::exceedDaily('photos')) {
                                unset($photoIds[$key]);
                                continue;
                            }
                            $photo->load($photoId);
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->caption = $rawMessage;
                            $photo->albumid = $albumid; /* We must update this photo into correct album id */
                            $photo->store();
                            $params[] = clone($photo);
                        }

                        if ($config->get('autoalbumcover') && !$album->photoid) {
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }

                        // Break if no photo added, which is likely because of daily limit.
                        if ( count($photoIds) < 1 ) {
                            $objResponse->addScriptCall( '__throwError', JText::_('COM_COMMUNITY_PHOTO_UPLOAD_LIMIT_EXCEEDED') );
                            return $objResponse->sendResponse();
                        }

                        // Trigger onPhotoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $apps->triggerEvent('onPhotoCreate', array($params));

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = $privacy; //$attachment['privacy'];
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message;
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $albumid;
                        $act->location = $album->location;

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1');
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE);
                        $params->set('photosId', implode(',', $photoIds));
                        $params->set('albumType',$album->type);
                        
                        if (is_array($photoIds) && count($photoIds) > 1) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }

                        //Store mood in param
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        // Add activity logging
                        // CActivityStream::remove($act->app, $act->cid);
                        $activityData = CActivityStream::add($act, $params->toString());

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        //add a notification to the target user if someone posted photos on target's profile
                        if($my->id != $attachment['target']){
                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);
                            $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                            $params->set('stream_url',CRoute::_('index.php?option=com_community&view=profile&userid='.$activityData->actor.'&actid='.$activityData->id));

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::sprintf('COM_COMMUNITY_NOTIFICATION_STREAM_PHOTO_POST', count($photoIds)), '', 'wall.post', $params);
                        }

                        //email and add notification if user are tagged
                        CUserHelper::parseTaggedUserNotification($message, $my, $activityData, array('type' => 'post-comment'));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));
                        break;
                    case 'events':
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);

                        $groupPrivacy = 0;
                        $eventPrivacy = 0;
                        $privacy = 0;
                        $groupId = 0;
                        //if this is a group event, we need to follow the group privacy
                        if($event->type == 'group' && $event->contentid){
                            $group = JTable::getInstance('Group', 'CTable');
                            $group->load($event->contentid);
                            $groupPrivacy = $privacy = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;
                            $groupId = $group->id;
                        }else{
                            $eventPrivacy = $privacy = $event->permission;
                        }

                        $photoIds = $attachment['id'];
                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($photoIds[0]);

                        $albumid = (!empty($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;
                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        if ($config->get('autoalbumcover') && !$album->photoid) {
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }

                        $params = array();
                        foreach ($photoIds as $photoId) {
                            $photo->load($photoId);

                            $photo->caption = $message;
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->albumid = $albumid;
                            $photo->store();
                            $params[] = clone($photo);
                        }

                        // Trigger onPhotoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $apps->triggerEvent('onPhotoCreate', array($params));

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = 0; //always 0 because this is determined by event_access
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_UPLOAD_PHOTO' , '{photo_url}', $album->name );
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $album->id;
                        $act->location = $album->location;
                        $act->groupid = $groupId;

                        $act->eventid = $event->id;
                        $act->group_access = $groupPrivacy; // just in case this event belongs to a group
                        $act->event_access = $eventPrivacy;
                        //$act->access      = $attachment['privacy'];

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1'); // this photo uploaded from status stream
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        $params->set('photosId', implode(',', $photoIds));
                        $params->set('albumType',$album->type);

                        // Add activity logging
                        if (count($photoIds) > 1) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }
                        // CActivityStream::remove($act->app, $act->cid);
                        $activityData = CActivityStream::add($act, $params->toString());

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        // Reload the stream with new stream data
                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));

                        break;
                    case 'groups':
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        $photoIds = $attachment['id'];
                        $privacy = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;

                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($photoIds[0]);

                        $albumid = (!empty($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;

                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        if ($config->get('autoalbumcover') && !$album->photoid) {
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }

                        $params = array();
                        foreach ($photoIds as $photoId) {
                            $photo->load($photoId);

                            $photo->caption = $message;
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->caption = $rawMessage;
                            $photo->albumid = $albumid;
                            $photo->store();
                            $params[] = clone($photo);
                        }
                        // Trigger onPhotoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $apps->triggerEvent('onPhotoCreate', array($params));

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = $privacy;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_UPLOAD_PHOTO' , '{photo_url}', $album->name );
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $album->id;
                        $act->location = $album->location;

                        $act->groupid = $group->id;
                        $act->group_access = $group->approvals;
                        $act->eventid = 0;
                        //$act->access      = $attachment['privacy'];

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1'); // this photo uploaded from status stream
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        $params->set('photosId', implode(',', $photoIds));
                        $params->set('albumType',$album->type);
                        // Add activity logging
                        if (count($photoIds) > 1) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }
                        // CActivityStream::remove($act->app, $act->cid);
                        $activityData = CActivityStream::add($act, $params->toString());

                        //add notifcation to all the members
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));
                        //Get group member emails
                        $model = CFactory::getModel('Groups');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $groupParams = new CParameter($group->params);

                        if($groupParams->get('wallnotification')) {
                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);
                        }

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));

                        break;

                    case 'pages':
                        //
                        $pageLib = new CPages();
                        $page = JTable::getInstance('Page', 'CTable');
                        $page->load($attachment['target']);

                        $photoIds = $attachment['id'];
                        $privacy = $page->approvals ? PRIVACY_PAGE_PRIVATE_ITEM : 0;

                        $photo = JTable::getInstance('Photo', 'CTable');
                        $photo->load($photoIds[0]);

                        $albumid = (!empty($attachment['album_id'])) ? $attachment['album_id'] : $photo->albumid;

                        $album = JTable::getInstance('Album', 'CTable');
                        $album->load($albumid);

                        if ($config->get('autoalbumcover') && !$album->photoid) {
                            $album->photoid = $photoIds[0];
                            $album->store();
                        }

                        $params = array();
                        foreach ($photoIds as $photoId) {
                            $photo->load($photoId);

                            $photo->caption = $message;
                            $photo->permissions = $privacy;
                            $photo->published = 1;
                            $photo->status = 'ready';
                            $photo->caption = $rawMessage;
                            $photo->albumid = $albumid;
                            $photo->store();
                            $params[] = clone($photo);
                        }
                        // Trigger onPhotoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $apps->triggerEvent('onPhotoCreate', array($params));

                        $act = new stdClass();
                        $act->cmd = 'photo.upload';
                        $act->actor = $my->id;
                        $act->access = $privacy;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->title = $message; //JText::sprintf('COM_COMMUNITY_ACTIVITIES_UPLOAD_PHOTO' , '{photo_url}', $album->name );
                        $act->content = ''; // Generated automatically by stream. No need to add anything
                        $act->app = 'photos';
                        $act->cid = $album->id;
                        $act->location = $album->location;

                        $act->groupid = 0;
                        $act->group_access = 0;
                        $act->pageid = $page->id;
                        $act->page_access = $page->approvals;
                        $act->eventid = 0;
                        //$act->access      = $attachment['privacy'];

                        /* Comment and like for individual photo upload is linked
                         * to the photos itsel
                         */
                        $act->comment_id = $photo->id;
                        $act->comment_type = 'photos';
                        $act->like_id = $photo->id;
                        $act->like_type = 'photo';

                        $albumUrl = 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&userid=' . $my->id;
                        $albumUrl = CRoute::_($albumUrl);

                        $photoUrl = 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id . '&userid=' . $photo->creator . '&photoid=' . $photo->id;
                        $photoUrl = CRoute::_($photoUrl);

                        $params = new CParameter('');
                        $params->set('multiUrl', $albumUrl);
                        $params->set('photoid', $photo->id);
                        $params->set('action', 'upload');
                        $params->set('stream', '1'); // this photo uploaded from status stream
                        $params->set('photo_url', $photoUrl);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        $params->set('photosId', implode(',', $photoIds));
                        $params->set('albumType',$album->type);
                        // Add activity logging
                        if (count($photoIds) > 1) {
                            $params->set('count', count($photoIds));
                            $params->set('batchcount', count($photoIds));
                        }
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }
                        // CActivityStream::remove($act->app, $act->cid);
                        $activityData = CActivityStream::add($act, $params->toString());

                        //add notifcation to all the members
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('page', $page->name);
                        $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id, false));
                        //Get page member emails
                        $model = CFactory::getModel('Pages');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $pageParams = new CParameter($page->params);

                        if($pageParams->get('wallnotification')) {
                            CNotificationLibrary::add('pages_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $page->name), '', 'pages.post', $params);
                        }

                        // Add user points
                        CUserPoints::assignPoint('photo.upload');

                        // Reload the stream with new stream data
                        $streamHTML = $pageLib->getStreamHTML($page, array('showLatestActivityOnTop'=>true));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));

                        break;
                        dafault:
                        return;
                }

                break;

            case 'video':
                switch ($attachment['element']) {
                    case 'profile':
                        // attachment id
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];
                        
                        //if actor posted something to target, the privacy should be under target's profile privacy settings
                        if (!COwnerHelper::isMine($my->id, $attachment['target']) && $attachment['target'] != '') {
                            $attachment['privacy'] = CFactory::getUser($attachment['target'])->getParams()->get('privacyProfileView');
                        }
                        
                        $privacy = !empty($attachment['privacy']) ? $attachment['privacy'] : COMMUNITY_STATUS_PRIVACY_PUBLIC;

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('creator_type', VIDEO_USER_TYPE);
                        $video->set('status', 'ready');
                        $video->set('permissions', $privacy);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);
                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            $video->set('location', $attachment['location'][0]);
                            $video->set('latitude', $attachment['location'][1]);
                            $video->set('longitude', $attachment['location'][2]);
                        };

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.linking';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = $privacy;

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos.linking';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos.linking';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos.linking';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        //
                        $activityData = CActivityStream::add($act, $params->toString());

                        if($my->id != $attachment['target']){
                            $params = new CParameter();
                            $params->set('activity_id', $activityData->id); // activity id is used to remove the activity if someone deleted this video
                            $params->set('target_id', $attachment['target']);
                            $video->params = $params->toString();

                            //also send a notification to the user
                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);
                            $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                            $params->set('stream_url',CRoute::_('index.php?option=com_community&view=profile&userid='.$activityData->actor.'&actid='.$activityData->id));

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::_('COM_COMMUNITY_NOTIFICATION_STREAM_VIDEO_POST'), '', 'wall.post', $params);
                        }

                        $video->store();

                        // @rule: Add point when user adds a new video link
                        //
                        CUserPoints::assignPoint('video.add', $video->creator);

                        //email and add notification if user are tagged
                        CUserHelper::parseTaggedUserNotification($message, $my, $activityData, array('type' => 'post-comment'));

                        // Trigger for onVideoCreate
                        //
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        break;

                    case 'groups':
                        // attachment id
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];
                        $privacy = 0; //$attachment['privacy'];

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('status', 'ready');
                        $video->set('groupid', $attachment['target']);
                        $video->set('permissions', $privacy);
                        $video->set('creator_type', VIDEO_GROUP_TYPE);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            $video->set('location', $attachment['location'][0]);
                            $video->set('latitude', $attachment['location'][1]);
                            $video->set('longitude', $attachment['location'][2]);
                        };

                        $video->store();

                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.linking';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = $privacy;

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->groupid = $video->groupid;
                        $act->group_access = $group->approvals;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        $activityData = CActivityStream::add($act, $params->toString());

                        // @rule: Add point when user adds a new video link
                        CUserPoints::assignPoint('video.add', $video->creator);

                        //add notifcation to all the members
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));
                        //Get group member emails
                        $model = CFactory::getModel('Groups');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $groupParams = new CParameter($group->params);

                        if($groupParams->get('wallnotification')) {
                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);
                        }

                        // Trigger for onVideoCreate
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        break;
                    case 'pages':
                        // attachment id
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];
                        $privacy = 0; //$attachment['privacy'];

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('status', 'ready');
                        $video->set('pageid', $attachment['target']);
                        $video->set('permissions', $privacy);
                        $video->set('creator_type', VIDEO_PAGE_TYPE);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            $video->set('location', $attachment['location'][0]);
                            $video->set('latitude', $attachment['location'][1]);
                            $video->set('longitude', $attachment['location'][2]);
                        };

                        $video->store();

                        //
                        $pageLib = new CPages();
                        $page = JTable::getInstance('Page', 'CTable');
                        $page->load($attachment['target']);

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.linking';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = $privacy;

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->pageid = $video->pageid;
                        $act->page_access = $page->approvals;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        $activityData = CActivityStream::add($act, $params->toString());

                        // @rule: Add point when user adds a new video link
                        CUserPoints::assignPoint('video.add', $video->creator);

                        //add notifcation to all the members
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('page', $page->name);
                        $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id, false));
                        //Get page member emails
                        $model = CFactory::getModel('Pages');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $pageParams = new CParameter($page->params);

                        if($pageParams->get('wallnotification')) {
                            CNotificationLibrary::add('pages_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $page->name), '', 'pages.post', $params);
                        }

                        // Trigger for onVideoCreate
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        // Reload the stream with new stream data
                        $streamHTML = $pageLib->getStreamHTML($page, array('showLatestActivityOnTop'=>true));

                        break;
                    case 'events':
                        //event videos
                        $fetch = $attachment['fetch'];
                        $cid = $fetch[0];

                        $privacy = 0;
                        $groupId = 0;
                        $groupPrivacy = 0;
                        $eventPrivacy = 0;

                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);
                        //if this is a group event, we need to follow the group privacy
                        if($event->type == 'group' && $event->contentid){
                            $group = JTable::getInstance('Group', 'CTable');
                            $group->load($event->contentid);
                            $groupPrivacy = $privacy = $group->approvals ? PRIVACY_GROUP_PRIVATE_ITEM : 0;
                        }else{
                            $eventPrivacy = $privacy = $event->permission;
                        }

                        $video = JTable::getInstance('Video', 'CTable');
                        $video->load($cid);
                        $video->set('status', 'ready');
                        $video->set('eventid', $attachment['target']);
                        $video->set('permissions', $privacy);
                        $video->set('creator_type', VIDEO_EVENT_TYPE);
                        $video->set('title', $fetch[3]);
                        $video->set('description', $fetch[4]);
                        $video->set('category_id', $fetch[5]);

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            $video->set('location', $attachment['location'][0]);
                            $video->set('latitude', $attachment['location'][1]);
                            $video->set('longitude', $attachment['location'][2]);
                        };

                        $video->store();

                        // Add activity logging
                        $url = $video->getViewUri(false);

                        $act = new stdClass();
                        $act->cmd = 'videos.linking';
                        $act->actor = $my->id;
                        $act->target = ($attachment['target'] == $my->id) ? 0 : $attachment['target'];
                        $act->access = 0; //always 0 because this is determined by event_access

                        //filter empty message
                        $act->title = $message;
                        $act->app = 'videos';
                        $act->content = '';
                        $act->cid = $video->id;
                        $act->groupid = 0;
                        $act->group_access = $groupPrivacy; // if this is a group event
                        $act->event_access = $eventPrivacy;
                        $act->location = $video->location;

                        /* Save cords if exists */
                        if (!empty($attachment['location'])) {
                            /* Save geo name */
                            $act->location = $attachment['location'][0];
                            $act->latitude = $attachment['location'][1];
                            $act->longitude = $attachment['location'][2];
                        };

                        $act->eventid = $event->id;

                        $act->comment_id = $video->id;
                        $act->comment_type = 'videos';

                        $act->like_id = $video->id;
                        $act->like_type = 'videos';

                        $params = new CParameter('');
                        $params->set('video_url', $url);
                        $params->set('style', COMMUNITY_STREAM_STYLE); // set stream style
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $params->set('mood', $attachment['mood']);
                        }

                        $activityData = CActivityStream::add($act, $params->toString());

                        // @rule: Add point when user adds a new video link
                        CUserPoints::assignPoint('video.add', $video->creator);

                        // Trigger for onVideoCreate
                        $apps = CAppPlugins::getInstance();
                        $apps->loadApplications();
                        $params = array();
                        $params[] = $video;
                        $apps->triggerEvent('onVideoCreate', $params);

                        $this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS, COMMUNITY_CACHE_TAG_FRONTPAGE, COMMUNITY_CACHE_TAG_FEATURED, COMMUNITY_CACHE_TAG_VIDEOS_CAT, COMMUNITY_CACHE_TAG_ACTIVITIES));

                        $objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

                        // Reload the stream with new stream data
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));
                        break;
                    default:
                        return;
                }

                break;

            case 'event':
                switch ($attachment['element']) {

                    case 'profile':
                        require_once(COMMUNITY_COM_PATH . '/controllers/events.php');

                        $eventController = new CommunityEventsController();

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $event = $eventController->ajaxCreate($attachment, $objResponse);

                        $objResponse->addScriptCall('window.location="' . $event->getLink() . '";');

                        if (CFactory::getConfig()->get('event_moderation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_EVENTS_MODERATION_NOTICE', $event->title));
                        }

                        break;

                    case 'groups':
                        require_once(COMMUNITY_COM_PATH . '/controllers/events.php');

                        $eventController = new CommunityEventsController();

                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $event = $eventController->ajaxCreate($attachment, $objResponse);

                        CEvents::addGroupNotification($event);

                        $objResponse->addScriptCall('window.location="' . $event->getLink() . '";');

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                        if (CFactory::getConfig()->get('event_moderation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_EVENTS_MODERATION_NOTICE', $event->title));
                        }

                        break;

                    case 'pages':
                        require_once(COMMUNITY_COM_PATH . '/controllers/events.php');

                        $eventController = new CommunityEventsController();

                        $pageLib = new CPages();
                        $page = JTable::getInstance('Page', 'CTable');
                        $page->load($attachment['target']);

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $event = $eventController->ajaxCreate($attachment, $objResponse);

                        CEvents::addPageNotification($event);

                        $objResponse->addScriptCall('window.location="' . $event->getLink() . '";');

                        // Reload the stream with new stream data
                        $streamHTML = $pageLib->getStreamHTML($page, array('showLatestActivityOnTop'=>true));

                        if (CFactory::getConfig()->get('event_moderation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_EVENTS_MODERATION_NOTICE', $event->title));
                        }

                        break;
                }

                break;
            case 'file':
                switch ($attachment['element']) {
                    case 'profile':
                        if (COwnerHelper::isMine($my->id, $attachment['target'])) {
                            /* If no privacy in attachment than we apply default: Public */
                            if (empty($attachment['privacy'])) $attachment['privacy'] = COMMUNITY_STATUS_PRIVACY_PUBLIC;
                        }

                        //if actor posted something to target, the privacy should be under target's profile privacy settings
                        if (!COwnerHelper::isMine($my->id, $attachment['target']) && $attachment['target'] != '') {
                            $attachment['privacy'] = CFactory::getUser($attachment['target'])->getParams()->get('privacyProfileView');
                        }

                        //push to activity stream
                        $act = new stdClass();
                        $act->cmd = 'file.sharing';
                        $act->actor = $my->id;
                        $act->target = $attachment['target'];
                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'filesharing';
                        $act->cid = $my->id;
                        $act->access = $attachment['privacy'];
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'file.sharing';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'file.sharing';

                        $activityParams = new CParameter('');
                        $activityParams->set('files', $attachment['id']);
                        
                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }
                        
                        $act->params = $activityParams->toString();

                        //check if the user points is enabled
                        if(CUserPoints::assignPoint('profile.status.update')){
                            /* Let use our new CApiStream */
                            $activityData = CApiActivities::add($act);
                            CTags::add($activityData);

                            // set activity id 
                            foreach ($attachment['id'] as $fileid) {
                                $fileTable = JTable::getInstance('File', 'CTable');
                                $fileTable->load($fileid);
                                
                                $fileTable->actid = $activityData->id;
                                $fileTable->store();
                            }
                            
                            $recipient = CFactory::getUser($attachment['target']);
                            $params = new CParameter('');
                            $params->set('actorName', $my->getDisplayName());
                            $params->set('recipientName', $recipient->getDisplayName());
                            $params->set('url', CUrlHelper::userLink($act->target, false));
                            $params->set('message', $message);
                            $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                            $params->set('stream_url',CRoute::_('index.php?option=com_community&view=profile&userid='.$activityData->actor.'&actid='.$activityData->id));

                            CNotificationLibrary::add('profile_status_update', $my->id, $attachment['target'], JText::sprintf('COM_COMMUNITY_FRIEND_WALL_POST', $my->getDisplayName()), '', 'wall.post', $params);

                            //email and add notification if user are tagged
                            CUserHelper::parseTaggedUserNotification($message, $my, $activityData, array('type' => 'post-comment'));
                        }
                    break;
                    case 'groups':
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if (!COwnerHelper::isCommunityAdmin() && !$group->isMember($my->id) && $config->get('lockgroupwalls')) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        $act = new stdClass();
                        $act->cmd = 'file.sharing';
                        $act->actor = $my->id;
                        $act->target = 0;

                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'filesharing';
                        $act->cid = $attachment['target'];
                        $act->groupid = $group->id;
                        $act->group_access = $group->approvals;
                        $act->eventid = 0;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'file.sharing';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'file.sharing';

                        $activityParams = new CParameter('');
                        $activityParams->set('files', $attachment['id']);

                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);

                        // set activity id 
                        foreach ($attachment['id'] as $fileid) {
                            $fileTable = JTable::getInstance('File', 'CTable');
                            $fileTable->load($fileid);
                            
                            $fileTable->actid = $activityData->id;
                            $fileTable->store();
                        }

                        CTags::add($activityData);
                        CUserPoints::assignPoint('group.wall.create');

                        $recipient = CFactory::getUser($attachment['target']);
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('group', $group->name);
                        $params->set('group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id, false));

                        //Get group member emails
                        $model = CFactory::getModel('Groups');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $groupParams = new CParameter($group->params);

                        if($groupParams->get('wallnotification')) {
                            CNotificationLibrary::add('groups_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $group->name), '', 'groups.post', $params);
                        }

                        //@since 4.1 when a there is a new post in group, dump the data into group stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addGroupStats($group->id, 'post');

                        // Add custom stream
                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                    break;
                    
                    case 'pages':
                        $pageLib = new CPages();
                        $page = JTable::getInstance('Page', 'CTable');
                        $page->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if (!COwnerHelper::isCommunityAdmin() && !$page->isMember($my->id) && $config->get('lockpagewalls')) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        $act = new stdClass();
                        $act->cmd = 'file.sharing';
                        $act->actor = $my->id;
                        $act->target = 0;

                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'filesharing';
                        $act->cid = $attachment['target'];
                        $act->pageid = $page->id;
                        $act->page_access = $page->approvals;
                        $act->groupid = 0;
                        $act->group_access = 0;
                        $act->eventid = 0;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'file.sharing';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'file.sharing';

                        $activityParams = new CParameter('');
                        $activityParams->set('files', $attachment['id']);

                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);

                        // set activity id 
                        foreach ($attachment['id'] as $fileid) {
                            $fileTable = JTable::getInstance('File', 'CTable');
                            $fileTable->load($fileid);
                            
                            $fileTable->actid = $activityData->id;
                            $fileTable->store();
                        }

                        CTags::add($activityData);
                        CUserPoints::assignPoint('page.wall.create');

                        $recipient = CFactory::getUser($attachment['target']);
                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('page', $page->name);
                        $params->set('page_url', 'index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=pages&task=viewpage&pageid=' . $page->id, false));

                        //Get page member emails
                        $model = CFactory::getModel('Pages');
                        $members = $model->getMembers($attachment['target'], null, true, false, true);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }
                        $pageParams = new CParameter($page->params);

                        if($pageParams->get('wallnotification')) {
                            CNotificationLibrary::add('pages_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT', $my->getDisplayName(), $page->name), '', 'pages.post', $params);
                        }

                        //@since 4.1 when a there is a new post in page, dump the data into page stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addPageStats($page->id, 'post');

                        // Add custom stream
                        // Reload the stream with new stream data
                        $streamHTML = $pageLib->getStreamHTML($page, array('showLatestActivityOnTop'=>true));

                    break;

                    case 'events':
                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if ((!COwnerHelper::isCommunityAdmin() && !$event->isMember($my->id) && $config->get('lockeventwalls'))) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        // If this is a group event, set the group object
                        $groupid = ($event->type == 'group') ? $event->contentid : 0;
                        //
                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($groupid);

                        $act = new stdClass();
                        $act->cmd = 'file.sharing';
                        $act->actor = $my->id;
                        $act->target = 0;
                        $act->title = $message;
                        $act->content = '';
                        $act->app = 'filesharing';
                        $act->cid = $attachment['target'];
                        $act->groupid = ($event->type == 'group') ? $event->contentid : 0;
                        $act->group_access = $group->approvals;
                        $act->eventid = $event->id;
                        $act->event_access = $event->permission;
                        $act->access = 0;
                        $act->comment_id = CActivities::COMMENT_SELF;
                        $act->comment_type = 'file.sharing';
                        $act->like_id = CActivities::LIKE_SELF;
                        $act->like_type = 'file.sharing';

                        $activityParams = new CParameter('');

                        //Store mood in paramm
                        if (!empty($attachment['mood']) && $attachment['mood'] != 'Mood') {
                            $activityParams->set('mood', $attachment['mood']);
                        }

                        $act->params = $activityParams->toString();

                        $activityData = CApiActivities::add($act);

                        // set activity id 
                        foreach ($attachment['id'] as $fileid) {
                            $fileTable = JTable::getInstance('File', 'CTable');
                            $fileTable->load($fileid);
                            
                            $fileTable->actid = $activityData->id;
                            $fileTable->store();
                        }
                        
                        CTags::add($activityData);

                        // add points
                        CUserPoints::assignPoint('event.wall.create');

                        $params = new CParameter('');
                        $params->set('message', $emailMessage);
                        $params->set('event', $event->title);
                        $params->set('event_url', 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);
                        $params->set('url', CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id, false));

                        //Get event member emails
                        $members = $event->getMembers(COMMUNITY_EVENT_STATUS_ATTEND, 12, CC_RANDOMIZE);

                        $membersArray = array();
                        if (!is_null($members)) {
                            foreach ($members as $row) {
                                if ($my->id != $row->id) {
                                    $membersArray[] = $row->id;
                                }
                            }
                        }

                        CNotificationLibrary::add('events_wall_create', $my->id, $membersArray, JText::sprintf('COM_COMMUNITY_NEW_WALL_POST_NOTIFICATION_EMAIL_SUBJECT_EVENTS', $my->getDisplayName(), $event->title), '', 'events.post', $params);

                        //@since 4.1 when a there is a new post in event, dump the data into event stats
                        $statsModel = CFactory::getModel('stats');
                        $statsModel->addEventStats($event->id, 'post');

                        // Reload the stream with new stream data
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));
                    break;
                }
                break;
            case 'poll':
                switch ($attachment['element']) {
                    case 'profile':
                        if (COwnerHelper::isMine($my->id, $attachment['target'])) {
                            /* If no privacy in attachment than we apply default: Public */
                            if (empty($attachment['privacy'])) $attachment['privacy'] = COMMUNITY_STATUS_PRIVACY_PUBLIC;
                        }

                        //if actor posted something to target, the privacy should be under target's profile privacy settings
                        if (!COwnerHelper::isMine($my->id, $attachment['target']) && $attachment['target'] != '') {
                            $attachment['privacy'] = CFactory::getUser($attachment['target'])->getParams()->get('privacyProfileView');
                        }

                        require_once(COMMUNITY_COM_PATH . '/controllers/polls.php');

                        $pollController = new CommunityPollsController();
                        $poll = $pollController->ajaxCreate($attachment, $message, $objResponse);

                        if (CFactory::getConfig()->get('moderatepollcreation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_POLLS_MODERATION_MSG', $poll->title));
                        }

                    break;
                    case 'groups':
                        require_once(COMMUNITY_COM_PATH . '/controllers/polls.php');

                        $groupLib = new CGroups();
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if (!COwnerHelper::isCommunityAdmin() && !$group->isMember($my->id) && $config->get('lockgroupwalls')) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $pollController = new CommunityPollsController();
                        $poll = $pollController->ajaxCreate($attachment, $message, $objResponse);

                        //add Group Notification
                        if (!CFactory::getConfig()->get('moderatepollcreation')) {
                            $group = JTable::getInstance( 'Group' , 'CTable' );
                            $group->load($attachment['target']);

                            $modelGroup = CFactory::getModel('groups');
                            $groupMembers = array();
                            $groupMembers = $modelGroup->getMembersId($attachment['target'], true);

                            // filter group creator.
                            if ($key = array_search($poll->creator, $groupMembers)) {
                                unset($groupMembers[$key]);
                            }

                            $subject = JText::sprintf('COM_COMMUNITY_GROUP_NEW_POLL_NOTIFICATION', $my->getDisplayName(), $group->name);
                            $params = new CParameter( '' );
                            $params->set('title', $poll->title);
                            $params->set('group', $group->name);
                            $params->set('group_url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id);
                            $params->set('poll', $poll->title);
                            $params->set('poll_url' , 'index.php?option=com_community&view=polls&groupid='.$group->id);
                            $params->set('url', 'index.php?option=com_community&view=polls&groupid='.$group->id);
                            CNotificationLibrary::add('groups_create_poll', $my->id, $groupMembers, JText::sprintf('COM_COMMUNITY_GROUP_NEW_EVENT_NOTIFICATION'), '', 'groups.poll', $params);
                        }

                        if (CFactory::getConfig()->get('moderatepollcreation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_POLLS_MODERATION_MSG', $poll->title));
                        }

                        // Reload the stream with new stream data
                        $streamHTML = $groupLib->getStreamHTML($group, array('showLatestActivityOnTop'=>true));

                    break;
                    case 'pages':
                        require_once(COMMUNITY_COM_PATH . '/controllers/polls.php');

                        $pageLib = new CPages();
                        $page = JTable::getInstance('Page', 'CTable');
                        $page->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if (!COwnerHelper::isCommunityAdmin() && !$page->isMember($my->id) && $config->get('lockpagewalls')) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        // Assign default values where necessary
                        $attachment['description'] = $message;
                        $attachment['ticket'] = 0;
                        $attachment['offset'] = 0;

                        $pollController = new CommunityPollsController();
                        $poll = $pollController->ajaxCreate($attachment, $message, $objResponse);

                        //add Page Notification
                        if (!CFactory::getConfig()->get('moderatepollcreation')) {
                            $page = JTable::getInstance( 'Page' , 'CTable' );
                            $page->load($attachment['target']);

                            $modelPage = CFactory::getModel('pages');
                            $pageMembers = array();
                            $pageMembers = $modelPage->getMembersId($attachment['target'], true);

                            // filter page creator.
                            if ($key = array_search($poll->creator, $pageMembers)) {
                                unset($pageMembers[$key]);
                            }

                            $subject = JText::sprintf('COM_COMMUNITY_PAGE_NEW_POLL_NOTIFICATION', $my->getDisplayName(), $page->name);
                            $params = new CParameter( '' );
                            $params->set('title', $poll->title);
                            $params->set('page', $page->name);
                            $params->set('page_url' , 'index.php?option=com_community&view=pages&task=viewpage&pageid='.$page->id);
                            $params->set('poll', $poll->title);
                            $params->set('poll_url' , 'index.php?option=com_community&view=polls&pageid='.$page->id);
                            $params->set('url', 'index.php?option=com_community&view=polls&pageid='.$page->id);
                            CNotificationLibrary::add('pages_create_poll', $my->id, $pageMembers, JText::sprintf('COM_COMMUNITY_PAGE_NEW_EVENT_NOTIFICATION'), '', 'pages.poll', $params);
                        }

                        if (CFactory::getConfig()->get('moderatepollcreation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_POLLS_MODERATION_MSG', $poll->title));
                        }

                        // Reload the stream with new stream data
                        $streamHTML = $pageLib->getStreamHTML($page, array('showLatestActivityOnTop'=>true));

                    break;
                    case 'events':
                        require_once(COMMUNITY_COM_PATH . '/controllers/polls.php');

                        $eventLib = new CEvents();
                        $event = JTable::getInstance('Event', 'CTable');
                        $event->load($attachment['target']);

                        // Permission check, only site admin and those who has
                        // mark their attendance can post message
                        if ((!COwnerHelper::isCommunityAdmin() && !$event->isMember($my->id) && $config->get('lockeventwalls'))) {
                            $objResponse->addScriptCall("alert('permission denied');");
                            return $objResponse->sendResponse();
                        }

                        $pollController = new CommunityPollsController();
                        $poll = $pollController->ajaxCreate($attachment, $message, $objResponse);
                        
                        if (CFactory::getConfig()->get('moderatepollcreation')) {
                            $objResponse->addAlert(JText::sprintf('COM_COMMUNITY_POLLS_MODERATION_MSG', $poll->title));
                        }

                        // Reload the stream with new stream data
                        $streamHTML = $eventLib->getStreamHTML($event, array('showLatestActivityOnTop'=>true));
                    break;
                }
                break;
            case 'link':
                break;
        }

        //no matter what kind of message it is, always filter the hashtag if there's any
        if(!empty($act->title)&& isset($activityData->id) && $activityData->id){
            //use model to check if this has a tag in it and insert into the table if possible
            $hashtags = CContentHelper::getHashTags($act->title);
            if(count($hashtags)){
                //$hashTag
                $hashtagModel = CFactory::getModel('hashtags');

                foreach($hashtags as $tag){
                    $hashtagModel->addActivityHashtag($tag, $activityData->id);
                }
            }
        }

        // Frontpage filter
        if ($streamFilter != false) {
            $streamFilter = json_decode($streamFilter);
            $filter = $streamFilter->filter;
            $value = $streamFilter->value;
            $extra = false;

            // Append added data to the list.
            if (isset($activityData) && $activityData->id) {
                $model = CFactory::getModel('Activities');
                $extra = $model->getActivity($activityData->id);
            }

            switch ($filter) {
                case 'privacy':
                    if ($value == 'me-and-friends' && $my->id != 0) {
                        $streamHTML = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true, array(), $extra);
                    } else {
                        $streamHTML = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array(), $extra);
                    }
                    break;

                case 'apps':
                    $streamHTML = CActivities::getActivitiesByFilter('apps', $my->id, 'frontpage', true, array('apps' => array($value)), $extra);
                    break;

                case 'hashtag';
                    $streamHTML = CActivities::getActivitiesByFilter('hashtag', $my->id, 'frontpage', true, array($filter => $value), $extra);
                    break;
                
                case 'keyword';
                    $streamHTML = CActivities::getActivitiesByFilter('keyword', $my->id, 'frontpage', true, array($filter => $value), $extra);
                    break;

                default:
                    $defaultFilter = $config->get('frontpageactivitydefault');
                    if ($defaultFilter == 'friends' && $my->id != 0) {
                        $streamHTML = CActivities::getActivitiesByFilter('active-user-and-friends', $my->id, 'frontpage', true, array(), $extra);
                    } else {
                        $streamHTML = CActivities::getActivitiesByFilter('all', $my->id, 'frontpage', true, array(), $extra);
                    }
                    break;
            }
        }

        if (empty($attachment['filter'])) {
            $attachment['filter'] = '';
            $filter = $config->get('frontpageactivitydefault');
            $filter = explode(':', $filter);

            $attachment['filter'] = (isset($filter[1])) ? $filter[1] : $filter[0];
        }

        if (empty($streamHTML)) {
            if (empty($attachment['target']))
                $attachment['target'] = '';
            if (empty($attachment['element']))
                $attachment['element'] = '';
            $streamHTML = CActivities::getActivitiesByFilter($attachment['filter'], $attachment['target'], $attachment['element'], true, array('show_featured'=>true,'showLatestActivityOnTop'=>true));
        }

        $objResponse->addAssign('activity-stream-container', 'innerHTML', $streamHTML);

        // Log user engagement
        CEngagement::log($attachment['type'] . '.share', $my->id);
        
        return $objResponse->sendResponse();
    }

    /**
     * Add comment to the stream
     *
     * @param int   $actid acitivity id
     * @param string $comment
     * @return obj
     */
    public function ajaxStreamAddComment($actid, $comment, $photoId = 0) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $my = CFactory::getUser();

        $wallModel = CFactory::getModel('wall');
        $rawComment = $comment;

        $json = array();

        $photoId = $filter->clean($photoId, 'int');

        // Pull the activity record and find out the actor
        // only allow comment if the actor is a friend of current user
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        //who can add comment
        $obj = $act;

        if ($act->groupid > 0) {
            $obj = JTable::getInstance('Group', 'CTable');
            $obj->load($act->groupid);
        } else if ($act->eventid > 0) {
            $obj = JTable::getInstance('Event', 'CTable');
            $obj->load($act->eventid);
        }

        //link the actual comment from video page itself to the stream
        if(isset($obj->comment_type) && $obj->comment_type == 'videos.linking'){
            $obj->comment_type = 'videos';
        }

        $params = new CParameter($act->params);

        $batchcount = $params->get('batchcount', 0);
        $wallParam = new CParameter('');

        // store last updated_at value, for delete purpose
        $wallParam->set('updated_at', $act->updated_at);

        if ($act->app == 'photos' && $batchcount > 1) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($params->get('photoid'));

            $act->comment_type = 'albums';
            $act->comment_id = $photo->albumid;

            $wallParam->set('activityId', $act->id);
        }

        //if photo id is not 0, this wall is appended with a picture
        if($photoId > 0){
            //lets check if the photo belongs to the uploader
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($photoId);

            if($photo->creator == $my->id && $photo->albumid == '-1'){
                $wallParam->set('attached_photo_id', $photoId);

                //sets the status to ready so that it wont be deleted on cron run
                $photo->status = 'ready';
                $photo->store();
            }
        }

        // Allow comment for system post
        $allowComment = false;
        if ($act->app == 'system') {
            $allowComment = !empty($my->id);
        }

        $commentType = $act->comment_type;
        if($act->comment_type == 'videos.linking'){
            //we convert videos.linking type to videos because we want to merge both of the comment together
            $commentType = 'videos';
        }

        if ($my->authorise('community.add', 'activities.comment.' . $act->actor, $obj) || $allowComment) {

            $table = JTable::getInstance('Wall', 'CTable');
            $table->type = $commentType;
            $table->contentid = $act->comment_id;
            $table->post_by = $my->id;
            $table->comment = $comment;
            $table->params = $wallParam->toString();

            //fetch url if there is any
            if (( preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $comment))) {

                $graphObject = CParsers::linkFetch($comment);
                
                if ($graphObject){
                    $url = $graphObject->get('url', '');
                    if(strlen($url)) {
                        $video = JTable::getInstance('Video', 'CTable');
                        if ($video->init($url)) {
                            $videoParam = new JRegistry();
                            $videoParam->set('videotitle', $video->title);
                            $videoParam->set('videotype', $video->type);
                            $videoParam->set('videoid', $video->video_id);
                            $videoParam->set('videodescription', $video->description);

                            if ($video->thumb) {
                                $config = CFactory::getConfig();
                                $remoteThumb = $video->thumb;
                                $thumbData = CRemoteHelper::getContent($remoteThumb, true);

                                // split the header and body
                                list($headers, $body) = explode("\r\n\r\n", $thumbData, 2);
                                preg_match('/Content-Type: image\/(.*)/i', $headers, $matches);

                                if (!empty($matches)) {
                                    $thumbPath = JPATH_ROOT . '/' . $config->get('videofolder') . '/' .VIDEO_FOLDER_NAME . '/wall';
                                    $thumbFileName = CFileHelper::getRandomFilename($thumbPath);
                                    $tmpThumbPath = $thumbPath . '/' . $thumbFileName;
                                    
                                    JFile::write($tmpThumbPath, $body);

                                    // Get the image type first so we can determine what extensions to use
                                    $info = getimagesize($tmpThumbPath);
                                    $mime = image_type_to_mime_type($info[2]);
                                    $thumbExtension = CImageHelper::getExtension($mime);

                                    $thumbFilename = $thumbFileName . $thumbExtension;
                                    $thumbPath = $thumbPath . '/' . $thumbFilename;
                                    
                                    JFile::move($tmpThumbPath, $thumbPath);

                                    list($width, $height) = explode('x', $config->get('videosThumbSize'));
                                    CImageHelper::resizeAspectRatio($thumbPath, $thumbPath, $width, $height);

                                    $thumb = $config->get('videofolder') . '/' . VIDEO_FOLDER_NAME . '/wall/' . $thumbFilename;
                                    $video->thumb = $thumb;
                                }
                            }

                            $videoParam->set('videothumb', $video->thumb);
                            $videoParam->set('videourl', $url);

                            $providerParams = new JRegistry($video->_provider->get('params'));
                            $videoParam->set('width', $providerParams->get('width', 16));
                            $videoParam->set('height', $providerParams->get('height', 9));

                            $graphObject->merge($videoParam);
                        }
                    }

                    $graphObject->merge($wallParam);
                    if ($graphObject->toString() != false) $table->params = $graphObject->toString();
                }
            }

            $table->store();

            $cache = CFactory::getFastCache();
            $cache->clean(array('activities'));

            if ($act->app == 'photos') {
                $table->contentid = $act->id;
            }
            $table->params = new CParameter($table->get('params'));
            $args[] = $table;
            CWall::triggerWallComments($args, false);
            $comment = CWall::formatComment($table);

            $json['html'] = $comment;

            //notification for activity comment
            //case 1: user's activity
            //case 2 : group's activity
            //case 3 : event's activity
            if ($act->groupid == 0 && $act->eventid == 0) {
                // //CFactory::load( 'libraries' , 'notification' );
                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=profile&userid=' . $act->actor . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('actor', $my->getDisplayName());
                $params->set('actor_url', CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                if ($my->id != $act->actor) {
                    /* Notifications to all poster in this activity except myself */
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $my->id);
                    if (!empty($users)) {
                        if(!in_array($act->actor, $users)) {
                            array_push($users,$act->actor);
                        }
                        $commenters = array_diff($users, array($act->actor));
                        // this will sent notification to the participant only
                        CNotificationLibrary::add('profile_activity_add_comment', $my->id, $commenters, JText::sprintf('COM_COMMUNITY_ACTIVITY_WALL_PARTICIPANT_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);

                        // this will sent a notification to the poster, reason is that the title should be different
                        CNotificationLibrary::add('profile_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    } else {
                        CNotificationLibrary::add('profile_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'), '', 'profile.activitycomment', $params);
                    }
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('profile_activity_reply_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    }
                }
            } elseif ($act->groupid != 0 && $act->eventid == 0) { /* Group activity */

                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $act->groupid . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('actor', $my->getDisplayName());
                $params->set('actor_url', CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);

                if ($my->id != $act->actor) {
                    /* Notifications to all poster in this activity except myself */
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $my->id);
                    if (!empty($users)) {
                        if(!in_array($act->actor, $users)) {
                            array_push($users,$act->actor);
                        }
                        $commenters = array_diff($users, array($act->actor));
                        // this will sent notification to the participant only
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $commenters, JText::sprintf('COM_COMMUNITY_ACTIVITY_GROUP_WALL_PARTICIPANT_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);

                        // this will sent a notification to the poster, reason is that the title should be different
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'profile.activityreply', $params);
                    } else {
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'profile.activitycomment', $params);
                    }
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('groups_activity_add_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), $table->comment, 'group.activityreply', $params);
                    }
                }
            } elseif ($act->eventid != 0) {
                $event = JTable::getInstance('Event','CTable');
                $event->load($act->eventid);
                $params = new CParameter('');
                $params->set('message', $table->comment);
                $url = 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $act->eventid . '&actid=' . $actid;
                $params->set('url', $url);
                $params->set('actor', $my->getDisplayName());
                $params->set('actor_url', CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id));
                $params->set('stream', JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
                $params->set('stream_url', $url);
                $params->set('event', $event->title);

                if ($my->id != $act->actor) {
                    CNotificationLibrary::add('events_submit_wall_comment', $my->id, $act->actor, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EVENT_EMAIL_SUBJECT'), '', 'events.wallcomment', $params);
                } else {
                    //for activity reply action
                    //get relevent users in the activity
                    $users = $wallModel->getAllPostUsers($act->comment_type, $act->id, $act->actor);
                    if (!empty($users)) {
                        CNotificationLibrary::add('events_activity_reply_comment', $my->id, $users, JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_USER_REPLY_EMAIL_SUBJECT'), '', 'event.activityreply', $params);
                    }
                }
            }

            //notifications
            CUserHelper::parseTaggedUserNotification($rawComment, $my, $act, array('type' => 'post-comment'));

            //Add tag
            CTags::add($table);
            // Log user engagement
            CEngagement::log($act->app . '.comment', $my->id);
        } else {
            $json['error'] = 'Permission denied.';
        }

        if ( !isset($json['error']) ) {
            $json['success'] = true;
        }

        die( json_encode($json) );
    }

    /**
     * Remove a wall comment
     *
     * @param int $actid
     * @param int $wallid
     */
    public function ajaxStreamRemoveComment($wallid) {
        $filter = JFilterInput::getInstance();
        $wallid = $filter->clean($wallid, 'int');

        $my = CFactory::getUser();

        //
        //@todo: check permission. Find the activity id that
        // has this wall's data. Make sure actor is friend with
        // current user

        $table = JTable::getInstance('Wall', 'CTable');
        $table->load($wallid);

        if(!$my->authorise('community.delete','walls', $table)){
            return false;
        }

        $wallParam   = new JRegistry();
        $wallParam->loadString($table->params);

        $contenOwner = false;
        if ($table->type == 'photos') {
            $photoTable = JTable::getInstance('Photo','CTable');
            $photoTable->load($table->contentid);

            $contenOwner = ($photoTable->creator == $my->id);
        } else if ($table->type == 'videos') {
            $videoTable = JTable::getInstance('Video','CTable');
            $videoTable->load($table->contentid);

            $contenOwner = ($videoTable->creator == $my->id);
        } else if ($wallParam->get('activityId', 0)) {
            $actTable = JTable::getInstance('Activity','CTable');
            $actTable->load($wallParam->get('activityId'));

            $contenOwner = ($actTable->actor == $my->id || $actTable->target == $my->id);
        } else {
            $actTable = JTable::getInstance('Activity','CTable');
            $actTable->load($table->contentid);

            $contenOwner = ($actTable->actor == $my->id || $actTable->target == $my->id);
        }
        
        // Access check: ACL
        if ($my->authorise('community.postcommentdelete', 'com_community') || $table->post_by == $my->id || $contenOwner) {
            // revert updated at to last comment
            if ($wallParam->get('updated_at', '')) {
                $wallModel = CFactory::getModel('wall');
                $getLastPost = $wallModel->getLastPost($table->contentid);
                
                if ($getLastPost->id == $wallid) {
                    $wallParamDelete = new JRegistry();
                    $wallParamDelete->loadString($getLastPost->params);

                    if ($wallParamDelete->get('updated_at', '')) {
                        $streamTable = JTable::getInstance('Activity','CTable');
                
                        if ($streamTable->load($table->contentid)) {
                            $streamTable->updated_at = $wallParamDelete->get('updated_at', '');
                            $streamTable->store();
                        }

                        $streamTable->reset();
                    }
                }
            }

            // check if there is any image appended, if yes, remove the picture.
            if ($wallParam->get('attached_photo_id') > 0 ) {
                $photoModel = CFactory::getModel('photos');
                $photoTable = $photoModel->getPhoto($wallParam->get('attached_photo_id'));
                $photoTable->delete();
            }

            // for article comment plugin, delete stream and notfication if exist
            if ($wallParam->get('notificationId', '')) {
                $notificationId = explode('-', $wallParam->get('notificationId'));
                $notificationTable = JTable::getInstance('Notification','CTable');

                foreach ($notificationId as $id) {
                    if ($id > 0) {
                        if ($notificationTable->load($id)) {
                            $notificationTable->delete();
                        }
                        $notificationTable->reset();
                    }
                }
            }

            if ($wallParam->get('activityId', 0) > 0) {
                $streamTable = JTable::getInstance('Activity','CTable');
                
                if ($streamTable->load($wallParam->get('activityId'))) {
                    $streamTable->delete();
                }
                $streamTable->reset();
            }

            if ($table->delete()) {
                // delete video thumbnail
                if ($wallParam->get('videothumb', '')) {
                    $videothumb = $wallParam->get('videothumb');
                    if (CStringHelper::substr($videothumb, 0, 4) != 'http') {
                        if (JFile::exists(JPATH_ROOT . '/' . $videothumb)) {
                            JFile::delete(JPATH_ROOT . '/' . $videothumb);
                        }
                    }
                }
            }

            $json = array();
            $json['success'] = true;
            $json['parent_id'] = $wallParam->get('activityId');

            die( json_encode($json) );
        } else {
            $json = array();
            $json['success'] = false;
            $json['parent_id'] = null;

            die( json_encode($json) );
        }
    }

    /**
     * @param $actid
     * @param bool|false $type
     * @param int $totalShown the total entries that we have currently
     * @param int $limit the limit per load, if set to zero, will load the entire stream
     */
    public function ajaxStreamShowComments($actid, $type = false, $totalShown = 0 ,$limit = 0) {
        if ( $type ) {
            $this->ajaxWallShowComments( $actid, $type, $totalShown ,$limit );
            return;
        }

        $limit = $totalShown + $limit;

        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');

        $wallModel = CFactory::getModel('wall');

        // Pull the activity record and find out the actor
        // only allow comment if the actor is a friend of current user
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);
        $params = new CParameter($act->params);

        //if this is an album type, this is an unique wall because it's aggregated, so we must determine the walls based on the params
        $commentsParam = '';
        if($act->comment_type == 'photos' && $params->get('batchcount', 0) > 1){
            $commentsParam = json_encode(array('activityId'=>$act->id));
        }

        if ($act->comment_type == 'photos' && $params->get('batchcount', 0) > 1) {
            $act->comment_type = 'albums';
            $act->comment_id = $act->cid;
        } else if ($act->comment_type == 'videos.linking') {
            $act->comment_type = 'videos';
            $act->comment_id = $act->cid;
        }

        $model = CFactory::getModel('wall');
        $totalResults = $model->getPostCount($act->comment_type, $act->comment_id, $commentsParam);

        $limitStart = ($totalResults-$limit <= 0) ? 0 :  $totalResults-$limit;

        $comments = $wallModel->getAllPost($act->comment_type, $act->comment_id, $limit , $limitStart, $commentsParam);
        $commentsHTML = '';

        CWall::triggerWallComments($comments, false);

        $count = 0;
        foreach ($comments as $row) {

            if($limit && $limit == $count){
                break;
            }

            $row->params = new CParameter($row->get('params', '{}'));
            if ($row->type == 'albums' && $row->params->get('activityId', NULL) != $actid && $params->get('batchcount', 0) > 1) {
                continue;
            }
            $row->stream_id = $actid;
            $commentsHTML .= CWall::formatComment($row);

            $count += 1;
        }

        $json = array();
        $json['success'] = true;
        $json['html'] = $commentsHTML;

        $json['total'] = $totalResults;

        die( json_encode($json) );
    }

    public function ajaxWallShowComments($uniqueId, $type = false, $totalShown = 0, $limit = 0) {
        $my = CFactory::getUser();
        $html = '';
        $model = CFactory::getModel('wall');

        $limit = $totalShown + $limit;

        if ( $type == 'albums' ) {
            $album = JTable::getInstance('Album', 'CTable');
            $album->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $album->id,
                ( COwnerHelper::isCommunityAdmin() || COwnerHelper::isMine($my->id, $album->creator)),
                $limit,
                0
            );
        } else if ( $type == 'discussions' ) {
            $discussion = JTable::getInstance('Discussion', 'CTable');
            $discussion->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $discussion->id,
                ( $my->id == $discussion->creator ),
                $limit,
                0,
                'wall/content',
                'groups,discussion'
            );
        } else if ( $type == 'videos' ) {
            $video = JTable::getInstance('Video', 'CTable');
            $video->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $video->id,
                ( COwnerHelper::isCommunityAdmin() || ($my->id == $video->creator && ($my->id != 0))),
                $limit,
                0,
                'wall/content',
                'videos,video'
            );
        } else if ( $type == 'photos' ) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($uniqueId);
            $html = CWallLibrary::getWallContents(
                $type,
                $photo->id,
                ( COwnerHelper::isCommunityAdmin() || $my->id == $photo->creator ),
                $limit,
                0,
                'wall/content',
                'photos,photo'
            );
        } else if ( strpos( $type, 'service.comment.' ) === 0 ) {
            $html = CWallLibrary::getWallContents(
                $type,
                $uniqueId,
                COwnerHelper::isCommunityAdmin(),
                $limit,
                0
            );
        }

        $json = array();
        $json['success'] = true;
        $json['html'] = $html;
        $json['total'] = $model->getPostCount($type, $uniqueId);

        die( json_encode($json) );
    }

    public function ajaxStreamAddLike($actid, $type = null, $reactionid = 1) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $reactionid = $filter->clean($reactionid, 'int');

        $objResponse = new JAXResponse();
        $wallModel = CFactory::getModel('wall');
        $like = new CLike();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        //guest cannot like
        $user = CFactory::getUser();
        if(!$user->id){
            die;
        }

        /**
         * some like_type are missing and causing stream id cannot be like, in this case, group create.
         * This condition is used to fix the existing activity with such issue.
         */
        if($act->comment_type == 'groups.create' && empty($act->like_type)){
            $act->like_type = 'groups.create';
            $act->store();
        }

        if($act->comment_type == 'pages.create' && empty($act->like_type)){
            $act->like_type = 'pages.create';
            $act->store();
        }

        if ($type == 'comment') {
            $act = JTable::getInstance('Wall', 'CTable');
            $act->load($actid);
            $act->like_type = 'comment';
            $act->like_id = $act->id;
        }

        $params = new CParameter($act->params);

        // this is used to seperate the like from the actual pictures
        if (isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) >= 1) {
            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && ($act->app == 'videos' || $act->app === 'videos.linking')) {
            return $this->ajaxLike('videos', $act->cid, $reactionid);
        } else if (isset($act->app)) {
            $act->like_type = $act->app;
            $act->like_id = $act->id;
            $act->store();
        }

        if (!$act->like_id) {
            die('error');
        }

        // Count before the add
        $oldLikeCount = $like->getLikeCount($act->like_type, $act->like_id);

        $doLike = $like->addLike($act->like_type, $act->like_id, $reactionid);

        if ($doLike) {
            $likeCount = $like->getLikeCount($act->like_type, $act->like_id);

            $json = array();
            $json['success'] = true;

            // If the like count is 1, then, the like bar most likely not there before
            // but, people might just click twice, hence the need to compare it before
            // the actual like

            if ($likeCount == 1 && $oldLikeCount != $likeCount) {
                // Clear old like status
                $objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid} .cStream-Likes').remove", '');
                $objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid}').prepend", '<div class="cStream-Likes"></div>');
            }

            $config = CFactory::getConfig();
            $enableReaction = !!$config->get('enablereaction');

            if ($enableReaction) {
                if ($type === 'comment') {
                    $json['html'] = CLikesHelper::commentRenderReactionStatus($act->id);
                } else {
                    $json['html'] = CLikesHelper::streamRenderReactionStatus($act->id);
                }
            } else {
                if ($type === 'comment') {
                    $json['html'] = $this->_commentShowLikes($objResponse, $act->id);
                } else {
                    $json['html'] = $this->_streamShowLikes($objResponse, $act->id, $act->like_type, $act->like_id);
                }
            }
        } else {
            $json = array('error' => 'liked');
        }

        die( json_encode($json) );
    }

    /**
     *
     */
    public function ajaxStreamUnlike($actid, $type = null, $reactionid = 1) {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');
        $reactionid = $filter->clean($reactionid, 'int');

        $objResponse = new JAXResponse();
        $like = new CLike();

        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        if ($type == 'comment') {
            $act = JTable::getInstance('Wall', 'CTable');
            $act->load($actid);
            $act->like_type = 'comment';
            $act->like_id = $act->id;
        }

        $params = new CParameter($act->params);

        if (isset($act->app) && $act->app == 'photos') {
            if ($params->get('batchcount', 0) > 1) {
               $act->like_type = 'album.self.share';
                $act->like_id = $act->id;
            } else {
                $act->like_type = 'photo';
            }
        } else if (isset($act->app) && $act->app == 'albums') {
            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && ($act->app == 'videos' || $act->app === 'videos.linking')) {
            return $this->ajaxUnlike('videos', $act->cid);
        }

        if (!$act->like_id) {
            die('error');
        }

        $result = $like->unlike($act->like_type, $act->like_id, null, $reactionid);

        if ($result) {
            $json = array();
            $json['success'] = true;

            $config = CFactory::getConfig();
            $enableReaction = !!$config->get('enablereaction');

            if ($enableReaction) {
                if ($type === 'comment') {
                    $json['html'] = CLikesHelper::commentRenderReactionStatus($act->id);
                } else {
                    $json['html'] = CLikesHelper::streamRenderReactionStatus($act->id);
                }
            } else {
                if ($type == 'comment') {
                    $json['html'] = $this->_commentShowLikes($objResponse, $act->id);
                } else {
                    $json['html'] = $this->_streamShowLikes($objResponse, $act->id, $act->like_type, $act->like_id);
                }
            }
        } else {
            $json['error'] = 'unlike failed';
        }
        
        die( json_encode($json) );
    }

    /**
     * List down all people who like it
     *
     */
    public function ajaxStreamShowLikes($actid, $target = '') {
        $filter = JFilterInput::getInstance();
        $actid = $filter->clean($actid, 'int');

        $objResponse = new JAXResponse();
        $wallModel = CFactory::getModel('wall');

        // Pull the activity record
        $act = JTable::getInstance('Activity', 'CTable');
        $act->load($actid);

        $params = new CParameter($act->params);

        if (isset($act->app) && $act->app == 'photos' && $params->get('batchcount', 0) > 0) {
            $act->like_type = 'album.self.share';
            $act->like_id = $act->id;
        } else if (isset($act->app) && $act->app == 'videos') {
            $act->like_type = 'videos.self.share';
            $act->like_id = $act->id;
        }

        $json = array(
            'success' => true,
            'html' => $this->_streamShowLikes($objResponse, $actid, $act->like_type, $act->like_id, $target)
        );

        die( json_encode( $json ) );
    }

    public function ajaxDeleteTempImage($photo_ids) {
        $ids = explode(',', $photo_ids);
        $ids = array_map(function($id) {
            return (int) $id;
        }, $ids);

        $my = CFactory::getUser();

        foreach ($ids as $photoid) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($photoid);

            //we must make sure that the creator is the current user
            if ($photo->creator == $my->id && $photo->status == 'temp') {
                $photo->delete();
            }
        }

        die('temp img deleted');
    }
    
    public function ajaxDeleteTempFile() {
        $jinput = JFactory::getApplication()->input;
        $file_ids = $jinput->get('arg2', 'default_value', 'array');
        
        $my = CFactory::getUser();

        foreach ($file_ids as $fileid) {
            $file = JTable::getInstance('File', 'CTable');
            $file->load($fileid);
            
            if ($file->creator == $my->id && $file->actid == -1) {
                $file->delete();
            }
        }

        exit;
    }

    /**
     * Display the full list of people who likes this stream item
     *
     * @param <type> $objResponse
     * @param <type> $actid
     * @param <type> $like_type
     * @param <type> $like_id
     */
    private function _streamShowLikes($objResponse, $actid, $like_type, $like_id, $target = '') {
        $my = CFactory::getUser();
        $like = new CLike();

        $likes = $like->getWhoLikes($like_type, $like_id);

        $canUnlike = false;
        $likeHTML = '';
        $likeUsers = array();

        foreach ($likes as $user) {
            $likeUsers[] = '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
            if ($my->id == $user->id)
                $canUnlike = true;
        }

        if (count($likeUsers) != 0) {
            if ( $target === 'popup' ) {
                $tmpl = new CTemplate();
                $tmpl->set('users', $likes);
                $likeHTML = $tmpl->fetch('ajax.stream.showothers');
            } else {
                $likeHTML .= implode(", ", $likeUsers);
                $likeHTML = CStringHelper::isPlural(count($likeUsers)) ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY_LIST', $likeHTML) : JText::sprintf('COM_COMMUNITY_LIKE_THIS_LIST', $likeHTML);
            }
        }

        return $likeHTML;
    }

    private function _commentShowLikes($obj, $actid) {
        $my = CFactory::getUser();
        $like = new CLike();

        $likeHTML = '';
        $likeCount = $like->getLikeCount('comment', $actid);

        if ($likeCount > 0) {
            $likeHTML = '<a href="javascript:" data-action="showlike" onclick="joms.api.commentShowLikes(\'' . $actid . '\');"><i class="joms-icon-thumbs-up"></i><span>' . $likeCount . '</span></a>';
        }

        return $likeHTML;
    }

    public function ajaxeditComment($id, $value, $photoId = 0) {
        $config = CFactory::getConfig();
        $my = CFactory::getUser();
        $actModel = CFactory::getModel('activities');
        $objResponse = new JAXResponse();
        $json = array();

        if ($my->id == 0) {
            $this->blockUnregister();
        }

        $wall = JTable::getInstance('wall', 'CTable');
        $wall->load($id);

        $cid = isset($wall->contentid) ? $wall->contentid : null;
        $activity = $actModel->getActivity($cid);
        $ownPost = ($my->id == $wall->post_by);
        $targetPost = ($activity->target == $my->id);

        $value = trim($value);

        // Access check: ACL
        if ($config->get('wallediting') && $my->authorise('community.postcommentedit', 'com_community') || ( ( $ownPost || $targetPost ) && !empty($my->id) )) {
            $params = new CParameter($wall->params);

            //if photo id is not 0, this wall is appended with a picture
            if($photoId > 0 && $params->get('attached_photo_id') != $photoId ){
                //lets check if the photo belongs to the uploader
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->load($photoId);

                if($photo->creator == $my->id && $photo->albumid == '-1'){
                    $params->set('attached_photo_id', $photoId);

                    //sets the status to ready so that it wont be deleted on cron run
                    $photo->status = 'ready';
                    $photo->store();
                }
            }else if($photoId == -1 ){
                //if there is nothing, remove the param if applicable
                //delete from db and files
                $photoModel = CFactory::getModel('photos');
                $photoTable = $photoModel->getPhoto($params->get('attached_photo_id'));
                $photoTable->delete();

                $params->set('attached_photo_id' , 0);
            }

            if ($photoId == -1 && !$value) {
                $wall->delete();
            } else {
                $wall->params = $params->toString();
                $wall->comment = $value;
                $wall->store();

                $CComment = new CComment();
                $value = $CComment->stripCommentData($value);

                // Need to perform basic formatting here
                // 1. support nl to br,
                // 2. auto-link text
                $CTemplate = new CTemplate();
                $value = $origValue = $CTemplate->escape($value);
                $value = CStringHelper::autoLink($value);
                $value = nl2br($value);
                $value = CUserHelper::replaceAliasURL($value);
                $value = CStringHelper::getEmoticon($value);

                $json['comment'] = $value;
                $json['originalComment'] = $origValue;

                // $objResponse->addScriptCall("joms.jQuery('div[data-commentid=" . $id . "] .cStream-Content span.comment').html", $value);
                // $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").val', $origValue);
                // $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-editor] textarea").removeData', 'initialized');

                // if ($photoId == -1) {
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-thumb").parent().remove', '');
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-attachment").css("display", "none").attr("data-no_thumb", 1);');
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-thumbnail").html', '<img/>');
                // } else if ($photoId != 0) {
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-fetch-wrapper").remove', '');
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-thumb").parent().remove', '');
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] [data-type=stream-comment-content] .cStream-Meta").before', '<div style="padding: 5px 0"><img class="joms-stream-thumb" src="' . JUri::root(true) ."/". $photo->thumbnail . '" /></div>');
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-stream-attachment").css("display", "block").removeAttr("data-no_thumb");');
                //     $objResponse->addScriptCall('joms.jQuery("div[data-commentid=' . $id . '] .joms-thumbnail img").attr("src", "' . JUri::root(true) ."/". $photo->thumbnail . '").attr("data-photo_id", "0").data("photo_id", 0);');
                // }
            }

        } else {
            $json['error'] = JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT');
        }


        if ( !isset($json['error']) ) {
            $json['success'] = true;
        }

        die( json_encode($json) );
    }

    /**
     *
     * @param type $text
     * @return type
     */
    public function ajaxGetFetchUrl($text) {
        $graphObject = CParsers::linkFetch($text);
        if ( $graphObject ) {
            $data = (array) $graphObject->toObject();
            die( json_encode($data) );
        }

        die( json_encode($graphObject) );
    }

    public function ajaxGetAdagency(){
        //prevent guest
        // $user = CFactory::getUser();
        // if(!$user->id){
        //     die;
        // }

        if (CSystemHelper::isComponentExists('com_adagency') && JComponentHelper::getComponent('com_adagency', true)->enabled) {

            $lang = JFactory::getLanguage();
            $extension = 'com_adagency';
            $base_dir = JPATH_SITE;
            $lang->load($extension, $base_dir);

            jimport('joomla.application.component.model');
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_adagency/models');
            $agencyModel = JModelLegacy::getInstance('Adagencyadvertisement', 'AdagencyModel');

            $advertisement = $agencyModel->getJomsocialAds();
            $config = $agencyModel->getJomsocialAdSettings();

            // Add configs.
            $config['create_ad_link_text'] = JText::_('ADAG_CREATE_AN_AD');
            $config['sponsored_stream_info_text'] = JText::_('ADAG_SPONSORED_STREAM');

            die(json_encode(
                array(
                    'config' => $config,
                    'ads' => $advertisement
                )
            ));
        }
    }

    public function ajaxAdagencyGetImpression($adsId, $campaignId, $bannerId, $type){
        if (CSystemHelper::isComponentExists('com_adagency') && JComponentHelper::getComponent('com_adagency', true)->enabled) {
            jimport('joomla.application.component.model');
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_adagency/models');
            $agencyModel = JModelLegacy::getInstance('Adagencyadvertisement', 'AdagencyModel');

            $agencyModel->increaseImpressions($adsId, $campaignId, $bannerId, $type); // call to increase impression
            die(json_encode(
                array(
                    'status' => true
                )
            ));
        }
    }

    /**
     * this function will call the function via ajax to the respective module
     */
    public function ajaxModuleCall(){
        $jinput = JFactory::getApplication()->input;

        $module = $jinput->get('module','','STRING');
        $method = $jinput->get('method','','STRING');

        //check if module exists
        $mod = JModuleHelper::getModule($module);
        if(isset($mod->id) && $mod->id && !empty($module) && !empty($method)){
            $helperFile = JPATH_ROOT . '/modules/mod_' . $mod->name.'/helper.php';
            if(file_exists($helperFile)){
                require_once($helperFile);
                $modName = explode('_',$mod->name);
                $className = 'Mod';
                foreach($modName as $name){
                    $className .= ucfirst($name);
                }
                $className .= 'Helper';

                if(class_exists($className) && method_exists($className, $method)){
                    return $className::$method();
                }
            }
        }

        return false;
    }

    //this will return the login token for the form
    public function ajaxGetLoginFormToken(){
        die( json_encode( array('token' => JSession::getFormToken() ) ) );
    }

    public function ajaxFeatureStream($streamType, $contextId, $actid){
        $my = CFactory::getUser();

        $extraInfo = array();
        $extraInfo['stream_type'] = $streamType;
        $extraInfo['profile_id'] = $contextId;

        $act = JTable::getInstance('activity','CTable');
        $act->load($actid);
        $act->extraInfo = $extraInfo;
        //display error if there is no id
        if(!$my->id || !$my->authorise('community.feature','activities.stream',$act)) {
            die(json_encode(array('error' => 'Invalid')));
        }

        $featuredModel = CFactory::getModel('featured');
        $featuredTable = $featuredModel->insertFeaturedStream($actid, $streamType, $contextId);

        if($featuredTable){
            die(json_encode(array('success' => TRUE)));
        }
        die(json_encode(array('error' => 'Error.')));
    }

    public function ajaxUnfeatureStream($streamType, $contextId, $actid){
        $my = CFactory::getUser();

        $extraInfo = array();
        $extraInfo['stream_type'] = $streamType;
        $extraInfo['profile_id'] = $contextId;

        $act = JTable::getInstance('activity','CTable');
        $act->load($actid);
        $act->extraInfo = $extraInfo;
        //display error if there is no id
        if(!$my->id || !$my->authorise('community.unfeature','activities.stream',$act)){
            die(json_encode(array('error' => 'Invalid')));
        }

        $featuredModel = CFactory::getModel('featured');
        $status = $featuredModel->deleteFeaturedStream($actid, 'stream.'.$streamType, $contextId);

        if($status){
            die(json_encode(array('success' => TRUE)));
        }
        die(json_encode(array('error' => 'Error.')));
    }

    public function ajaxDefaultUserStream($defaultFilter){
        //get current user
        $my = CFactory::getUser();


        $allowedFilter = array('all','privacy:me-and-friends','apps:profile','apps:photo','apps:video','apps:group','apps:event','apps:filesharing','apps:polls','apps:my-following');
        //quick validation
        if(!$my->id || !in_array($defaultFilter,$allowedFilter)){
            die( json_encode( array(
                'error' => JText::_('COM_COMMUNITY_SAVE_DEFAULT_FILTER_FAILED')
            ) ) );
        }

        //do a quick change here
        //$userParams = new CParameter($my->params);
        $my->_cparams->set('frontpageactivitydefault', $defaultFilter);
        //$my->params = $userParams->toString();
        $my->save();

        die( json_encode( array(
            'success' => true,
            'message' => JText::_('COM_COMMUNITY_SAVE_DEFAULT_FILTER_SUCCESS')
        ) ) );
    }

    /**
     * @param $type
     * @param $id
     * @param $message
     * @throws Exception
     */
    public function ajaxSaveWall($type ,$id, $message, $photoId = 0, $notificationCmd = '', $objectName = '')
    {
        $json = array();
        $my = CFactory::getUser();
        $filter = JFilterInput::getInstance();
        $message = $filter->clean($message, 'string');
        $id = $filter->clean($id, 'int');
        $type = $filter->clean($type, 'string');
        $photoId = $filter->clean($photoId, 'int');
        $notificationCommand = $filter->clean($notificationCmd,'','STRING');
        $objectName = $filter->clean($objectName,'','STRING');
        $config = CFactory::getConfig();

        // If the content is false, the message might be empty.
        if (empty($message)) {
            $json['error'] = JText::_('COM_COMMUNITY_EMPTY_MESSAGE');
        } else {
            // @rule: Spam checks
            if ($config->get('antispam_akismet_walls')) {
                //CFactory::load( 'libraries' , 'spamfilter' );

                $filter = CSpamFilter::getFilter();
                $filter->setAuthor($my->getDisplayName());
                $filter->setMessage($message);
                $filter->setEmail($my->email);
                $filter->setType('message');
                $filter->setIP(CFactory::getClientIP());

                if ($filter->isSpam()) {
                    $json['error'] = JText::_('COM_COMMUNITY_WALLS_MARKED_SPAM');
                    die( json_encode($json) );
                }
            }

            // Save the wall content
            $wall = CWallLibrary::saveWall($id, $message, $type, $my,
                COwnerHelper::isCommunityAdmin(), 'system,thirdparty','wall/content', 0, $photoId);

            //here we should add to activity table if needed

            //notification to all the commentors or email
            $wallModel = CFactory::getModel('wall');
            $users = $wallModel->getAllPostUsers($type, $id, $my->id);

            //we will get a set of info from the table before sending the information to notification
            $db = JFactory::getDbo();
            $query = "SELECT * FROM ".$db->quoteName('#__community_thirdparty_wall_options')." WHERE "
                .$db->quoteName('name').'='.$db->quote($type);
            $db->setQuery($query);
            $wallOptions = $db->loadObject();

            if ($wallOptions) {
                // send notification to article creator (aticle comment plugin)
                if ($wallOptions->notification_cmd == 'article_comment') {
                    $article = JTable::getInstance('content');
                    $article->load($id);

                    if (!in_array($article->get("created_by"), $users)) {
                        array_push($users, $article->get("created_by"));
                    }
                }

                $params			= new CParameter( '' );
                $params->set('url' , 'index.php?option=com_community&view=groups&task=viewgroup&groupid=');
                $params->set('user' , $my->getDisplayName() );
                $params->set('user_url' , 'index.php?option=com_community&view=profile&userid='.$my->id );

                //check if there is any links in object name url
                $wallParams = new CParameter($wallOptions->params);
                $wallLink = '';
                if ($wallParams->get('object_url')) {
                    $joomlaConfig = JFactory::getConfig();
                    $mode = $joomlaConfig->get('force_ssl', 0) == 2 ? 1 : (-1);

                    if ($wallParams->get('object_title')) $wallLink = '<a href="'.JRoute::_($wallParams->get('object_url'), false, $mode).'">'.$wallParams->get('object_title').'</a>';
                    else $wallLink = '<a href="'.JRoute::_($wallParams->get('object_url'), false, $mode).'">'.$wallOptions->object_name.'</a>';
                } else {
                    //there is no link here
                    if ($wallParams->get('object_title')) $wallLink = $wallParams->get('object_title');
                    else $wallLink = $wallOptions->object_name;
                }
                
                $emailSubject = '';
                if ($wallParams->get('object_title')) $emailSubject = JText::sprintf('COM_COMMUNITY_THIRDPARTY_WALL_COMMENT_EMAIL_SUBJECT', $wallParams->get('object_title'));
                else $emailSubject = JText::sprintf('COM_COMMUNITY_THIRDPARTY_WALL_COMMENT_EMAIL_SUBJECT', $wallOptions->object_name);

                $notificationName = JText::sprintf('COM_COMMUNITY_THIRDPARTY_WALL_COMMENT_NOTIFICATION_TITLE', $wallLink);
                
                $notificationIds = CNotificationLibrary::add($wallOptions->notification_cmd, $my->id, $users, $emailSubject, JText::sprintf('COM_COMMUNITY_THIRDPARTY_WALL_COMMENT_EMAIL_BODY', $wallLink),'',$params, true, '', $notificationName);

                $actTitle = JText::sprintf('COM_COMMUNITY_THIRDPARTY_WALL_COMMENT_ACTIVITY_TITLE', $wallLink);

                //create an activty stream
                $act          = new stdClass();
                $act->cmd 	  = 'example.task';
                $act->actor   = $my->id;
                $act->target  = 0;
                $act->title   = $actTitle;
                $act->app 	  = $type;
                $act->access  = 10; // 10 = Public;
                $act->cid     = 0;

                CFactory::load('libraries', 'activities');
                $act->comment_type  = $type;
                $act->comment_id    = CActivities::COMMENT_SELF;

                $streamData = CActivityStream::add($act);

                if ($notificationIds) {
                    $wallTable = JTable::getInstance('Wall', 'CTable');
                    $wallTable->load($wall->id);

                    $wallParam = new CParameter($wallTable->params);
                    $wallParam->set('notificationId', implode('-', $notificationIds));
                    $wallParam->set('activityId', $streamData->id);

                    $wallTable->params = $wallParam->toString();

                    $wallTable->store();
                }
            }

            //CFactory::load( 'helpers' , 'event' );

            /*
            $actor = $my->id;
            $target = 0;
            $content = $message;
            $cid = $id;
            $app = $type;
            $act = $handler->getActivity($type, $actor, $target, $content, $cid, $app);
            $act->eventid = $event->id;

            $params = new CParameter('');
            $params->set(
                'url',
                ''
            );
            $params->set('action', '');
            $params->set('wallid', $wall->id);


            CActivityStream::add($act, $params->toString());

            CUserPoints::assignPoint($type);
            */

            $json['html'] = $wall->content;
        }

        $this->cacheClean(array(COMMUNITY_CACHE_TAG_EVENTS, COMMUNITY_CACHE_TAG_ACTIVITIES));

        if ( !isset($json['error']) ) {
            $json['success'] = true;
        }

        die( json_encode($json) );
    }

    public function editthirdpartyWall(){

    }

    public function ajaxLogin($uri = FALSE)
    {
        $my = CFactory::getUser();
        $config = CFactory::getConfig();
        $document = JFactory::getDocument();
        $usersConfig = JComponentHelper::getParams('com_users');
        $tmpl = new CTemplate();

        // check if user is already logged-in
        if ($my->id != 0) {
            $json = array();
            die( json_encode($json) );
        }

        // facebook login button
        $fbHtml = '';
        if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {
            $facebook = new CFacebook();
            $fbHtml = $facebook->getLoginHTML();
        }

        $linkedinHtml = '';

        /* LinkedIn login */
        if ($config->get('linkedinclientid') && $config->get('linkedinsecret') && !$config->get('usejfbc')) {
            $linkedin = new CLinkedin();
            $linkedinHtml = $linkedin->getLoginHTML();
        }

        $twitterHtml = '';

        /* Twitter login */
        if ($config->get('twitterconnectkey') && $config->get('twitterconnectsecret') && !$config->get('usejfbc')) {
            $twitter = new CTwitter();
            $twitterHtml = $twitter->getLoginHTML();
        }

        $googleHtml = '';

        /* Google login */
        if ($config->get('googleclientid') && !$config->get('usejfbc')) {
            $google = new CGoogle();
            $googleHtml = $google->getLoginHTML();

            $document->addCustomTag('<script src="https://apis.google.com/js/api:client.js"></script>');
        }

        if ($config->get('usejfbc')) {
            if (class_exists('JFBCFactory')) {
               $providers = JFBCFactory::getAllProviders();
               $fbHtml = '';
               foreach($providers as $p){
                    $fbHtml .= $p->loginButton();
               }
            }
        }

        // set redirect url
        if (!$uri) {
            $uri = CFactory::getLastURI();
        }
        $uri = base64_encode($uri);

        $tmpl->set('linkedinHtml', $linkedinHtml );
        $tmpl->set('twitterHtml', $twitterHtml );
        $tmpl->set('googleHtml', $googleHtml );
        $tmpl->set('fbHtml', $fbHtml );
        $tmpl->set('useractivation', $usersConfig->get('useractivation') );
        $tmpl->set('allowUserRegister', $usersConfig->get('allowUserRegistration') );
        $tmpl->set('inviteOnlyRegister', $config->get('invite_only_request'));
        $tmpl->set('useractivation', $usersConfig->get('useractivation') );
        $tmpl->set('return', $uri );
        $html = $tmpl->fetch('ajax.login');

        $json = array(
            'title'   => '&nbsp;',
            'html'    => $html,
            'noLogin' => true
        );

        die( json_encode($json) );
    }

    public function ajaxShowReactedUsers( $element = '', $uid = 0, $reactId = 0) {
        $json = array();
        $json['title'] = JText::_('COM_COMMUNITY_REACTION_USERS');

        $uid = (int) $uid;
        $reactId = (int) $reactId;
        $filter = JFilterInput::getInstance( array($element), array($uid), $reactId);
        $element = $filter->clean($element, 'string');

        $json['html'] = $this->_getReactedUsers( $element, $uid, $reactId);

        die(json_encode($json));
    }

    protected function _getReactedUsers( $element = '', $uid = 0, $reactId = 0 ) {
        $db = JFactory::getDbo();
        $sql = "SELECT `reaction_ids`, `like`
                FROM `#__community_likes`
                WHERE `reaction_ids` != ''
                AND `like` != ''
                AND `uid` = $uid
                AND `element` = " . $db->quote($element);

        try {
            $data = $db->setQuery($sql)->loadObject();
            if ($data) {
                $enabledUsers = $this->_getEnabledUsers($data->like);
                $like = new CLike;
                $reactions = $like->getReactions($data, $enabledUsers);

                $total = count($enabledUsers);
                $all = new stdClass;
                $all->reaction_id = 0;
                $all->userids = $enabledUsers;
                $all->count = $total;

                array_unshift( $reactions, $all );

                $displayData = array();
                $displayData['reactions'] = $reactions;
                $displayData['total'] = $total;
                $displayData['reactId'] = $reactId;
                $displayData['uid'] = $uid;
                $displayData['element'] = $element;

                $tmpl = new CTemplate();
                $tmpl->set('displayData', $displayData);
                $html = $tmpl->fetch('ajax.reacted.users');
            } else {
                $html = 'Empty!';
            }
            
        } catch ( Exception $e) {
            $html = 'Error when get reacted users';
        }

        return $html;
    }

    protected function _getEnabledUsers($userids) {
        $db = JFactory::getDbo();
        $sql = "SELECT id FROM `#__users`
                WHERE id in ( $userids )
                AND block = 0";
        
        $ids = $db->setQuery($sql)->loadColumn();
        return $ids;
    }

    public function ajaxGetUsersByReaction($element = '', $uid = 0, $reactId = 0) {
        $my = CFactory::getUser();
        if (!$my->id) {
            die('error');
        }

        $json = array();
        $json['title'] = JText::_('COM_COMMUNITY_REACTION_USERS');

        $uid = (int) $uid;
        $reactId = (int) $reactId;
        $filter = JFilterInput::getInstance( $element, $uid, $reactId);
        $element = $filter->clean($element, 'string');

        $db = JFactory::getDbo();
        $sql = "SELECT `reaction_ids`, `like`
                FROM `#__community_likes`
                WHERE `reaction_ids` != ''
                AND `like` != ''
                AND `uid` = $uid
                AND `element` = " . $db->quote($element);

        try {
            $data = $db->setQuery($sql)->loadObject();
            if ($data) {
                $enabledUsers = $this->_getEnabledUsers($data->like);
                $like = new CLike;
                $reactions = $like->getReactions($data, $enabledUsers);

                $total = count($enabledUsers);
                $all = new stdClass;
                $all->reaction_id = 0;
                $all->userids = $enabledUsers;
                $all->count = $total;

                array_unshift( $reactions, $all );

                $active = array_filter($reactions, function($i) use ($reactId) {
                    return $i->reaction_id == $reactId;
                });

                $active = array_shift($active);
                $users = array();
                foreach ($active->userids as $id) {
                    $users[] = CFactory::getUser($id);
                }

                $tmpl = new CTemplate();
                $tmpl->set('users', $users);
                $json['html'] = $tmpl->fetch('ajax.stream.showothers');
            } else {
                $html = 'Empty!';
            }
            
        } catch ( Exception $e) {
            $json['html'] = 'Error when get reacted users';
        }

        die(json_encode($json));
    }

    public function ajaxRate($type = null, $cid = null, $rating = null)
    {
        $my = CFactory::getUser();
        
        if (!$my->id) {
            die('error');
        }

        $filter = JFilterInput::getInstance();
        $type = $filter->clean($type, 'string');
        $cid = $filter->clean($cid, 'int');
        $userid = $filter->clean($userid, 'int');
        $rating = $filter->clean($raing, 'int');
        
        $json = array();

        if (!type || !$cid || !rating) {
            $json = array('error' => 'like error');
        } else {
            $ratingTable = JTable::getInstance('Rating', 'CTable');
            
            if ($ratingTable->isRated($type, $cid, $my->id)) {
               $ratingTable->ratingDelete($type, $cid, $my->id);
            }

            $ratingTable->type = $type;
            $ratingTable->cid = $cid;
            $ratingTable->userid = $my->id;
            $ratingTable->rating = $rating;

            $ratingTable->store();

            $json['success'] = true;
            $json['ratingCount'] = $ratingTable->getUserRatingCount($type, $cid);
            $json['ratingResult'] = $ratingTable->getRatingResult($type, $cid);
        }

        die(json_encode($json));
    }
}
