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

require_once('pre.php');
require_once('www/pm/include/ProjectGroupHTML.class');
require_once('common/pm/ProjectGroupFactory.class');
require_once('common/pm/ProjectCategory.class');
require_once('www/project/stats/project_stats_utils.php');
require_once('www/include/tool_reports.php');

if (!session_loggedin()) {
	exit_not_logged_in();
}

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}

$perm =& $g->getPermission( session_get_user() );
if (!$perm->isPMAdmin()) {
	exit_permission_denied();
}


$page_title="Task Reporting System";
$bar_colors=array("pink","violet");

function pm_reporting_header($group_id) {
		reports_header($group_id,
			array('aging','tech','subproject'),
				array('Aging Report','Tasks by Technician','Tasks by Subproject')
	);
}

function pm_quick_report($group_id,$title,$subtitle1,$sql1,$subtitle2,$sql2,$comment="") {
		global $bar_colors;

	   	pm_header(array ("title"=>$title,'pagename'=>'pm_reporting'));
	   	pm_reporting_header($group_id);
	   	echo "\n<H1>$title</H1>";

		reports_quick_graph($subtitle1,$sql1,$sql2,$bar_colors);

		if ($comment) echo $comment;

	pm_footer(array());
}


include_once('www/include/HTML_Graphs.php');

if ($what) {
	/*
		Update the database
	*/

	$period_clause=period2sql($period,$span,"start_date");

	if ($what=="aging") {

		pm_header(array ("title"=>"Aging Report",'pagename'=>'pm_reporting'));
		pm_reporting_header($group_id);
		echo "\n<H1>Aging Report</H1>";

		$time_now=time();
//		echo $time_now."<P>";

		if (!$period || $period=="lifespan") {
			$period="month";
			$span=12;
		}

		if (!$span) {
			$span=1;
		}
		$sub_duration=period2seconds($period,1);
//		echo $sub_duration,"<br>";

		for ($counter=1; $counter<=$span; $counter++) {

			$start=($time_now-($counter*$sub_duration));
			$end=($time_now-(($counter-1)*$sub_duration));

			$sql="SELECT avg((end_date-start_date)/(24*60*60)) ".
				 "FROM project_task,project_group_list ".
				 "WHERE end_date > 0 ".
				 "AND (start_date >= '$start' AND start_date <= '$end') ".
				 "AND project_task.status_id=2 ".
				 "AND project_group_list.group_project_id=project_task.group_project_id ".
				 "AND project_group_list.group_id='$group_id' ";

			$result = db_query($sql);

			$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
			$values[$counter-1]=((int)(db_result($result, 0,0)*1000))/1000;
		}

		GraphIt($names, $values,
			"Average Duration For Closed Tasks (days)");

		echo "<P>";

		for ($counter=1; $counter<=$span; $counter++) {

			$start=($time_now-($counter*$sub_duration));
			$end=($time_now-(($counter-1)*$sub_duration));

			$sql="SELECT count(*) ".
				 "FROM project_task,project_group_list ".
				 "WHERE start_date >= '$start' ".
				 "AND start_date <= '$end' ".
				 "AND project_group_list.group_project_id=project_task.group_project_id ".
				 "AND project_group_list.group_id='$group_id' ";

			$result = db_query($sql);

			$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
			$values[$counter-1]=db_result($result, 0,0);
		}

		GraphIt($names, $values, "Number of Tasks Started");

		echo "<P>";

		for ($counter=1; $counter<=$span; $counter++) {

			$start=($time_now-($counter*$sub_duration));
			$end=($time_now-(($counter-1)*$sub_duration));

			$sql="SELECT count(*) ".
				 "FROM project_task,project_group_list ".
				 "WHERE start_date <= '$end' ".
				 "AND (end_date >= '$end' OR end_date < 1 OR end_date is null) ".
				 "AND project_group_list.group_project_id=project_task.group_project_id ".
				 "AND project_group_list.group_id='$group_id' ";

			$result = db_query($sql);

			$names[$counter-1]=date("Y-m-d",($end));
			$values[$counter-1]=db_result($result, 0,0);
		}

		GraphIt($names, $values, "Number of Tasks Still Not Completed");

		echo "<P>";

		pm_footer(array());

	} else if ($what=="subproject") {

		$sql1="SELECT project_group_list.project_name AS Subproject, count(*) AS Count ".
			  "FROM project_group_list,project_task ".
			  "WHERE project_group_list.group_project_id=project_task.group_project_id ".
			  "AND project_task.status_id = '1' ".
			  "AND project_group_list.group_id='$group_id' ".
			  $period_clause .
			  "GROUP BY Subproject";
		$sql2="SELECT project_group_list.project_name AS Subproject, count(*) AS Count ".
			  "FROM project_group_list,project_task ".
			  "WHERE project_group_list.group_project_id=project_task.group_project_id ".
			  "AND project_task.status_id <> '3' ".
			  "AND project_group_list.group_id='$group_id' ".
			  $period_clause .
			  "GROUP BY Subproject";

		pm_quick_report($group_id,
			  "Tasks By Category",
			  "Open Tasks By Category",$sql1,
			  "All Tasks By Category",$sql2);

	} else if ($what=="tech") {

		$sql1="SELECT users.user_name AS Technician, count(*) AS Count ".
			  "FROM users,project_group_list,project_task,project_assigned_to ".
			  "WHERE users.user_id=project_assigned_to.assigned_to_id ".
			  "AND project_assigned_to.project_task_id=project_task.project_task_id ".
			  "AND project_task.group_project_id=project_group_list.group_project_id ".
			  "AND project_task.status_id = '1' ".
			  "AND project_group_list.group_id='$group_id' ".
			  $period_clause .
			  "GROUP BY Technician";

		$sql2="SELECT users.user_name AS Technician, count(*) AS Count ".
			  "FROM users,project_group_list,project_task,project_assigned_to ".
			  "WHERE users.user_id=project_assigned_to.assigned_to_id ".
			  "AND project_assigned_to.project_task_id=project_task.project_task_id ".
			  "AND project_task.group_project_id=project_group_list.group_project_id ".
			  "AND project_task.status_id <> '3' ".
			  "AND project_group_list.group_id='$group_id' ".
			  $period_clause .
			  "GROUP BY Technician";

		pm_quick_report($group_id,
		  "Tasks By Technician",
		  "Open Tasks By Technician",$sql1,
		  "All Tasks By Technician",$sql2,
		  "<p>Note that same task can be ".
		  "assigned to several technicians. ".
		  "Such task will be counted for ".
		  "each of them.</p>");

	} else {
		exit_missing_param();
	}

} else {
	/*
		Show main page
	*/
	pm_header(array ("title"=>$page_title,'pagename'=>'pm_reporting'));

	pm_reporting_header($group_id);

	pm_footer(array());

}

?>
