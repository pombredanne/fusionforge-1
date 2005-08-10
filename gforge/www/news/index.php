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
require_once('www/news/news_utils.php');
require_once('common/forum/Forum.class');

news_header(array('title'=>$Language->getText('news', 'title'),'pagename'=>'news','sectionvals'=>array(group_getname($group_id))));

echo $Language->getText('news', 'choose');

$group_id = getIntFromRequest('group_id');
$limit = getIntFromRequest('limit');
$offset = getIntFromRequest('offset');

/*
	Put the result set (list of forums for this group) into a column with folders
*/
if ($group_id && ($group_id != $sys_news_group)) {
	$sql="SELECT * FROM news_bytes WHERE group_id='$group_id' AND is_approved <> '4' ORDER BY post_date DESC";
} else {
	$sql="SELECT * FROM news_bytes WHERE is_approved='1' ORDER BY post_date DESC";
}

if (!$limit || $limit>50) $limit=50;
$result=db_query($sql,$limit+1,$offset);
$rows=db_numrows($result);
$more=0;
if ($rows>$limit) {
	$rows=$limit;
        $more=1;
}

if ($rows < 1) {
	if ($group_id) {
		echo '<h2>'.$Language->getText('news', 'nonewsfor', group_getname($group_id)).'</h2>';
	} else {
		echo '<h2>'.$Language->getText('news', 'nonews').'</h2>';
	}
	echo '
		<p>' . $Language->getText('news', 'noitems') . '</p>';;
	echo db_error();
} else {
	echo '<table width="100%" border="0">
		<tr><td valign="top">';

	for ($j = 0; $j < $rows; $j++) { 
		echo '
		<a href="/forum/forum.php?forum_id='.db_result($result, $j, 'forum_id').'">'.
			html_image("ic/cfolder15.png","15","13",array("border"=>"0")) . ' &nbsp;'.
			stripslashes(db_result($result, $j, 'summary')).'</a> ';
		echo '
		<br />';
	}

        if (getStringFromRequest('more')) {
        	echo '<br /><a href="'
                     .'?group_id='.$group_id.'&amp;limit='.$limit
                     .'&amp;offset='. (string)($offset+$limit) .'">['.$Language->getText('news', 'olderheadlines').']</a>';
        }

        echo '
	</td></tr></table>';
}

news_footer(array());

?>
