<?php
/**
 * Kunena Component
 * @package         Kunena.Template.Crypsisb3
 * @subpackage      Layout.Widget
 *
 * @copyright       Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;
?>
<h3><?php echo $this->header; ?></h3>
<div class="well well-small"><div class="kcustom-top"><?php echo $this->subLayout('Widget/Module')->set('position', 'kunena_custom_top'); ?></div><?php echo $this->body; ?><div class="kcustom-bottom"><?php echo $this->subLayout('Widget/Module')->set('position', 'kunena_custom_bottom'); ?></div></div>
