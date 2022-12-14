<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined('_JEXEC') or die();

$config	= CFactory::getConfig();
?>


<?php if ($pages) { ?>

<div class="joms-gap"></div>

<ul class="joms-list--card">
    <?php

    $my = CFactory::getUser();
    $pageModel = CFactory::getModel('pages');

    for ( $i = 0; $i < count( $pages ); $i++ ) {
        $page =& $pages[$i];
        
        $reviews = JTable::getInstance('Rating', 'CTable');
        $reviewsCount = $reviews->getUserRatingCount('pages', $page->id);
        $ratingValue = $reviews->getRatingResult('pages', $page->id);

        $isMine = $my->id == $page->ownerid;
        $isAdmin = $pageModel->isAdmin($my->id, $page->id);
        $isMember = $pageModel->isMember($my->id, $page->id);
        $isBanned = $page->isBanned($my->id);
        $creator = CFactory::getUser($page->ownerid);

        // Check if "Feature this" button should be added or not.
        $addFeaturedButton = false;
        $isFeatured = false;
        if ( $isCommunityAdmin && $showFeatured ) {
            $addFeaturedButton = true;
            if ( in_array($page->id, $featuredList) ) {
                $isFeatured = true;
            }
        }

        //all the information needed to fill up the summary
        $params = $page->getParams();
        
        $eventsModel = CFactory::getModel('Events');
        $totalEvents = $eventsModel->getTotalPageEvents($page->id);
        $showEvents = ($config->get('page_events') && $config->get('enableevents') && $params->get('eventpermission',
            1) >= 1);

        $videoModel = CFactory::getModel('videos');
        $showVideo = ($params->get('videopermission') != -1) && $config->get('enablevideos') && $config->get('pagevideos');
        if($showVideo) {
            $videoModel->getPageVideos($page->id, '', 0, '');
            $totalVideos = $videoModel->total ? $videoModel->total : 0;
        }

        $showPhoto = ($params->get('photopermission') != -1) && $config->get('enablephotos') && $config->get('pagephotos');
        $photosModel = CFactory::getModel('photos');
        $albums = $photosModel->getPageAlbums($page->id, false, false);
        $totalPhotos = 0;
        foreach ($albums as $album) {
            $albumParams = new CParameter($album->params);
            $totalPhotos = $totalPhotos + $albumParams->get('count');
        }
        
        $pollModel = CFactory::getModel('polls');
        $polls = $pollModel->getAllPolls(null, null, null, null, false, true, null, null, $page->id);
        $totalPolls = 0; 
        foreach ($polls as $poll) {
            $totalPolls++;
        }
        $showPolls = ($config->get('page_polls') && $config->get('enablepolls') && $params->get('pollspermission',
            1) >= 1);

        // Check if "Invite friends" and "Settings" buttons should be added or not.
        $canInvite = false;
        $canEdit = false;

        if (($isMember && !$isBanned) || $isCommunityAdmin) {
            $canInvite = true;
        }

        if ($isMine || $isAdmin || CFactory::getUser()->authorise('community.pageedit', 'com_community')) {
            $canEdit = true;
        }
    ?>

    <li class="joms-list__item <?php echo $page->approvals == COMMUNITY_PRIVATE_GROUP ? 'group-private' : 'group-public' ?>">
        <div class="joms-list__cover">
            <a href="<?php echo $page->getLink(); ?>">
                <?php  if (in_array($page->id, $featuredList)) { ?>
                <div class="joms-ribbon__wrapper">
                    <span class="joms-ribbon"><?php echo JText::_('COM_COMMUNITY_FEATURED'); ?></span>
                </div>
                <?php } ?>

                <div class="joms-list__cover-image" data-image="<?php echo $page->getCover(); ?>" style="background-image: url(<?php echo $page->getCover(); ?>);"></div>
            </a>
            <?php if ($addFeaturedButton || $canInvite || $canEdit) { ?>
            <div class="joms-focus__button--options--desktop">
                <a class="joms-button--options" data-ui-object="joms-dropdown-button" href="javascript:">
                    <svg class="joms-icon" viewBox="0 0 16 16">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-cog"></use>
                    </svg>
                </a>
                <ul class="joms-dropdown">
                    <?php if ($addFeaturedButton) { ?>
                        <?php if ($isFeatured) { ?>
                            <li><a href="javascript:" onclick="joms.api.pageRemoveFeatured('<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a></li>
                        <?php } else { ?>
                            <li><a href="javascript:" onclick="joms.api.pageAddFeatured('<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_PAGE_FEATURE'); ?></a></li>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($canInvite) { ?>
                        <li><a href="javascript:" onclick="joms.api.pageInvite('<?php echo $page->id; ?>');"><?php echo JText::_('COM_COMMUNITY_INVITE_FRIENDS'); ?></a></li>
                    <?php } ?>

                    <?php if ($canEdit) { ?>
                        <li><a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=edit&pageid=' . $page->id); ?>"><?php echo JText::_('COM_COMMUNITY_SETTINGS'); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </div>

        <div class="joms-list__content">
            <h4 class="joms-list__title">
                <a href="<?php echo $page->getLink(); ?>">
                    <?php echo $this->escape($page->name); ?>
                </a>
            </h4>
            <p><?php echo CStringHelper::ratingStar($ratingValue, 0, $page->id); ?></p>

          <? /***
            <ul class="joms-list--table">
                <?php if(($page->approvals == COMMUNITY_PRIVATE_PAGE && $isMember) || $page->approvals == COMMUNITY_PUBLIC_PAGE){ ?>
                    <li>
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-users"></use>
                        </svg>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=pages&task=viewmembers&pageid='.$page->id) ?>">
                        <?php echo JText::sprintf((CStringHelper::isPlural($page->membercount)) ? 'COM_COMMUNITY_PAGES_MEMBER_COUNT_MANY':'COM_COMMUNITY_PAGES_MEMBER_COUNT', $page->membercount);?>
                        </a>
                    </li>
                    
                    <?php if($showVideo){ ?>
                    <li>
                        <svg class="joms-icon" viewBox="0 0 16 16">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-film"></use>
                        </svg>
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&pageid=' . $page->id); ?>">
                            <?php echo ($totalVideos == 1) ? $totalVideos.' '.JText::_('COM_COMMUNITY_VIDEOS_COUNT') : $totalVideos.' '.JText::_('COM_COMMUNITY_VIDEOS_COUNT_MANY'); ?>
                        </a>
                    </li>
                    <?php } ?>

                    <?php if($showPhoto){ ?>
                        <li>
                            <svg class="joms-icon" viewBox="0 0 16 16">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-image"></use>
                            </svg>
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&pageid=' . $page->id); ?>">
                                <?php 
                                    echo ($totalPhotos == 1) ?
                                    $totalPhotos.' '.JText::_('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR') :
                                    $totalPhotos.' '.JText::_('COM_COMMUNITY_PHOTOS_COUNT'); 
                                ?>
                            </a>
                        </li>
                    <?php } ?>
                    
                    <?php if ($showEvents) { ?>
                        <li>
                            <svg class="joms-icon" viewBox="0 0 16 16">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-calendar"></use>
                            </svg>
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&pageid=' . $page->id); ?>">
                                <?php echo ($totalEvents == 1 || $totalEvents == 0)
                                    ? $totalEvents.' '.JText::_('COM_COMMUNITY_EVENTS_COUNT')
                                    : $totalEvents.' '.JText::_('COM_COMMUNITY_EVENTS_COUNT_MANY'); ?>
                            </a>
                        </li>
                    <?php } ?>

                    <?php if ($showPolls) { ?>
                        <li>
                            <svg class="joms-icon" viewBox="0 0 16 16">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-list"></use>
                            </svg>
                            <a href="<?php echo CRoute::_('index.php?option=com_community&view=polls&pageid=' . $page->id); ?>">
                                <?php echo ($totalPolls == 1 || $totalPolls == 0)
                                    ? $totalPolls.' '.JText::_('COM_COMMUNITY_POLLS_COUNT')
                                    : $totalPolls.' '.JText::_('COM_COMMUNITY_POLLS_COUNT_MANY'); ?>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
            ***/ ?>

        </div>
        <? /***
        <?php if($page->approvals == COMMUNITY_PRIVATE_PAGE) { ?>
            <span class="joms-list__permission">
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-lock"></use>
                </svg>
                <?php echo JText::_('COM_COMMUNITY_PAGES_PRIVATE'); ?>
            </span>
        <?php } else { ?>
            <span class="joms-list__permission">
                <svg class="joms-icon" viewBox="0 0 16 16">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-earth"></use>
                </svg>
                <?php echo JText::_('COM_COMMUNITY_PAGES_OPEN'); ?>
            </span>
        <?php } ?>

        <div class="joms-list__footer joms-padding">
            <div class="<?php echo CUserHelper::onlineIndicator($creator); ?>">
                <a class="joms-avatar" href="<?php echo CUrlHelper::userLink($creator->id);?>"><img src="<?php echo $creator->getAvatar();?>" alt="avatar" data-author="<?php echo $creator->id; ?>" ></a>
            </div>
            <div class="joms-block">
                <?php echo JText::_('COM_COMMUNITY_PAGES_CREATED_BY'); ?> <a href="<?php echo CUrlHelper::userLink($creator->id);?>"><?php echo $creator->getDisplayName(); ?></a>
            </div>
        </div>

        ***/ ?>
    </li>

    <?php } ?>
</ul>

<?php } else { ?>
    <div class="cEmpty cAlert"><?php echo JText::_('COM_COMMUNITY_PAGES_NOITEM'); ?></div>
<?php } ?>

<?php if (isset($pagination) && $pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
