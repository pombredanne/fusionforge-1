<?php 
require_once $gfwww.'include/BBCodeSupport.class.php';

	
class BBCodeSupportHtml extends BBCodeSupport {
	
	var $javascript_ready = 0;
	
	function BBCodeSupportHtml(){		
		$this->BBCodeSupport();
	}
	
	
	function prepareJavascript($form_name, $text_field_name){
		$javascript_ready = 1;
		?>
				
		<script language="JavaScript" type="text/javascript">
		<!--
		// bbCode control by
		// subBlue design
		// www.subBlue.com
		
		// Startup variables
		var imageTag = false;
		var theSelection = false;
		
		// Check for Browser & Platform for PC & IE specific bits
		// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
		var clientPC = navigator.userAgent.toLowerCase(); // Get client info
		var clientVer = parseInt(navigator.appVersion); // Get browser version
		
		var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
		var is_nav  = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
						&& (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
						&& (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
		
		var is_win   = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
		var is_mac    = (clientPC.indexOf("mac")!=-1);
		
		
		// Helpline messages
		b_help = "<?php echo _('Bold text: [b]text[/b]  (alt+b)'); ?>";
		i_help = "<?php echo _('Italic text: [i]text[/i]  (alt+i)'); ?>";
		u_help = "<?php echo _('Underline text: [u]text[/u]  (alt+u)'); ?>";
		q_help = "<?php echo _('Quote text : [quote]text[/quote]  (alt+q)'); ?>";
		c_help = "<?php echo _('Code display: [code]code[/code]  (alt+c)'); ?>";
		l_help = "<?php echo _('List: [list]text[/list] (alt+l)'); ?>";
		o_help = "<?php echo _('Ordered list: [list=]text[/list]  (alt+o)'); ?>";
		p_help = "<?php echo _('Insert image: [img]http://image_url[/img]  (alt+p)'); ?>";
		w_help = "<?php echo _('Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)'); ?>";
		a_help = "<?php echo _('Close all open bbCode tags'); ?>";
		s_help = "<?php echo _('Font color: [color=red]text[/color]  Tip: you can also use color=#FF0000'); ?>";
		f_help = "<?php echo _('Font size: [size=x-small]small text[/size]'); ?>";
		k_help = "<?php echo _('Link to Task: [task]Forumid:Subproject:Taskid:TaskName[/task]   (alt+k)'); ?>";
		h_help = "<?php echo _('Link to Artifact: [artifact]ArtifactID[/artifact]   (alt+h)'); ?>";
		
		// Define the bbCode tags
		bbcode = new Array();
		bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[code]','[/code]','[list]','[/list]','[list=]','[/list]','[img]','[/img]','[url]','[/url]','dummy','dummy','dummy','dummy','[task]','[/task]','[artifact]','[/artifact]');
		imageTag = false;
		
		// Shows the help messages in the helpline window
		function helpline(help) {
			document.<?php echo $form_name; ?>.helpbox.value = eval(help + "_help");
		}
		
		
		// Replacement for arrayname.length property
		function getarraysize(thearray) {
			for (i = 0; i < thearray.length; i++) {
				if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
					return i;
				}
			return thearray.length;
		}
		
		// Replacement for arrayname.push(value) not implemented in IE until version 5.5
		// Appends element to the array
		function arraypush(thearray,value) {
			thearray[ getarraysize(thearray) ] = value;
		}
		
		// Replacement for arrayname.pop() not implemented in IE until version 5.5
		// Removes and returns the last element of an array
		function arraypop(thearray) {
			thearraysize = getarraysize(thearray);
			retval = thearray[thearraysize - 1];
			delete thearray[thearraysize - 1];
			return retval;
		}
		
		function emoticon(text) {
			text = ' ' + text + ' ';
			if (document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.createTextRange && document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.caretPos) {
				var caretPos = document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.caretPos;
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
			} else {
			document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.value  += text;
			document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
			}
		}
		
		function bbfontstyle(bbopen, bbclose) {
			if ((clientVer >= 4) && is_ie && is_win) {
				theSelection = document.selection.createRange().text;
				if (!theSelection) {
					document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.value += bbopen + bbclose;
					document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
					return;
				}
				document.selection.createRange().text = bbopen + theSelection + bbclose;
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
				return;
			} else {
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.value += bbopen + bbclose;
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
				return;
			}
			storeCaret(document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>);
		}
		
		
		function bbstyle(bbnumber) {
		
			donotinsert = false;
			theSelection = false;
			bblast = 0;
		
			if (bbnumber == -1) { // Close all open tags & default button names
				while (bbcode[0]) {
					butnumber = arraypop(bbcode) - 1;
					document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.value += bbtags[butnumber + 1];
					buttext = eval('document.<?php echo $form_name; ?>.addbbcode' + butnumber + '.value');
					eval('document.<?php echo $form_name; ?>.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
				}
				imageTag = false; // All tags are closed including image tags :D
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
				return;
			}
		
			if ((clientVer >= 4) && is_ie && is_win)
				theSelection = document.selection.createRange().text; // Get text selection
				
			if (theSelection) {
				// Add tags around selection
				document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
				theSelection = '';
				return;
			}
			
			// Find last occurance of an open tag the same as the one just clicked
			for (i = 0; i < bbcode.length; i++) {
				if (bbcode[i] == bbnumber+1) {
					bblast = i;
					donotinsert = true;
				}
			}
		
			if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
				while (bbcode[bblast]) {
						butnumber = arraypop(bbcode) - 1;
						document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.value += bbtags[butnumber + 1];
						buttext = eval('document.<?php echo $form_name; ?>.addbbcode' + butnumber + '.value');
						eval('document.<?php echo $form_name; ?>.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
						imageTag = false;
					}
					document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
					return;
			} else { // Open tags
			
				if (imageTag && (bbnumber != 14)) {		// Close image tag before adding another
					document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.value += bbtags[15];
					lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
					document.<?php echo $form_name; ?>.addbbcode14.value = "Img";	// Return button back to normal state
					imageTag = false;
				}
				
				// Open tag
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.value += bbtags[bbnumber];
				if ((bbnumber == 14) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
				arraypush(bbcode,bbnumber+1);
				eval('document.<?php echo $form_name; ?>.addbbcode'+bbnumber+'.value += "*"');
				document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>.focus();
				return;
			}
			storeCaret(document.<?php echo $form_name; ?>.<?php echo $text_field_name; ?>);
		}
		
		// Insert at Claret position. Code from
		// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
		function storeCaret(textEl) {
			if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
		}
		
		//-->
		</script>
		<?php		
	}
	
	function displayForm($form_name, $text_field_name, $action, $enctype=''){
		if (!$this->javascript_ready){
			$this->prepareJavascript($form_name, $text_field_name);	
		}
		?>
		<form action="<?php echo $action . "\" " . $enctype; ?>" method="post" name="<?php echo $form_name; ?>">
		<?php
	}
	
	function displayBBCodeHelpTools(){
		?>
		
		<table width="450" border="0" cellspacing="0" cellpadding="2">
		  <tr align="center" valign="middle"> 
			<td>
			  <input type="button" accesskey="b" name="addbbcode0" value=" B " style="font-weight:bold; width: 30px" onClick="bbstyle(0)" onMouseOver="helpline('b')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="i" name="addbbcode2" value=" i " style="font-style:italic; width: 30px" onClick="bbstyle(2)" onMouseOver="helpline('i')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="u" name="addbbcode4" value=" u " style="text-decoration: underline; width: 30px" onClick="bbstyle(4)" onMouseOver="helpline('u')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="q" name="addbbcode6" value="Quote" style="width: 50px" onClick="bbstyle(6)" onMouseOver="helpline('q')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="c" name="addbbcode8" value="Code" style="width: 40px" onClick="bbstyle(8)" onMouseOver="helpline('c')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="l" name="addbbcode10" value="List" style="width: 40px" onClick="bbstyle(10)" onMouseOver="helpline('l')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="o" name="addbbcode12" value="List=" style="width: 40px" onClick="bbstyle(12)" onMouseOver="helpline('o')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="p" name="addbbcode14" value="Img" style="width: 40px"  onClick="bbstyle(14)" onMouseOver="helpline('p')" />
			  </span></td>
			<td>
			  <input type="button" accesskey="w" name="addbbcode16" value="URL" style="text-decoration: underline; width: 40px" onClick="bbstyle(16)" onMouseOver="helpline('w')" />
			  </span></td>
			 <td>
			  <input type="button" accesskey="k" name="addbbcode22" value="Task" style="width: 40px"  onClick="bbstyle(22)" onMouseOver="helpline('k')" />
			  </span></td>
			  <td>
			  <input type="button" accesskey="h" name="addbbcode24" value="Artifact" style="width: 60px"  onClick="bbstyle(24)" onMouseOver="helpline('h')" />
			  </span></td>
			<td>
		  </tr>
		  <tr> 
			<td colspan="9"> 
			  <table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr> 
				  <td>&nbsp;<?php echo _('Font colour'); ?>: 
					<select name="addbbcode18" onChange="bbfontstyle('[color=' + this.form.addbbcode18.options[this.form.addbbcode18.selectedIndex].value + ']', '[/color]')" onMouseOver="helpline('s')">
					  <option style="color:black; background-color: #FFFFFF " value="default" ><?php echo _('Default'); ?></option>
					  <option style="color:darkred; background-color: #DEE3E7" value="darkred" ><?php echo _('Dark Red'); ?></option>
					  <option style="color:red; background-color: #DEE3E7" value="red" ><?php echo _('Red'); ?></option>
					  <option style="color:orange; background-color: #DEE3E7" value="orange" ><?php echo _('Orange'); ?></option>
					  <option style="color:brown; background-color: #DEE3E7" value="brown" ><?php echo _('Brown'); ?></option>
					  <option style="color:yellow; background-color: #DEE3E7" value="yellow" ><?php echo _('Yellow'); ?></option>
					  <option style="color:green; background-color: #DEE3E7" value="green" ><?php echo _('Green'); ?></option>
					  <option style="color:olive; background-color: #DEE3E7" value="olive" ><?php echo _('Olive'); ?></option>
					  <option style="color:cyan; background-color: #DEE3E7" value="cyan" ><?php echo _('Cyan'); ?></option>
					  <option style="color:blue; background-color: #DEE3E7" value="blue" ><?php echo _('Blue'); ?></option>
					  <option style="color:darkblue; background-color: #DEE3E7" value="darkblue" ><?php echo _('Dark Blue'); ?></option>
					  <option style="color:indigo; background-color: #DEE3E7" value="indigo" ><?php echo _('Indigo'); ?></option>
					  <option style="color:violet; background-color: #DEE3E7" value="violet" ><?php echo _('Violet'); ?></option>
					  <option style="color:white; background-color: #DEE3E7" value="white" ><?php echo _('White'); ?></option>
					  <option style="color:black; background-color: #DEE3E7" value="black" ><?php echo _('Black'); ?></option>
					</select> &nbsp;<?php echo _('Font size'); ?>:<select name="addbbcode20" onChange="bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]')" onMouseOver="helpline('f')">
					  <option value="7" ><?php echo _('Tiny'); ?></option>
					  <option value="9" ><?php echo _('Small'); ?></option>
					  <option value="12" selected ><?php echo _('Normal'); ?></option>
					  <option value="18" ><?php echo _('Large'); ?></option>
					  <option  value="24" ><?php echo _('Huge'); ?></option>
					</select>
					</span></td>
				  <td nowrap="nowrap" align="right"><span class="gensmall"><a href="javascript:bbstyle(-1)"  onMouseOver="helpline('a')"><?php echo _('Close Tags'); ?></a></span></td>
				</tr>
			  </table>
			</td>
		  </tr>
		  <tr> 
			<td colspan="9"> 
			  <input type="text" name="helpbox" size="45" maxlength="100" style="width:450px; font-size:10px" value="<?php echo _('Tip: Styles can be applied quickly to selected text'); ?>" />
			  </td>
		  </tr>
	  </table>
	  
		
		<?php
	}
	
	function displayTextField($text_field_name, $default_value=''){
		?>
		<textarea name="<?php echo $text_field_name; ?>" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="6" class="post" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);"><?php echo $default_value; ?></textarea>
		<?php
	}


}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
