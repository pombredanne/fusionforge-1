<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

$no_gz_buffer=true;

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'docman/include/doc_utils.php';
require_once $gfcommon.'docman/Document.class.php';

$arr=explode('/',getStringFromServer('REQUEST_URI'));
$group_id=$arr[3];
$docid=$arr[4];

if ($docid) {

	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error('Error',$g->getErrorMessage());
	}
	if(!$g->isPublic()) {
		session_require(array('group' => $group_id));
	}

	$d = new Document($g,$docid);
	if (!$d || !is_object($d)) {
		exit_error('Document unavailable','Document is not available.');
	} elseif ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	Header ('Content-disposition: filename="'.str_replace('"', '', $d->getFileName()).'"');

	if (strstr($d->getFileType(),'app')) {
		Header ("Content-type: application/binary");
	} else {
		Header ("Content-type: ".$d->getFileType());
	}

	echo $d->getFileData();

} else {
	exit_error(_('No document data'),_('No document to display - invalid or inactive document number.'));
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
