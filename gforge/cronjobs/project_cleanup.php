#! /usr/bin/php5 -f
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require dirname(__FILE__).'/../www/env.inc.php';
require $gfwww.'include/squal_pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

db_begin();


#one hour ago for projects
$then=(time()-3600);
db_query("DELETE FROM groups WHERE status='I' and register_time < '$then'");
$err .= db_error();

#one week ago for users
$then=(time()-604800);
db_query("DELETE FROM user_group WHERE EXISTS (SELECT user_id FROM users ".
"WHERE status='P' and add_date < '$then' AND users.user_id=user_group.user_id)");
$err .= db_error();

db_query("DELETE FROM users WHERE status='P' and add_date < '$then'");
$err .= db_error();

#30 days ago for sessions
$then=(time()-(30*60*60*24));
db_query("DELETE FROM user_session WHERE time < '$then'");
$err .= db_error();

#one month ago for preferences
$then=(time()-604800*4);
db_query("DELETE FROM user_preferences WHERE set_date < '$then'");
$err .= db_error();

#3 weeks ago for jobs
$then=(time()-604800*3);
db_query("UPDATE people_job SET status_id = '3' where post_date < '$then'");
$err .= db_error();

#1 day ago for form keys
$then=(time()-(60*60*24));
db_query("DELETE FROM form_keys WHERE creation_date < '$then'");
$err .= db_error();

db_commit();
if (db_error()) {
	$err .= "Error: ".db_error();
}

cron_entry(7,$err);

?>
