<?php
/**
 * GForge Search Engine
 *
 * Copyright 2005 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id: GroupSearchEngine.class.php,v 1.2 2004/12/12 23:34:46 gsmet Exp $
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');

class FrsGroupSearchEngine extends GroupSearchEngine {
	
	function FrsGroupSearchEngine() {
		global $Language;
		$this->GroupSearchEngine(SEARCH__TYPE_IS_FRS, 'FrsHtmlSearchRenderer', _('This project\'s releases'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters)) {
			if($this->Group->usesFRS()) {
				return true;
			}
		}
		return false;
	}
}

?>