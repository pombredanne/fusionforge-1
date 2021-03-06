<?php
/**
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */


$ath->header(array ('title'=>_('Modify').': '.$ah->getID(). ' - ' . $ah->getSummary(),'atid'=>$ath->getID()));

?>
	<h3>[#<?php echo $ah->getID(); ?>] <?php echo $ah->getSummary(); ?></h3>

	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;atid=<?php echo $ath->getID(); ?>" METHOD="post" enctype="multipart/form-data">
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
	<input type="hidden" name="func" value="postmod" />
	<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>" />

	<table width="80%">
<?php
if (session_loggedin()) {
?>
		<tr>
			<td><?php
				if ($ah->isMonitoring()) {
					$img="xmail16w.png";
					$key="stop_monitoring";
				} else {
					$img="mail16w.png";
					$key="monitor";
				}
				echo '
				<a href="index.php?group_id='.$group_id.'&amp;artifact_id='.$ah->getID().'&amp;atid='.$ath->getID().'&amp;func=monitor"><strong>'.
					html_image('ic/'.$img.'','20','20',array()).' '.$key.'</strong></a>';
				?>&nbsp;<a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=monitor'); ?>')"><strong>(?)</strong></a>
			</td>
			<td><?php
				if ($group->usesPM()) {
					echo '
				<a href="'.getStringFromServer('PHP_SELF').'?func=taskmgr&amp;group_id='.$group_id.'&amp;atid='.$atid.'&amp;aid='.$aid.'">'.
					html_image('ic/taskman20w.png','20','20',array()).'<strong>'._('Build Task Relation').'</strong></a>';
				}
				?>
			</td>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
			</td>
		</tr>
	</table>
<?php } ?>
<table border="0" width="80%">

	<tr>
		<td><strong><?php echo _('Submitted by') ?>:</strong><br />
			<?php
			echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				$submittedBy = $ah->getSubmittedBy();
				?>
				(<tt><?php echo util_make_link ($submittedUnixName,$submittedBy,$submittedUnixName); ?></tt>)
			<?php } ?>
		</td>
		<td><strong><?php echo _('Date Submitted') ?>:</strong><br />
		<?php
		echo date(_('Y-m-d H:i'), $ah->getOpenDate() );

		$close_date = $ah->getCloseDate();
		if ($ah->getStatusID()==2 && $close_date > 1) {
			echo '<br /><strong>'._('Date Closed').':</strong><br />'
				 .date(_('Y-m-d H:i'), $close_date);
		}
		?>
		</td>
	</tr>

    <?php
        $ath->renderExtraFields($ah->getExtraFieldData(),true);
    ?>

	<tr>
		<td><strong><?php echo _('Assigned to')?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=assignee'); ?>')"><strong>(?)</strong></a></strong><br />
            <?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</td>
		<td>
		<strong><?php echo _('Priority') ?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=priority'); ?>')"><strong>(?)</strong></a></strong><br />
		<?php
		/*
			Priority of this request
		*/
		echo $ah->getPriority();
		?>
		</td>
	</tr>

	<tr>
		<td>
		<?php if (!$ath->usesCustomStatuses()) { ?>
		<strong><?php echo _('State') ?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=status'); ?>')"><strong>(?)</strong></a></strong><br />
		<?php

		echo $ath->statusBox ('status_id', $ah->getStatusID() );
		}
		?>
		</td>
		<td>
		</td>
	</tr>

	<tr>
		<td colspan="2"><strong><?php echo _('Summary')?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=summary'); ?>')"><strong>(?)</strong></a></strong><br />
			<?php echo $ah->getSummary(); ?>
		</td>
	</tr>

	<tr><td colspan="2">
		<br />
		<?php echo $ah->showDetails(); ?>
	</td></tr>
</table>
<script type="text/javascript" src="<?php echo util_make_url('/tabber/tabber.js') ?>"></script>
<div id="tabber" class="tabber">
<div class="tabbertab" title="<?php echo _('Followups');?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<br /><strong><?php echo _('OR Attach A Comment') ?>: <?php echo notepad_button('document.forms[1].details') ?> <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=comment'); ?>')"><strong>(?)</strong></a></strong><br />
		<textarea name="details" rows="7" cols="60"></textarea></p>
		<p>
		<h3><?php echo _('Followup') ?>:</h3>
		<?php
			echo $ah->showMessages(); 
		?>
	</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Attachments'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<?php echo _('Attach Files') ?><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<p>
		<h3><?php echo _('Attached Files') ?>:</h3>
		<?php
		//
		//  print a list of files attached to this Artifact
		//
		$file_list =& $ah->getFiles();

		$count=count($file_list);

		$title_arr=array();
		$title_arr[]=_('Delete');
		$title_arr[]=_('Name');
		$title_arr[]=_('Description');
		$title_arr[]=_('Download');
		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		if ($count > 0) {

			for ($i=0; $i<$count; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td><input type="CHECKBOX" name="delete_file[]" value="'. $file_list[$i]->getID() .'">'._('Delete').' </td>
				<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
				<td>'.  htmlspecialchars($file_list[$i]->getDescription()) .'</td>
				<td>'.util_make_link ('/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'.$file_list[$i]->getName(),_('Download')).'</td>
				</tr>';
			}

		} else {
			echo '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle(0).'><td colspan=3>'._('No Files Currently Attached').'</td></tr>';
		}
		echo $GLOBALS['HTML']->listTableBottom();
		?>
	</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Commits'); ?>">
<table border="0" width="80%">
	<?php
		$hookParams['artifact_id']=$aid;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Changes'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<h3><?php echo _('Change Log') ?>:</h3>
		<?php 
			echo $ah->showHistory(); 
		?>
	</td></tr>
</table>
</div>
</div>
		</form>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
