<?php
require_once $gfcommon.'include/Error.class.php';
require_once $gfwww.'include/SmilieSupportHtml.class.php';
require_once $gfwww.'include/BBCodeSupportHtml.class.php';
require_once $gfwww.'include/HTMLSupport.class.php';


class TextSupport extends Error {
	/**
	 * The BBCodeSupportHtml object.
	 *
	 * @var	 object  $BBCodeSupportHtml.
	 */
	var $BBCodeSupportHtml;
	
	/**
	 * The SmilieSupportHtml object.
	 *
	 * @var	 object  $SmilieSupportHtml.
	 */
	var $SmilieSupportHtml;
	
	/**
	 * The HTMLSupport object.
	 *
	 * @var	 object  $HTMLSupport.
	 */
	var $HTMLSupport;
	

	// must be called after HTML was initialized
	function TextSupport(){
		$this->Error();
		
		$BBCodeSupportHtml = new BBCodeSupportHtml();
		if (!$BBCodeSupportHtml || !is_object($BBCodeSupportHtml)) {
			$this->setError('TextSupport:: No Valid BBCodeSupportHtml Object');
			return false;
		}
		if ($BBCodeSupportHtml->isError()) {
			$this->setError('TextSupport:: '.$BBCodeSupportHtml->getErrorMessage());
			return false;
		}
		$this->BBCodeSupportHtml =& $BBCodeSupportHtml;
		
		
		
		$SmilieSupportHtml = new SmilieSupportHtml();		
		if (!$SmilieSupportHtml || !is_object($SmilieSupportHtml)) {
			$this->setError('TextSupport:: No Valid BBCodeSupportHtml Object');
			return false;
		}
		if ($SmilieSupportHtml->isError()) {
			$this->setError('TextSupport:: '.$SmilieSupportHtml->getErrorMessage());
			return false;
		}
		$this->SmilieSupportHtml =& $SmilieSupportHtml;
		
		
		$HTMLSupport = new HTMLSupport();		
		if (!$HTMLSupport || !is_object($HTMLSupport)) {
			$this->setError('TextSupport:: No Valid HTMLSupport Object');
			return false;
		}
		if ($HTMLSupport->isError()) {
			$this->setError('TextSupport:: '.$HTMLSupport->getErrorMessage());
			return false;
		}
		$this->HTMLSupport =& $HTMLSupport;	
			
		
	}
	
	// this function prepare the text to be inserted into the db	
	// strip html will do a specialchars parse in the text
	// make_clickable will parse the text and transform www.aa.aa into a <a... tag
	// returns bbcode_uid, it's the code that was used to code the text with bbcode support, we will need that when we want to display the text with bbcode on	
	function prepareText(&$text, $make_clickable=1, $strip_html=1, $smilie_on=1, $bbcode_on=1){
			$text = $this->HTMLSupport->prepareText($text, $strip_html);
		if ($smilie_on){
			$text = $this->SmilieSupportHtml->prepareText($text);
		}
		if ($bbcode_on){
			$bbcode_uid = $this->BBCodeSupportHtml->makeBBCodeUID();	
			$text = $this->BBCodeSupportHtml->prepareText($text, $bbcode_uid);
		}
		if ($make_clickable){
			//$text = $this->makeClickable($text);
		}
		
		return $bbcode_uid;
	}
	
	
	// strip_html is also defined here so that we could for exemple store the raw data in the db and then displaying the text
	// with a strip html function on it. 
	function displayText($text, $make_clickable=1, $smilie_on=1, $bbcode_on=1, $bbcode_uid=0,$strip_html=1){
		$text = $this->HTMLSupport->prepareText($text, $strip_html);
		if ($smilie_on){
			// parse the text var and convert all the smilies into nice images links
			$text = $this->SmilieSupportHtml->displayText($text);
		}
		if ($bbcode_on){
			$text = $this->BBCodeSupportHtml->displayText($text, $bbcode_uid);
		}
		
		$text = str_replace("\n", "\n<br />\n", $text);
		
		if ($make_clickable){
			$text = $this->makeClickable($text);
		}
		
		return $text;		
	}
	
	function displayBBCodeHelpTools(){
		echo $this->BBCodeSupportHtml->displayBBCodeHelpTools();
	}
	
	function displayTextField($text_field_name, $default_value=''){
		echo $this->BBCodeSupportHtml->displayTextField($text_field_name, $default_value);
	}
	
	function displaySmiliesList($number_of_smilies_per_row=5){
		echo $this->SmilieSupportHtml->displaySmiliesList();
	}
	
	
	function displayForm($form_name, $text_field_name, $action, $enctype){
		echo $this->BBCodeSupportHtml->displayForm($form_name, $text_field_name, $action, $enctype);
	}
	
	
	
	/**
	 * Rewritten by Nathan Codding - Feb 6, 2001.
	 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
	 * 	to that URL
	 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
	 * 	to http://www.xxxx.yyyy[/zzzz]
	 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
	 *		to that email address
	 * - Only matches these 2 patterns either after a space, or at the beginning of a line
	 *
	 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
	 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
	 */
	function makeClickable($text){
	
		// pad it with a space so we can match things at the start of the 1st line.
		$ret = " " . $text;
	
		// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
		// xxxx can only be alpha characters.
		// yyyy is anything up to the first space, newline, or comma.
		$ret = preg_replace("#([\n ])([a-z]+?)://([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", $ret);
	
		// matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
		// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
		// yyyy contains either alphanum, "-", or "."
		// zzzz is optional.. will contain everything up to the first space, newline, or comma.
		// This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
		// This is to keep it from getting annoying and matching stuff that's not meant to be a link.
		$ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]*)?)#i", "\\1<a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a>", $ret);
	
		// matches an email@domain type address at the start of a line, or after a space.
		// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
		$ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
	
		// Remove our padding..
		$ret = substr($ret, 1);
	
		return($ret);
	}
	
	/**
	 * Nathan Codding - Feb 6, 2001
	 * Reverses the effects of makeClickable(), for use in editpost.
	 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
	 *
	 */
	function undoMakeClickable($text){
		$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
		$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);
	
		return $text;
	
	}
	
	
	
	

}

?>
