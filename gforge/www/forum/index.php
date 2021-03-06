<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
    Message Forums
    By Tim Perdue, Sourceforge, 11/99

    Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

    Complete OO rewrite by Tim Perdue 12/2002
*/

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'forum/../forum/include/ForumHTML.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/Forum.class.php';

$group_id = getIntFromRequest('group_id');
if ($group_id) {
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$ff=new ForumFactory($g);
	if (!$ff || !is_object($ff) || $ff->isError()) {
		exit_error(_('Error'),$ff->getErrorMessage());
	}

	$farr =& $ff->getForums();

	if ( $farr !== false && count($farr) == 1 ) {
  		Header("Location: ".util_make_url ("/forum/forum.php?forum_id=".$farr[0]->getID()));
		exit();
	}

	forum_header(array('title'=>sprintf(_('Forums for %1$s'), $g->getPublicName()) ));

	if ($ff->isError() || count($farr) < 1) {
		echo '<h1>'.sprintf(_('No Forums Found For %1$s'), $g->getPublicName()) .'</h1>';
		if($ff->isError()) {
			echo $ff->getErrorMessage();
		}
		forum_footer(array());
		exit;
	}


//	echo _('<p>Choose a forum and you can browse, search, and post messages.<p>');

	echo $HTML->subMenu(array("My Monitored Forums"),array("/forum/myforums.php?group_id=$group_id"));
	$tablearr=array(_('Forum'),_('Description'),_('Threads'),_('Posts'), _('Last Post'),_('Moderation Level'));
	echo $HTML->listTableTop($tablearr);

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/

	for ($j = 0; $j < count($farr); $j++) {
		if (!is_object($farr[$j])) {
			//just skip it - this object should never have been placed here
		} elseif ($farr[$j]->isError()) {
			echo $farr[$j]->getErrorMessage();
		} else {
			switch ($farr[$j]->getModerationLevel()) {
				case 0 : $modlvl = _('No Moderation');break;
				case 1 : $modlvl = _('Anonymous & Non Group Users');break;
				case 2 : $modlvl = _('All Except Admins');break;
			}
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td><a href="forum.php?forum_id='. $farr[$j]->getID() .
				'&group_id=' . $group_id . '">'.
				html_image("ic/forum20w.png","20","20",array("border"=>"0")) .
				'&nbsp;' .
				$farr[$j]->getName() .'</a></td>
				<td>'.$farr[$j]->getDescription().'</td>
				<td style="text-align:center">'.$farr[$j]->getThreadCount().'</td>
				<td style="text-align:center">'. $farr[$j]->getMessageCount() .'</td>
				<td>'.  date(_('Y-m-d H:i'),$farr[$j]->getMostRecentDate()) .'</td>
				<td style="text-align:center">'. $modlvl  .'</td></tr>';
		}
	}
	echo $HTML->listTableBottom();

	forum_footer(array());

} else {

	exit_no_group();

}

?>
