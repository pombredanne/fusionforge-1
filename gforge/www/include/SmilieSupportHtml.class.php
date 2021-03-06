<?php
require_once $gfwww.'include/SmilieSupport.class.php';



class SmilieSupportHtml extends SmilieSupport {

	

	function SmilieSupportHtml(){
		$this->SmilieSupport();
				
	}
	

	
	
	
	//
	// Fill smiley templates (or just the variables) with smileys
	// Either in a window or inline
	//
	function displaySmiliesList($number_of_smilies_per_row=5)
	{
		global $SMILIES, $sys_default_domain;
		
		$num_smilies = count($SMILIES);
		
		if (!$num_smilies){
			return "";
		}
		
		reset($SMILIES);
		
		$inline_columns = 4;
		$inline_rows = $number_of_smilies_per_row;
		$window_columns = 8;
	
		$smilies_count = min(19, $num_smilies);
		$smilies_split_row = $inline_columns - 1;
	
		$s_colspan = 0;
		$row = 0;
		$col = 0;
		$first_time = 1;
		
		// we have to make sure that we won't display 2x the same smilie, so we will that array to list all the smilies that we already have display
		$smilies_displayed = array();
		
		// start smilies title	
		$return = '<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
			<tr align="center">
				<th height="25">'._('Smilies\'s list').'</th>
			</tr>
			<tr align="center">
				<td><table width="100" border="0" cellspacing="0" cellpadding="5">';
	

		while (list ($smilie_code, $smilie_info) = each ($SMILIES)) {
			if (empty($smilies_displayed[$smilie_info['image']])){
				$smilies_displayed[$smilie_info['image']] = 1;
				
				if (!$col && $first_time){
					$return .= '<tr align="center" valign="middle">' . "\n";
					$first_time = 0;
				}else if (!$col && !$first_time){
					$return .= '</tr><tr align="center" valign="middle">'. "\n";
				}
				
				$return .= '<td><a href="javascript:emoticon(\''.$smilie_code.'\')"><img src="' . $sys_images_url . '/images/smiles/' . $smilie_info['image'] . '" border="0" alt="'.$smilie_info['emoticon'].'" title="'.$smilie_info['emoticon'].'" /></a></td>'. "\n";
				
				$s_colspan = max($s_colspan, $col + 1);
				
				if ($col == $smilies_split_row){
					if ($row == $inline_rows - 1){
						break;
					}
					$col = 0;
					$row++;
				}
				else{
					$col++;
				}	
			}
		}
		
		$return .= '</tr></table>
				</td>
			</tr>
		</table>';
		
		return $return;
	}


}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
