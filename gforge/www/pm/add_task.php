<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once $gfwww.'include/note.php';

$related_artifact_id = getIntFromRequest('related_artifact_id');
$related_artifact_summary = getStringFromRequest('related_artifact_summary');

pm_header(array('title'=>_('Add a new Task'),'group_project_id'=>$group_project_id));
echo notepad_func();
?>

<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postaddtask" />
<input type="hidden" name="add_artifact_id[]" value="<?php echo $related_artifact_id; ?>" />

<table border="0" width="100%">

	<tr>
		<td>
		<strong><?php echo _('Category') ?></strong><br />
		<?php
		echo $pg->categoryBox('category_id'); 
		echo util_make_link ('/pm/admin/?group_id='.$group_id.'&amp;add_cat=1&amp;group_project_id='.$group_project_id,'('._('admin').')');
		?>
		</td>

		
		<input type="submit" value="<?php echo _('Submit') ?>" name="submit" />
		</td>
	</tr>

	<tr>
		<td>
			<strong><?php echo _('Percent Complete') ?>:</strong><br />
			<?php echo $pg->percentCompleteBox(); ?>
		</td>
		<td>
			<strong><?php echo _('Priority') ?>:</strong><br />
			<?php echo build_priority_select_box(); ?>
		</td>
	</tr>

  	<tr>
		<td colspan="2">
		<strong><?php echo _('Task Summary') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="summary" size="40" maxlength="65" value="<?php echo $related_artifact_summary; ?>" />
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo _('Task Details') ?>:</strong><?php echo notepad_button('document.forms[1].details') ?> <?php echo utils_requiredField(); ?><br />
		<textarea name="details" rows="5" cols="40" wrap="soft"></textarea></td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo _('Estimated Hours') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="hours" size="5" />
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo _('Start Date') ?>:</strong><br />
		<?php
		echo $pg->showMonthBox ('start_month',date('m', time()));
		echo $pg->showDayBox ('start_day',date('d', time()));
		echo $pg->showYearBox ('start_year',date('Y', time()));
		echo $pg->showHourBox ('start_hour',date('G', time()));
		echo $pg->showMinuteBox ('start_minute', date('i', 15*(time()%15)));
		?><br /><?php echo _('The system will modify your start/end dates if you attempt to create a start date earlier than the end date of any tasks you depend on.') ?>
			<br /><a href="calendar.php?group_id=<?php echo $group_id; ?>&amp;group_project_id=<?php echo $group_project_id; ?>" target="_blank"><?php echo _('View Calendar') ?></a>
		</td>

	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo _('End Date') ?>:</strong><br />
		<?php
		echo $pg->showMonthBox ('end_month',date('m', (time()+604800)));
		echo $pg->showDayBox ('end_day',date('d', (time()+604800)));
		echo $pg->showYearBox ('end_year',date('Y', (time()+604800)));
		echo $pg->showHourBox ('end_hour',date('G', (time()+604800)));
		echo $pg->showMinuteBox ('end_minute', date('i', 15*((time()+604800)%15)));
		?>
		</td>

	</tr>

	<tr>
		<td valign="top">
		<strong><?php echo _('Assigned to') ?>:</strong><br />
		<?php
		echo $pt->multipleAssignedBox();
		?>
		</td>
		<td valign="top">
		<strong><?php echo _('Dependent on task') ?>:</strong><br />
		<?php
		echo $pt->multipleDependBox();
		?><br />
		<?php echo _('Dependent note') ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<input type="submit" value="<?php echo _('Submit') ?>" name="submit" />
		</td>
	</tr>
	<input type="hidden" name="duration" value="0">
	<input type="hidden" name="parent_id" value="0">
<!--
will add duration and parent_id choices at some point
	<tr>
		<td>
		<strong><?php echo _('Estimated Hours') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="hours" size="5" />
		</td>

		<td>
		<input type="submit" value="<?php echo _('Submit') ?>" name="submit" />
		</td>
	</tr>
-->
</table>
</form>
<?php

pm_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
