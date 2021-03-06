<?php

/**
 * projects_hierarchyPlugin Class
 *
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

class projects_hierarchyPlugin extends Plugin {
	function projects_hierarchyPlugin () {
		$this->Plugin() ;
		$this->name = "projects_hierarchy" ;
		$this->text = "projects_hierarchy!" ; // To show in the tabs, use...
		$this->hooks[] = "user_personal_links";//to make a link to the user's personal part of the plugin
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "groupmenu" ;	// To put into the project tabs
		$this->hooks[] = "groupisactivecheckbox" ; // The "use ..." checkbox in editgroupinfo
		$this->hooks[] = "groupisactivecheckboxpost" ; //
		$this->hooks[] = "userisactivecheckbox" ; // The "use ..." checkbox in user account
		$this->hooks[] = "userisactivecheckboxpost" ; //
		$this->hooks[] = "project_admin_plugins"; // to show up in the admin page fro group
		$this->hooks[] = "admin_project_link"; // to add son to a project
		$this->hooks[] = "project_home_link"; // to see father and sons in project home
		$this->hooks[] = "tree"; // to see the tree of projects
		$this->hooks[] = "delete_link"; // to delete link
	}

	function CallHook ($hookname, $params) {
		global $use_projects_hierarchyplugin,$G_SESSION,$HTML;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("projects_hierarchy")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/projects_hierarchy/index.php' . $param ));				
			}
		} elseif ($hookname == "groupmenu") {
			$group_id=$params['group'];
			$project = &group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['DIRS'][]='/plugins/projects_hierarchy/index.php?type=group&id=' . $group_id . "&pluginname=" . $this->name; // we indicate the part we're calling is the project one
			} else {
				//$params['TITLES'][]=$this->text." is [Off]";
			}	
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_projects_hierarchyplugin = getStringFromRequest('use_projects_hierarchyplugin');
			if ( $use_projects_hierarchyplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "userisactivecheckbox") {
			//check if user is active
			// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
		} elseif ($hookname == "userisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
			$user = $params['user'];
			$use_projects_hierarchyplugin = getStringFromRequest('use_projects_hierarchyplugin');
			if ( $use_projects_hierarchyplugin == 1 ) {
				$user->setPluginUse ( $this->name );
			} else {
				$user->setPluginUse ( $this->name, false );
			}
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_projects_hierarchyplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo ">    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "user_personal_links") {
			// this displays the link in the user's profile page to it's personal projects_hierarchy (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$text = $params['text'];
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>' ;
				echo util_make_link ("/plugins/projects_hierarchy/index.php?id=$userid&type=user&pluginname=".$this->name,
						     _('View Personal projects_hierarchy')
					);
				echo '</p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  projects_hierarchy administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo util_make_link ("/plugins/projects_hierarchy/index.php?id=".$group->getID().'&type=admin&pluginname='.$this->name,
						     _('View the projects_hierarchy Administration')
					);
				echo '<br />';
			}
		}												    
		elseif ($hookname == "tree") {
			
			header('Location: ../plugins/projects_hierarchy/softwaremap.php');
			
		} 
		elseif ($hookname == "project_home_link") {
			// ############################## Display link
			$group_id = $params;
			echo $HTML->boxTop(_('Linked projects'));
			$cpt_project = 0 ;
			// father request
			$query = "SELECT DISTINCT group_id,unix_group_name,group_name FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type ='shar' AND plugin_projects_hierarchy.activated='t' AND groups.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.sub_project_id=".$group_id;
			$res = db_query($query);
			echo db_error();
			while ($row = db_fetch_array($res)) {
				echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Parent project').': <a href="'.$GLOBALS['sys_urlprefix'].'/projects/'.$row['unix_group_name'].'/">' . $row['group_name'] . '</a><br/>';
				$cpt_project ++;
			}
			
			if($cpt_project != 0){
			print '<hr size="1" />';
			}
			$cpt_temp = $cpt_project ;
			// sons request
			$query = "SELECT DISTINCT group_id,unix_group_name,group_name,com FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type ='shar' AND plugin_projects_hierarchy.activated='t' AND groups.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.project_id=".$group_id;
			$res = db_query($query);
			echo db_error();
			while ($row = db_fetch_array($res)) {
				echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Child project').' : <a href="'.$GLOBALS['sys_urlprefix'].'/projects/'.$row['unix_group_name'].'/">' . $row['group_name'] . '</a> : '.$row['com'].'<br/>';
				$cpt_project ++;
			}
			
			
			if($cpt_project != $cpt_temp){
			print '<hr size="1" />';
			}
			$cpt_temp = $cpt_project ;
			
			// links if project is father
			$query = "SELECT DISTINCT group_id,unix_group_name,group_name,com FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type ='navi' AND plugin_projects_hierarchy.activated='t' AND groups.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.project_id=".$group_id;
			$res = db_query($query);
			echo db_error();
			while ($row = db_fetch_array($res)) {
				echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Links')." : <a href=\"".$GLOBALS['sys_urlprefix']."/projects/".$row['unix_group_name']."/\">" . $row['group_name'] . "</a> :  ".$row['com']."<br/>";
				$cpt_project ++;
			}
			// links if project is son
			$query = "SELECT DISTINCT group_id,unix_group_name,group_name,com FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type ='navi' AND plugin_projects_hierarchy.activated='t' AND groups.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.sub_project_id=".$group_id;
			$res = db_query($query);
			echo db_error();
			while ($row = db_fetch_array($res)) {
				echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Links')." : <a href=\"".$GLOBALS['sys_urlprefix']."/projects/".$row['unix_group_name']."/\">" . $row['group_name'] . "</a><br/>";
				$cpt_project ++;
			}
			
			
			
			if($cpt_project != $cpt_temp){
			print '<hr size="1" />';
			}
			
			if($cpt_project == 0){
			echo _('No linked project avalaible');	
			print '<hr size="1" />';
			}
			
			echo $HTML->boxBottom();
			
		}
		elseif ($hookname == "admin_project_link") {
			//modif pour hierarchie par Fabien le 10/10/06
			//add files add_son.php, del_father.php,del_son.php,wait_son.php and hierarchy_utils.php
			
			global $gfplugins;
			require_once $gfplugins.'projects_hierarchy/www/hierarchy_utils.php';
			//include('../../plugins/projects_hierarchy/hierarchy_utils.php');
			$group_id = $params ;
			echo $HTML->boxMiddle(_('Modify the hierarchy'));
			echo '<form action="../../plugins/projects_hierarchy/add_son.php?group_id='.$group_id.'" method="POST" name="formson">';
			//include('hierarchy_utils.php');
			//select box of sons
			echo '<table><tr>';
			echo '<td>'._('Select a project :').'</td><td>'.son_box($group_id,'sub_project_id','0').'</td><td>&nbsp;</td>' ;
			echo '</tr><tr>' ;
			echo '<td>'._('Commentary :').'</td><td> <input type="text" size="25" value="" name="com"></td>' ;
			//echo type_son_box();
			echo '<td><input type="submit"  name="son" value="'._('Add son project').'"></td></tr></table></form>';
			echo '<br/>';
			echo '<form action="../../plugins/projects_hierarchy/add_link.php?group_id='.$group_id.'" method="POST" name="formlink">';
			//include('hierarchy_utils.php');
			//select box of sons
			echo '<table><tr>';
			echo '<td>'._('Select a project :').'</td><td>'.link_box($group_id,'sub_project_id','0').'</td><td>&nbsp;</td>';
			echo '</tr><tr>' ;
			echo '<td>'._('Commentary :').'</td><td><input type="text" size="25" value="" name="com"></td>' ;
			echo '<td><input type="submit"  name="son" value="'._('Add a link').'"></td></tr></table></form>';
			echo '<br/>';
			//select all the sons of the current project
			$sql_son = "SELECT group_id,group_name,unix_group_name,sub_project_id, activated,link_type,com FROM groups,plugin_projects_hierarchy WHERE " .
					"	(	groups.group_id = plugin_projects_hierarchy.sub_project_id " .
					"AND plugin_projects_hierarchy.project_id = ".$group_id.")" ;
							
			
			$res_son=db_query($sql_son)or die(db_error());
			if (!$res_son || db_numrows($res_son) < 1) {
				$cpt_son = 0;
			}
			else {
				//display of sons
				$cpt_son = 1 ;
				echo _('Link list');
				echo '<table>';
				$i = 0;
				while($row_son = db_fetch_array($res_son)){
						$i++;
						echo '<tr>';
						echo '<td>';
						//link to the project
						echo "<a href=\"../../projects/".$row_son['unix_group_name']."/\">".$row_son['group_name']."</a>";
						echo '</td>'; 
						
						echo '<td>';
						if($row_son['link_type'] == 'navi'){
						echo _('Navigation link');	
						}
						else {
						echo _('Share link');
						}
						echo '</td>';
						
						echo '<td>';
						if($row_son['activated'] == 'f'){
							echo _('Waiting');
						}
						else {
							print "<b>"._('Authorize')."</b>";
						}
						echo '</td>';
						echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link ?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_son.php?group_id=".$group_id."&sub_group_id=".$row_son['sub_project_id']."\"}'}>"._('Delete')."</a></td>";
						echo "<tr><td colspan='4'>"._('Commentary :')." <i>".$row_son['com']."</i>";
						echo '</td></tr>';
					}
				
			}
			//select  navigation link by father
			$sql_son = "SELECT group_id,group_name,unix_group_name,project_id, activated,link_type,com FROM groups,plugin_projects_hierarchy WHERE " .
							" (	groups.group_id = plugin_projects_hierarchy.project_id " .
					"AND plugin_projects_hierarchy.sub_project_id = ".$group_id." AND plugin_projects_hierarchy.link_type = 'navi') ";
			
			$res_son=db_query($sql_son)or die(db_error());
			if (!$res_son || db_numrows($res_son) < 1) {
				if($cpt_son == 1 ){
				echo '</table>';
				}
			}
			else {
				//display of sons 
				if($cpt_son != 1 ){
				echo _('Link list');
				echo '<table>';
				}
				
				$i = 0;
				while($row_son = db_fetch_array($res_son)){
						$i++;
						echo '<tr>';
						echo '<td>';
						//link to the project
						echo "<a href=\"../../projects/".$row_son['unix_group_name']."/\">".$row_son['group_name']."</a>";
						echo '</td>'; 
						
						echo '<td>';
						if($row_son[link_type] == 'navi'){
						echo _('Navigation link');
						}
						else {
						echo _('Share link');	
						}
						echo '</td>';
						
						echo '<td>';
						if($row_son[activated] == 'f'){
						//echo _('Waiting');
						echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to authorize this project ?')."\")){window.location.href=\"../../plugins/projects_hierarchy/wait_son.php?sub_group_id=".$group_id."&group_id=".$row_son['project_id']."\"}'}>"._('Authorize')."</a></td>";
						}
						else {
							print "<b>"._('Authorize')."</b>";
						}
						echo '</td>';
						echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link ?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_father.php?group_id=".$row_son['project_id']."&sub_group_id=".$group_id."\"}'}>"._('Delete')."</a></td>";
						echo "<tr><td colspan='4'>"._('Commentary of father :')." <i>".$row_son['com']."</i></td>";
						echo '</tr>';
					}
				echo '</table>';
			}
			
			//research allowing father
			$sql_father = "SELECT group_id,group_name,unix_group_name,project_id,com FROM groups,plugin_projects_hierarchy WHERE " .
					"		groups.group_id = plugin_projects_hierarchy.project_id " .
					"AND plugin_projects_hierarchy.sub_project_id = ".$group_id."".
					"		AND plugin_projects_hierarchy.activated = true AND plugin_projects_hierarchy.link_type = 'shar'";
					//print $sql_wait;
			$res_father=db_query($sql_father)or die(db_error());
			if (!$res_father || db_numrows($res_father) < 1) {
				
				}
				else {
					//display of the father of the current project
					echo '<table><tr><td colspan=\"2\">';	
					echo _('Project\'s parent');
					echo '</td></tr>';	
					while ($row_father = db_fetch_array($res_father)) {
					echo '<tr>';
					echo '<td>';
					echo "<a href=\"../../projects/".$row_father['unix_group_name']."/\">".$row_father['group_name']."</a>";
					echo '</td><td>';
					echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link ?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_father.php?sub_group_id=".$group_id."&group_id=".$row_father['group_id']."\"}'}>"._('Delete')."</a></td>";
					echo '</td></tr>';
					}
					echo '</table>';
				}
			
			  
			//research waiting fathers
			$sql_wait = "SELECT group_id,group_name,unix_group_name,project_id,link_type,com FROM groups,plugin_projects_hierarchy WHERE " .
					"		groups.group_id = plugin_projects_hierarchy.project_id " .
					"AND plugin_projects_hierarchy.sub_project_id = ".$group_id."".
					"		AND plugin_projects_hierarchy.activated = false AND plugin_projects_hierarchy.link_type = 'shar'";
					//print $sql_wait;
			$res_wait=db_query($sql_wait)or die(db_error());
			if (!$res_wait || db_numrows($res_wait) < 1) {
				
				}
				else { 
					//display of waiting fathers
					echo '<table><tr><td colspan=\"2\">';	
					echo _('Father waiting for validation');
					echo '</td></tr>';	
					while ($row_wait = db_fetch_array($res_wait)) {
					echo '<tr>';
					echo '<td>';
					echo "<a href=\"../../projects/".$row_wait['unix_group_name']."/\">".$row_wait['group_name']."</a>";
					echo '</td>';
					echo'<td>';
					echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to authorize this project ?')."\")){window.location.href=\"../../plugins/projects_hierarchy/wait_son.php?sub_group_id=".$group_id."&group_id=".$row_wait['group_id']."\"}'}>"._('Do you really want to authorize this project ?')."</a></td>";
					echo '</td><td>';
					echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link ?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_father.php?sub_group_id=".$group_id."&group_id=".$row_wait['group_id']."\"}'}>"._('Delete')."</a></td>";
					echo "</td><tr><td colspan='3'>"._('Commentary of father :')." <i>".$row_wait['com']."</i>";
					echo '</td></tr>';
					}
					echo '</table>';
				}				
				
			
		} 
		elseif ($hookname == "delete_link") {
			
			$sql = "DELETE FROM plugin_projects_hierarchy WHERE project_id = ".$params." OR sub_project_id = ".$params." ";
			$res_son=db_query($sql);
		} 
		
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
