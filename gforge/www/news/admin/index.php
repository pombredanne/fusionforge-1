<?php
/**
 * GForge News Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('pre.php');
require_once('note.php');
require_once('news_admin_utils.php');
require_once('www/news/news_utils.php');
//common forum tools which are used during the creation/editing of news items
require_once('common/forum/Forum.class');

$group_id = getIntFromRequest('group_id');
$post_changes = getStringFromRequest('post_changes');
$approve = getStringFromRequest('approve');
$status = getIntFromRequest('status');
$summary = getStringFromRequest('summary');
$details = getStringFromRequest('details');

if ($group_id && $group_id != $sys_news_group && user_ismember($group_id,'A')) {
	$id = getIntFromRequest('id');
	$status = getIntFromRequest('status');
	$summary = getStringFromRequest('summary');
	$details = getStringFromRequest('details');

	/*

		Per-project admin pages.

		Shows their own news items so they can edit/update.

		If their news is on the homepage, and they edit, it is removed from
			sf.net homepage.

	*/
	if ($post_changes) {
		if ($approve) {
			/*
				Update the db so the item shows on the home page
			*/
			if ($status != 0 && $status != 4) {
				//may have tampered with HTML to get their item on the home page
				$status=0;
			}

			//foundry stuff - remove this news from the foundry so it has to be re-approved by the admin
			db_query("DELETE FROM foundry_news WHERE news_id='$id'");

			if (!$summary) {
				$summary='(none)';
			}
			if (!$details) {
				$details='(none)';
			}

			$sql="UPDATE news_bytes SET is_approved='$status', summary='".htmlspecialchars($summary)."', ".
				"details='".htmlspecialchars($details)."' WHERE id='$id' AND group_id='$group_id'";
			$result=db_query($sql);

			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= $Language->getText('general', 'error_on_update');
			} else {
				$feedback .= $Language->getText('news_admin', 'newsbyte_updated');
			}
			/*
				Show the list_queue
			*/
			$approve='';
			$list_queue='y';
		}
	}

	news_header(array('title'=>$Language->getText('news_admin', 'title')));

	if ($approve) {
		/*
			Show the submit form
		*/

		$sql="SELECT * FROM news_bytes WHERE id='$id' AND group_id='$group_id'";
		$result=db_query($sql);
		if (db_numrows($result) < 1) {
			exit_error($Language->getText('general', 'error'), $Language->getText('newsbyte_admin', 'newsbyte_not_found'));
		}
		
		$group =& group_get_object($group_id);
		
		echo notepad_func();
		echo '
		<h3>'.$Language->getText('news_admin', 'approve_newsbyte', array($group->getPublicName())).'</h3>
		<p />
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="group_id" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />';

		$user =& user_get_object(db_result($result,0,'submitted_by'));

		echo '
		<strong>'.$Language->getText('news_admin', 'submitted_by').':</strong> '.$user->getRealName().'<br />
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />

		<strong>'.$Language->getText('news_admin', 'status').':</strong><br />
		<input type="radio" name="status" value="0" checked="checked" /> '.$Language->getText('news_admin', 'status_displayed').'<br />
		<input type="radio" name="status" value="4" /> '.$Language->getText('news_admin', 'status_delete').'<br />

		<strong>'.$Language->getText('news_admin', 'subject').':</strong><br />
		<input type="text" name="summary" value="'.db_result($result,0,'summary').'" size="30" maxlength="60"><br />
		<strong>'.$Language->getText('news_admin', 'details').':</strong>'.notepad_button('document.forms[1].details').'<br />
		<textarea name="details" rows="5" cols="50">'.db_result($result,0,'details').'</textarea><p>
		<strong>'.$Language->getText('news_admin', 'removal_when_edit', array($GLOBALS['sys_name'])).'</strong><br /></p>
		<input type="submit" name="submit" value="'.$Language->getText('general', 'submit').'" />
		</form>';

	} else {
		/*
			Show list of waiting news items
		*/

		$sql="SELECT * FROM news_bytes WHERE is_approved <> 4 AND group_id='$group_id'";
		$result=db_query($sql);
		$rows=db_numrows($result);
		$group =& group_get_object($group_id);
		
		if ($rows < 1) {
			echo '
				<h4>'.$Language->getText('news_admin','noqueued').': '.$group->getPublicName().'</h4>';
		} else {
			echo '
				<h4>'.$Language->getText('news_admin','queued').': '.$group->getPublicName().'</h4>
				<ul>';
			for ($i=0; $i<$rows; $i++) {
				echo '
				<li><a href="/news/admin/?approve=1&id='.db_result($result,$i,'id').'&amp;group_id='.
					db_result($result,$i,'group_id').'">'.
					db_result($result,$i,'summary').'</a></li>';
			}
			echo '</ul>';
		}

	}
	news_footer(array());

} else if (user_ismember($sys_news_group,'A')) {
	/*

		News uber-user admin pages

		Show all waiting news items except those already rejected.

		Admin members of $sys_news_group (news project) can edit/change/approve news items

	*/
	if ($post_changes) {
		if ($approve) {
			if ($status==1) {
				/*
					Update the db so the item shows on the home page
				*/
				$sql="UPDATE news_bytes SET is_approved='1', post_date='".time()."', ".
					"summary='".htmlspecialchars($summary)."', details='".htmlspecialchars($details)."' WHERE id='$id'";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= $Language->getText('general', 'error_on_update');
				} else {
					$feedback .= $Language->getText('news_admin', 'newsbyte_updated');
				}
			} else if ($status==2) {
				/*
					Move msg to deleted status
				*/
				$sql="UPDATE news_bytes SET is_approved='2' WHERE id='$id'";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= $Language->getText('general', 'error_on_update');
					$feedback .= db_error();
				} else {
					$feedback .= $Language->getText('news_admin', 'newsbyte_deleted');
				}
			}

			/*
				Show the list_queue
			*/
			$approve='';
			$list_queue='y';
		} else if (getStringFromRequest('mass_reject')) {
			/*
				Move msg to rejected status
			*/
			$sql="UPDATE news_bytes "
			     ."SET is_approved='2' "
			     ."WHERE id IN ('".implode($news_id,"','")."')";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= $Language->getText('general', 'error_on_update');
				$feedback .= db_error();
			} else {
				$feedback .= $Language->getText('news_admin', 'newsbyte_rejected');
			}
		}
	}

	news_header(array('title'=>$Language->getText('news_admin', 'title')));

	if ($approve) {
		/*
			Show the submit form
		*/

		$sql="SELECT groups.unix_group_name,news_bytes.* ".
			"FROM news_bytes,groups WHERE id='$id' ".
			"AND news_bytes.group_id=groups.group_id ";
		$result=db_query($sql);
		if (db_numrows($result) < 1) {
			exit_error($Language->getText('general', 'error'), $Language->getText('newsbyte_admin', 'newsbyte_not_found'));
		}
		
		$group =& group_get_object(db_result($result,0,'group_id'));
		$user =& user_get_object(db_result($result,0,'submitted_by'));

		echo '
		<h3>'.$Language->getText('news_admin', 'approve_newsbyte', array($group->getPublicName())).'</h3>
		<p />
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="for_group" value="'.db_result($result,0,'group_id').'" />
		<input type="hidden" name="id" value="'.db_result($result,0,'id').'" />
		<strong>'.$Language->getText('news_admin', 'submitted_for_group').':</strong> <a href="/projects/'.strtolower(db_result($result,0,'unix_group_name')).'/">'.$group->getPublicName().'</a><br />
		<strong>'.$Language->getText('news_admin', 'submitted_by').':</strong> '.$user->getRealName().'<br />
		<input type="hidden" name="approve" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="radio" name="status" value="1" /> '.$Language->getText('news_admin', 'approve_for_frontpage').'<br />
		<input type="radio" name="status" value="0" /> '.$Language->getText('news_admin', 'do_nothing').'<br />
		<input type="radio" name="status" value="2" checked="checked" /> '.$Language->getText('news_admin', 'reject').'<br />
		<strong>'.$Language->getText('news_admin', 'subject').':</strong><br />
		<input type="text" name="summary" value="'.db_result($result,0,'summary').'" size="30" maxlength="60" /><br />
		<strong>'.$Language->getText('news_admin', 'details').':</strong><br />
		<textarea name="details" rows="5" cols="50">'.db_result($result,0,'details').'</textarea><br />
		<input type="submit" name="submit" value="'.$Language->getText('general', 'submit').'" />
		</form>';

	} else {

		/*
			Show list of waiting news items
		*/

	        $old_date = time()-60*60*24*30;
		$sql_pending= "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=0
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			AND groups.is_public=1
			AND groups.status='A'
			ORDER BY post_date
		";

		$old_date = time()-(60*60*24*7);
		$sql_rejected = "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=2
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			ORDER BY post_date
		";

		$sql_approved = "
			SELECT groups.group_id,id,post_date,summary,
				group_name,unix_group_name
			FROM news_bytes,groups
			WHERE is_approved=1
			AND news_bytes.group_id=groups.group_id
			AND post_date > '$old_date'
			ORDER BY post_date
		";

		show_news_approve_form(
			$sql_pending,
			$sql_rejected,
			$sql_approved
		);

	}
	news_footer(array());

} else {

	exit_error($Language->getText('general','permdenied'),$Language->getText('news_admin','permdenied',$GLOBALS['sys_name']));

}
?>
