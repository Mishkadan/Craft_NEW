<?php
/**
 * Kunena Component
 * @package         Kunena.Administrator.Template
 * @subpackage      Trash
 *
 * @copyright       Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('dropdown.init');
HTMLHelper::_('formbehavior.chosen', 'select');

$count = count($this->purgeitems);
?>

<div id="kunena" class="admin override">
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div id="j-sidebar-container" class="span2">
					<div id="sidebar">
						<div class="sidebar-nav">
							<?php include KPATH_ADMIN . '/template/j3/common/menu.php'; ?>
						</div>
					</div>
				</div>
				<div id="j-main-container" class="span10">
					<form action="<?php echo KunenaRoute::_('administrator/index.php?option=com_kunena&view=trash') ?>"
					      method="post" id="adminForm"
					      name="adminForm">
						<input type="hidden" name="task" value="purge"/>
						<input type="hidden" name="boxchecked" value="1"/>
						<input type="hidden" name="md5" value="<?php echo $this->md5Calculated ?>"/>
						<?php echo HTMLHelper::_('form.token'); ?>
						<fieldset>
							<legend><?php echo Text::_('COM_KUNENA_ITEMS_BEING_DELETED'); ?></legend>
							<div class="alert"><?php echo Text::sprintf('COM_KUNENA_WARNING_PERM_DELETE_ITEMS', $count); ?></div>
							<?php
							if ($count)
								:
								?>
								<table class="table table-striped">
									<?php foreach ($this->purgeitems as $item)
										:
										?>
										<tr>
											<td width="1%"><?php echo $this->escape($item->id); ?></td>
											<td><?php echo $this->escape($item->subject); ?></td>
										</tr>
									<?php endforeach; ?>
								</table>
							<?php endif; ?>
						</fieldset>
					</form>
				</div>
				<div class="pull-right small">
					<?php echo KunenaAdminVersion::getLongVersionHTML(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
