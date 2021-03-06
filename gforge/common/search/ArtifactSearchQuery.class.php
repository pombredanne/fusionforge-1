<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'search/SearchQuery.class.php';

class ArtifactSearchQuery extends SearchQuery {
	
	/**
	 * group id
	 *
	 * @var int $groupId
	 */
	var $groupId;
	
	/**
	 * artifact id
	 *
	 * @var int $artifactId
	 */
	var $artifactId;
	
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param int $artifactId artifact id
	 */
	function ArtifactSearchQuery($words, $offset, $isExact, $groupId, $artifactId) {
		//TODO: Why is groupId an arg and var since it isn't used anywhere?
		$this->groupId = $groupId;
		$this->artifactId = $artifactId;
		
		$this->SearchQuery($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		global $sys_database_type;

		global $sys_use_fti;
		if ($sys_use_fti) {
			$words=$this->getFormattedWords();
			$artifactId = $this->artifactId;
		    if (count($words)) {
				$tsquery0 = "headline(summary, '".$this->getFormattedWords()."') as summary";
				$tsquery = ", artifact_idx ai, artifact_message_idx ami, to_tsquery('".$words."') q";
				$tsmatch = "(ai.vectors @@ q OR ami.vectors @@ q)"; 
				$rankCol = "sum((rank(ai.vectors, q)+rank(ami.vectors, q))) as rank";
				$tsjoin = 'AND ai.artifact_id = a.artifact_id '
						. 'AND ami.id = am.id ';
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "summary";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "0 as rank";
				$phraseOp = "";
			}
			$phraseCond = '';
			if (count($this->phrases)) {
				$detailsCond = $this->getMatchCond('a.details', $this->phrases);
				$summaryCond = $this->getMatchCond('a.summary', $this->phrases);
				$msgCond = $this->getMatchCond('am.body', $this->phrases);
				$phraseCond = "$phraseOp (($detailsCond) OR ($summaryCond))";
			}
			$sql = "
				select a.group_artifact_id,a.artifact_id, $tsquery0,
				a.open_date,users.realname, rank
				FROM (SELECT a.artifact_id,
				$rankCol
				FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id)
				$tsquery
				WHERE 
				a.group_artifact_id='$artifactId'
				$tsjoin
				AND ($tsmatch $phraseCond)
				GROUP BY a.artifact_id) x,
				artifact a, users
				WHERE
				a.artifact_id = x.artifact_id
				AND users.user_id=a.submitted_by
				ORDER BY group_artifact_id ASC, rank DESC, a.artifact_id ASC";
		} else {

			if ($sys_database_type == "mysql") {
				$sql = 'SELECT DISTINCT a.group_artifact_id,a.artifact_id,a.summary,a.open_date,users.realname ';
			} else {
				$sql = 'SELECT DISTINCT ON (a.group_artifact_id,a.artifact_id) a.group_artifact_id,a.artifact_id,a.summary,a.open_date,users.realname ';
			}
			$sql.='FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id), users ' 
				. 'WHERE a.group_artifact_id=\''.$this->artifactId.'\' '
				. 'AND users.user_id=a.submitted_by '
				. 'AND (('.$this->getIlikeCondition('a.details', $this->words).') ' 
				. 'OR ('.$this->getIlikeCondition('a.summary', $this->words).') '
				. 'OR ('.$this->getIlikeCondition('am.body', $this->words).')) '
				. 'ORDER BY group_artifact_id ASC, a.artifact_id ASC';
		}
		return $sql;
	}

	/**
	 * getSearchByIdQuery - get the sql query built to get the search results when we are looking for an int
	 *
	 * @return string sql query to execute
	 */	
	function getSearchByIdQuery() {
		global $sys_database_type;

		if ($sys_database_type == "mysql") {
			$sql = 'SELECT DISTINCT a.group_artifact_id, a.artifact_id ';
		} else {
			$sql = 'SELECT DISTINCT ON (a.group_artifact_id,a.artifact_id) a.group_artifact_id, a.artifact_id ';
		}
		$sql.='FROM artifact a ' 
			. 'WHERE a.group_artifact_id=\''.$this->artifactId.'\' '
			. 'AND a.artifact_id=\''.$this->searchId.'\'';

		return $sql;
	}
	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
