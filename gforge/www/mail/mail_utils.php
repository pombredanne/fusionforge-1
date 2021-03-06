<?php
/**
 * GForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003 (c) Guillaume Smet
 *
 * @version   $Id$
 *
 */


function mail_header($params) {
	global $HTML, $group_id;

	if ($group_id) {
		//required for site_project_header
		$params['group'] = $group_id;
		$params['toptab'] = 'mail';

		$project =& group_get_object($group_id);

		if ($project && is_object($project)) {
			if (!$project->usesMail()) {
				exit_error(_('Error'), _('This Project Has Turned Off The Mailing Lists'));
			}
		}


		site_project_header($params);
		if (session_loggedin()) {
			$perm =& $project->getPermission(session_get_user());
			if ($perm && is_object($perm) && !$perm->isError() && $perm->isAdmin()) {
				echo $HTML->subMenu(
					array(
						_('Admin')
					),
					array(
						'/mail/admin/?group_id='.$group_id
					)
				);
			}
		}
	} else {
		exit_no_group();
	}
}

function mail_footer($params) {
	site_project_footer($params);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
