<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
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

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once($sys_path_to_jpgraph.'/jpgraph.php');
require_once($sys_path_to_jpgraph.'/jpgraph_line.php');
require_once $gfcommon.'reporting/ReportUserAct.class.php';
require_once $gfwww.'include/unicode.php';

session_require( array('group'=>$sys_stats_group) );

$dev_id = getStringFromRequest('dev_id');
$SPAN = getStringFromRequest('SPAN');
$start = getStringFromRequest('start');
$end = getStringFromRequest('end');

//
//	Create Report
//
$report=new ReportUserAct($SPAN,$dev_id,$start,$end); 

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	echo $report->getErrorMessage();
	exit;
}

//
// Get User Object
//
$u =& user_get_object($dev_id);
if (!$u || $u->isError()) {
	exit_error("Could Not Get User");
}

// Create the graph. These two calls are always required
$graph  = new Graph(640, 480,"auto");
$graph->SetMargin(50,10,35,50);
$graph->SetScale( "textlin");
//$graph->SetScale( "linlog");
//$graph ->SetYScale("log");

if ($area=='tracker') {

	// Create the tracker open plot
	$ydata  =& $report->getTrackerOpened();
	$lineplot =new LinePlot($ydata);
	$lineplot ->SetColor("black");
	$graph->Add( $lineplot);

	// Create the tracker close plot
	$ydata2  =& $report->getTrackerClosed();
	$lineplot2 =new LinePlot($ydata2);
	$lineplot2 ->SetColor("blue");
	$graph->Add( $lineplot2 );

	//	Legends
	$lineplot->SetLegend (convert_unicode(_('Tracker Items Opened')));
	$lineplot2 ->SetLegend(convert_unicode(_('Tracker Items Closed')));

} elseif ($area=='forum') {

	// Create the forum plot
	$ydata3  =& $report->getForum();
	$lineplot3 =new LinePlot($ydata3);
	$lineplot3 ->SetColor("orange");
	$graph->Add( $lineplot3 );

	//	Legends
	$lineplot3 ->SetLegend("Forum");

} elseif ($area=='docman') {

	// Create the Docman plot
	$ydata4  =& $report->getDocs();
	$lineplot4 =new LinePlot($ydata4);
	$lineplot4 ->SetColor("red");
	$graph->Add( $lineplot4 );

	//	Legends
	$lineplot4 ->SetLegend("Docs");

} elseif ($area=='taskman') {

	// Create the Tasks Opened plot
	$ydata5  =& $report->getTaskOpened();
	$lineplot5 =new LinePlot($ydata5);
	$lineplot5 ->SetColor("purple");
	$graph->Add( $lineplot5 );

	// Create the Tasks Closed plot
	$ydata6  =& $report->getTaskClosed();
	$lineplot6 =new LinePlot($ydata6);
	$lineplot6 ->SetColor("yellow");
	$graph->Add( $lineplot6 );

	//	Legends
	$lineplot5 ->SetLegend("Task Open");
	$lineplot6 ->SetLegend("Task Close");
}


//
//	Titles
//
$graph->title->Set("User Activity For: ".$u->getRealName() 
	." (".date('m/d/Y',$report->getStartDate()) ."-". date('m/d/Y',$report->getEndDate()) .")");
$graph->subtitle->Set($sys_name);
//$graph->xaxis-> title->Set("Date" );
//$graph->yaxis-> title->Set("Number" ); 

$a=$report->getDates();
$graph->xaxis->SetTickLabels($a); 
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetTextLabelInterval($report->getGraphInterval());

// Display the graph
$graph->Stroke();

?>
