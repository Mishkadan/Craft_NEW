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

/**
 * @since 3.2 we'll use CActivity for each activity object
 * @todo in sprint 3 we must move everything into CActivity while passing into template layout
 */
/* Temporary fix for sprint 2 */
if ($this->act instanceof CTableActivity) {
    /* If this's CTableActivity then we use getProperties() */
    $activity = new CActivity($this->act->getProperties());
} else {
    /* If it's standard object than we just passing it */
    $activity = new CActivity($this->act);
}

$mainframe	= JFactory::getApplication();
$jinput 	= $mainframe->input;
$isSingleAct= ($jinput->get->get('actid',0) > 0) ? true : false;

$address = $activity->getLocation();
$user = $activity->getActor();
$target = $activity->getTarget();
$headMetas = $activity->getParams('headMetas');
/* We do convert into JRegistry to make it easier to use */
if ($headMetas) {
    $headMetaParams = new JRegistry($headMetas);
}
if ($act->app == 'profile.avatar.upload') {
    $this->load('activities.profile.avatar.upload');
    return;
}


if (!empty($act->params)) {
    if (!is_object($act->params)) {
        $act->params = new JRegistry($act->params);
    }

    $mood = $act->params->get('mood', null);

    // get status background
    $bgid = $act->params->get('bgid', null);
    if ($bgid) {
        $backgroundsModel = CFactory::getModel('Backgrounds');
        $background = $backgroundsModel->getBackground($bgid);
    } else {
        $background = null;
    }
} else {
    $mood = null;
    $background = null;
}
$title = $activity->get('title');
?>

<div class="joms-stream__header">
    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
        <?php if($user->id > 0) :?>
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
        </a>
        <?php endif; ?>
    </div>
    <div class="joms-stream__meta">

        <?php if($user->id > 0) :?>
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>" data-joms-username class="joms-stream__user"><?php echo $user->getDisplayName(false, true); ?></a>
        <?php else :
            echo $user->getDisplayName();
        endif;

        if ($activity->get('eventid')) {
            $event = $this->event;
            $group = JTable::getInstance('Group', 'CTable');
            $group->load($event->contentid);
            $actLink = CRoute::_(CUrlHelper::eventLink($event->id, false)."&actid=".$activity->id);
            ?>
            <span class="joms-stream__reference">
            <?php if(JFactory::getApplication()->input->get('view') == 'groups'){ // if this is within group, no need to display the second part (group)
                $actLink = CRoute::_(CUrlHelper::eventLink($event->id)."&actid=".$activity->id);
                ?>
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::eventLink($event->id); ?>"><?php echo $this->escape($event->title); ?></a>
            <?php } else { ?>
                <?php if ( $group->id > 0 ) {
                    $actLink = CRoute::_(CUrlHelper::groupLink($group->id, false)."&actid=".$activity->id);
                    ?>
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::groupLink($group->id); ?>"><?php echo $this->escape($group->name); ?></a>
                <?php } ?>
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::eventLink($event->id); ?>"><?php echo $this->escape($event->title); ?></a>
            <?php } ?>
            </span>
            <?php
        } else if ($activity->get('groupid')) {
            $group = $this->group;
            $actLink = CRoute::_(CUrlHelper::groupLink($group->id, false)."&actid=".$activity->id);
            ?>
            <span class="joms-stream__reference">
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::groupLink($group->id); ?>"><?php echo $group->name; ?></a>
            </span>
        <?php } else if ($activity->get('pageid')) {
            $page = $this->page;
            $actLink = CRoute::_(CUrlHelper::pageLink($page->id, false)."&actid=".$activity->id);
            ?>
            <span class="joms-stream__reference">
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::pageLink($page->id); ?>"><?php echo $page->name; ?></a>
            </span>
        <!-- Target is user profile -->
        <?php } else if ( ( $activity->get('app') == 'profile' ) && ( $activity->get('target') != 0 ) && $activity->get('target') != $user->id ) { ?>
            <span class="joms-stream__reference">
                <span class="joms-stream__arrow-right">▶</span> <a href="<?php echo CUrlHelper::userLink($activity->target); ?>"><?php echo CFactory::getUser($activity->get('target'))->getDisplayName(false, true); ?></a>
            </span>
        <?php } ?>

        <?php if(isset($actLink)) {?>
        <a href="<?php echo $actLink;?>">
        <?php } ?>
        <span class="joms-stream__time">
            <small><?php echo $activity->getCreateTimeFormatted(); ?></small>
            <?php if ( strpos($activity->get('app'), 'events') === false  && strpos($activity->get('app'), 'groups') === false ) { ?>
            <?php ( $activity->get('groupid') || ($activity->get('app') == 'profile') && $activity->get('target') != $activity->get('actor') ) ? '' : $this->load('/privacy/show'); ?>
            <?php } ?>

        </span>
            <?php if(isset($actLink)) { ?>
        </a>
            <?php } ?>
    </div>

    <?php

        $my = CFactory::getUser();
        $this->load('activities.stream.options');

    ?>

</div>
<div class="joms-stream__body">

    <div data-type="stream-editor" class="cStream-Respond" style="display:none">
        <textarea class="joms-textarea" style="margin-bottom:0"><?php echo $activity->get('title'); ?></textarea>
        <div style="text-align:right;">
            <button class="joms-button--neutral joms-button--small" onclick="joms.view.stream.cancel('<?php echo $activity->get('id'); ?>');"><?php echo JText::_('COM_COMMUNITY_CANCEL'); ?></button>&nbsp;
            <button class="joms-button--primary joms-button--small" onclick="joms.view.stream.save('<?php echo $activity->get('id'); ?>', this);"><?php echo JText::_('COM_COMMUNITY_SAVE'); ?></button>
        </div>
    </div>







    <div class="joms-attachment-list">
        <ul class="lenta_attachments">
            <?php
            $modelFile = CFactory::getModel('files');
            $files = $modelFile->getFileList('act', $activity->get('id'), 0, 100);

            foreach ($files as $file) {

                $filename = $file->name;
                $filepath = $file->filepath;
                $fileext  = end(explode( '.', $filepath));
                $fileDownloadLink = CRoute::_('index.php?option=com_community&view=files&task=downloadfile&type=activity&id='.$file->id);
                //if ( $fileext !== false ) {
                //$filename .= substr($filepath, $fileext);
                // }
                $fileLib = new CFilesLibrary();
                $fileLib->convertToMb($file);

                switch ($file->type) {

                    case 'document' :
                        echo '<li class="def">';
                        if($fileext == 'pdf') {
                            echo '<iframe src="https://docs.google.com/viewer?url=' . JUri::root() . $filepath . '&embedded=true" style="width:100%; height:400px;border=0;" title="' . $filename . '" loading="lazy"></iframe>';
                        } else
                            echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src=' . JUri::root() . $filepath . '" style="width:100%; height:400px;border:0;" title="'.$filename.'" loading="lazy"></iframe>';
                        echo '</li>';
                        break;

                    case 'audio' : ?>
                        <li class="audiofile def">
                            <div class="audiplayer">
                                <svg viewBox="0 0 24 22" class="joms-icon">
                                    <use xlink:href="<?php echo JUri::getInstance() ?>#lenta-music"></use>
                                </svg>
                                <span><b><?php echo $filename; ?></b></span>
                            </div>
                            <audio title="<?php echo $filename; ?>" controls style="width: 100%">
                                <source src="<?php echo JUri::root(). $filepath; ?>">
                                Your browser does not support the audio element.
                            </audio>
                        </li>
                        <? break;

                    case 'multimedia' :

                        $storage    = CStorage::getStorage($file->storage);
                        $path = $storage->getURI($file->filepath) ? : $file->path;
                        $videoParams = new JRegistry($file->params);
                        $videoWidth = $videoParams->get('width', 16);
                        $videoHeight = $videoParams->get('height', 9);
                        ?>
                        <li>
                            <div class="joms-media--video joms-js--video"
                                 data-type="<?= $file->type ==='multimedia' ? 'file' : ''; ?>"
                                 data-id="<?php echo $file->id; ?>"
                                 data-path="<?php echo htmlspecialchars($path) ?>"
                                 data-width="<?php echo $videoWidth ?>"
                                 data-height="<?php echo $videoHeight ?>">

                                <div class="joms-media__thumbnail <?php echo ($config->getInt('enablevideosthumbbigsize') ? 'fullwidth-thumb-video' : ''); ?>">
                                    <img src="<?php echo $file->thumb; ?>" alt="<?php echo $file->name; ?>">
                                    <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-<?php echo $act->id ?>">
                                        <div class="mejs-overlay-button"></div>
                                    </a>
                                </div>
                            </div>
                        </li>

                        <? break;

                    case 'images' : ?>
                        <li>
                            <a class ="lentamedia"
                               href="javascript:" onclick="joms.api.photoOpen('<?php echo $file->type; ?>', '<?php echo $file->id; ?>');" >
                                <img src="<?php echo JUri::root(). $filepath; ?>" alt="<?php echo $this->escape($filename); ?>" loading="lazy">
                            </a>
                        </li>
                        <? break;

                    default: ?>
                        <li class="def">
                            <svg viewBox="0 0 16 16" class="joms-icon">
                                <use xlink:href="<?php echo JUri::getInstance() ?>#joms-icon-file-zip"></use>
                            </svg>
                            <a class="joms-filename" href="<?php echo $fileDownloadLink; ?>" title="<?php echo $filename; ?>" onclick="joms.api.fileUpdateHit(<?php echo $file->id; ?>);">
                                <?php echo $filename.'.'.$fileext; ?>
                            </a>
                            <span class="joms-filesize"><?php //echo $file->filesize; ?></span>
                        </li>
                    <?  }

            } ?>
        </ul>

    </div>










    <p data-type="stream-content">
        <?php if ($background && $config->get('enablebackground')) { ?>
            <div class="colorful-status__container" style="background-image: url('<?php echo $background->image ?>');">
                <div class="colorful-status__inner" style="<?php echo (($background->textcolor) ? 'color:#' . $background->textcolor . ';' : '') ?>">
                    <?php echo CActivities::format($activity->get('title')); ?>
                </div>
            </div>
        <?php } else { ?>
            <?php $title =  empty($title) ? ltrim(CActivities::format($activity->get('title'), $mood),' -') : CActivities::format($activity->get('title'), $mood);

            echo CActivities::shorten($title, $activity->get('id'), 0, $config->getInt('streamcontentlength'));

            if ($address) { ?>
            <span class="joms-status-location"><?php if(!empty($title)){?>- <?php }?><?php echo JText::_('COM_COMMUNITY_AT'); ?>
                <a href="javascript:" onclick="joms.api.locationView('<?php echo $activity->get('id'); ?>');"><?php echo $address ?></a>
            </span>
            <?php } ?>
        <?php } ?>
    </p>

    <!-- Fetched data -->
    <?php if ($headMetas) { ?>

        <?php if ($headMetaParams->get('title') || $headMetaParams->get('description')) { ?>

            <?php

                if($headMetaParams->get('type') == 'video'){
                    if($headMetaParams->get('video_provider') == 'break'){
                        $href= 'href="'.$headMetaParams->get('link').'" target="_blank"';
                    }else{
                        $href= 'href=\'javascript:jax.call("community" , "videos,ajaxShowStreamVideoWindow", "'.$activity->id.'");\'';
                    }

                }else{
                    $href = $headMetaParams->get('link') ? $headMetaParams->get('link') : '#';

                    $isNewTab = CFactory::getConfig()->get('newtab',false);
                    if ($isNewTab) $href = "href='".$href."' target='_blank'";
                    else $href = "href='".$href."'";
                }
            ?>
            <?php
            if($config->get('enable_embedly') && $headMetaParams->get('link')){ ?>
                <a href="<?php echo $headMetaParams->get('link'); ?>" class="embedly-card" data-card-controls="0" data-card-recommend="0" data-card-theme="<?php echo $config->get('enable_embedly_card_template'); ?>" data-card-key="<?php echo $config->get('embedly_card_apikey'); ?>" data-card-align="<?php echo $config->get('enable_embedly_card_position') ?>"><?php echo JText::_('COM_COMMUNITY_EMBEDLY_LOADING');?></a>
            <?php }elseif($headMetaParams->get('type') == 'video'){ ?>
            <div class="joms-media--video joms-js--video"
                    data-type="<?php echo $headMetaParams->get('video_provider'); ?>"
                    data-id="<?php echo $headMetaParams->get('video_id'); ?>"
                    data-path="<?php echo ( $headMetaParams->get('video_provider') === 'file' ? JURI::root(true) . '/' : '' ) . $headMetaParams->get('link') ?>" >

                <div class="joms-media__thumbnail">
                    <img src="<?php echo $headMetaParams->get('image'); ?>; ?>" alt="<?php echo $headMetaParams->get('title'); ?>" >
                    <a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-<?php echo $activity->id; ?>">
                        <div class="mejs-overlay-button"></div>
                    </a>
                </div>
                <div class="joms-media__body">
                    <h4 class="joms-media__title">
                        <?php echo JHTML::_('string.truncate', $headMetaParams->get('title'), 50, true, false); ?>
                    </h4>
                    <p class="joms-media__desc">
                        <?php echo JHTML::_('string.truncate', $headMetaParams->get('description'), $config->getInt('streamcontentlength'), true, false); ?>
                    </p>
                </div>
            </div>
            <?php }else{ ?>
            <div class="joms-media">
                <div style="position:relative;">
                    <div class="row-fluid">
                        <?php if ($headMetaParams->get('image')) { ?>
                        <div class="span4">
                            <a <?php echo $href; ?> onclick="joms.api.photoZoom('<?php echo $headMetaParams->get('image'); ?>');">
                                <img class="joms-stream-thumb" src="<?php echo $headMetaParams->get('image'); ?>" alt="<?php echo $headMetaParams->get('title'); ?>" />
                            </a>
                        </div>
                        <?php } ?>
                        <div class="span<?php echo $headMetaParams->get('image') ? '8' : '12' ?>">
                            <article class="joms-stream-fetch-content" style="margin-left:0; padding-top:0">
                                <a <?php echo $href; ?>>
                                    <span class="joms-stream-fetch-title"><?php echo $headMetaParams->get('title'); ?></span>
                                </a>
                                    <span class="joms-stream-fetch-desc"><?php echo CStringHelper::trim_words($headMetaParams->get('description')); ?></span>
                                    <?php if ($headMetaParams->get('link')) { ?>
                                        <cite><?php echo preg_replace('#^https?://#', '', $headMetaParams->get('link')); ?></cite>
                                    <?php } ?>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        <?php }
    }
    ?>
</div>

<?php $this->load('stream/footer'); ?>
