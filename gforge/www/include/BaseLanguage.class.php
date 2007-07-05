<?php

/**
 * GForge Localization Facility
 *
 * Portions Copyright 1999-2000 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id$
 */

/*

	Tim Perdue, September 7, 2000

	Base class for adding multilingual support to SF.net

	Contains variables which can be overridden optionally by other
	language files.

	Base language is english - an english class will extend this one,
	but won't override anything

	As new languages are added, they can override what they wish, and
		as we extend our class, other languages can follow suit
		as they are translated without holding up our progress
*/

class BaseLanguage {

	/**
	 * associative array to hold the string value
	 *
	 * @var array $textArray
	 */
	var $textArray ;
	
	/**
	 * selected language
	 *
	 * @var string $lang
	 */
	var $lang;
	
	/**
	 * name of the current language
	 *
	 * @var string $name
	 */
	var $name;
	
	/**
	 * language id
	 *
	 * @var int $id
	 */
	var $id;
	
	/**
	 * language code
	 *
	 * @var string $code
	 */
	var $code;
	
	/**
	 * result set handle for supported languages
	 *
	 * @var resource $languagesRes
	 */
	var $languagesRes;
	
	/**
	 * array containing dependencies of the cache file
	 *
	 * @var array $cacheDependencies
	 */
	var $cacheDependencies = array();

	/**
	 * array containing the plugins which are loaded
	 *
	 * @var array $pluginDependencies
	 */
	var $pluginDependencies = array();

	/**
	 * Constructor
	 */
	function BaseLanguage() {
		// disable localization caching system if configuration is wrong
		if(!isset($GLOBALS['sys_localization_cache_path']) || !is_writable($GLOBALS['sys_localization_cache_path'])) {
			$GLOBALS['sys_localization_enable_caching'] = false;
		}
	}

	/**
	 * loadLanguageFile - load the localized strings from a file
	 *
	 * @param $fname file name
	 */
	function loadLanguageFile($fname) {
		
		$lines = file($fname, 1);

		$this->cacheDependencies[] = $fname;
		
		for( $i=0, $max = sizeof($lines); $i < $max; $i++) {
			$currentLine = $lines[$i];
			if (substr($currentLine, 0, 1) == '#') {
				continue;
			}
			// Language files can include others for defaults.
			// e.g. an English-Canada.tab file might "include English" first,
			// then override all those whacky American spellings.
			if (preg_match("/^include ([a-zA-Z]+)/", $currentLine, $matches)) {
				$dir = dirname($fname);
				$fileName = $dir.'/'.$matches[1].'.tab';
				if(file_exists($fileName)) {
					$this->loadLanguageFile($fileName);
				}
			} else {
				$line = explode("\t", $currentLine, 3);

				if($line && count($line) == 3) {
					$this->textArray[$line[0]][$line[1]] = chop($line[2]);
				}
			}
		}
	}

	/**
	 * loadLanguageID - load the selected language
	 *
	 * @param int $languageId language id
	 */
	function loadLanguageID($languageId) {
		$res = db_query('SELECT classname FROM supported_languages WHERE language_id=\''.$languageId.'\'');
		$this->loadLanguage(db_result($res, 0, 'classname'));
	}

	/**
	 * loadLanguage - load localized strings of the selected language
	 *
	 * @param string $lang language name
	 */	
	function loadLanguage($lang) {
		setupGettext ($lang) ;

		$cachePath = $this->getLocalizationCachePath($lang);

		if (function_exists('plugin_manager_get_object')) {
			$pluginManager =& plugin_manager_get_object();
			$pluginNames =& $pluginManager->getPlugins();
		} else {
			$pluginNames = array();
		}
		if($GLOBALS['sys_localization_enable_caching'] && file_exists($cachePath)) {
			$this->textArray =& $this->getLocalizationCache($cachePath);
			$this->lang = $lang ;
			if(!$GLOBALS['sys_localization_enable_timestamp_checking'] || !$this->isCacheOutdated($cachePath, $pluginNames)) {
				return;
			}
		}
		$this->dependencies = array();
		if($lang != 'Base') {
			$this->loadGlobalLanguage('Base');
		}
		$this->loadGlobalLanguage($lang);

		$this->pluginDependencies = array();
		if ( $lang != 'Base' ) {
			$this->loadPluginSpecificLanguage('Base', $pluginNames);
		}
		$this->loadPluginSpecificLanguage($lang, $pluginNames);

		$this->lang = $lang ;
		if($GLOBALS['sys_localization_enable_caching']) {
			$this->writeLocalizationCache($cachePath);
		}
	}

	/**
	 * loadGlobalLanguage - load localized strings of the selected language for the global site
	 *
	 * @param string $lang language name
	 */
	function loadGlobalLanguage($lang) {
		global $sys_theme, $sys_urlroot, $sys_etc_path;

		// Customization by language in $sys_urlroot/include/languages/<Language>.tab
		$fname = $sys_urlroot.'/include/languages/'.$lang.'.tab';
		$this->loadLanguageFile($fname) ;
		// Customization by theme by language in $sys_urlroot/themes/<theme_name>/<Language>.tab
		$ftname = $sys_urlroot.'/themes/'.$sys_theme.'/'.$lang.'.tab' ;
		if (file_exists ($ftname)) {
			$this->loadLanguageFile($ftname) ;
		}
		// Site-local customizations in $sys_etc_path/languages-local/<Language>.tab
		$fname = "$sys_etc_path/languages-local/$lang.tab" ;
		if (file_exists ($fname)) {
			$this->loadLanguageFile($fname) ;
		}
		// Site-local Customization by theme in $sys_etc_path/languages-local/<theme_name>/<Language>.tab
		$fltname = "$sys_etc_path/languages-local/$sys_theme/$lang.tab";
		if (file_exists ($fltname)) {
			$this->loadLanguageFile($fltname) ;
		}
	}

	/**
	 * loadPluginSpecificLanguage - load localized strings of the selected language for installed plugins
	 *
	 * @param string $lang language name
	 */
	function loadPluginSpecificLanguage($lang, $pluginNames) {
		if (is_array($pluginNames)) {
			foreach($pluginNames AS $pluginName) {
				$plugin =& plugin_get_object($pluginName);
				if($plugin && !$plugin->isError()) {
					$languagePath = $plugin->GetLanguagePath().$lang.'.tab';
					$specificLanguagePath = $plugin->GetSpecificLanguagePath().$lang.'.tab';
					if(file_exists($languagePath)) {
						$this->loadLanguageFile($languagePath);
					}
					if(file_exists($specificLanguagePath)) {
						$this->loadLanguageFile($specificLanguagePath);
					}
					$this->pluginDependencies[] = $pluginName;
				}
			}
		}
	}

	/**
	 * getText - get a localized string
	 *
	 * @param string $pagename name of the current page
	 * @param string $category key
	 * @param mixed $args array which will replace the $1, $2, etc before it is returned
	 */
	function getText($pagename, $category, $args = '') {
		if ($args) {
			for ($i=1, $max = sizeof($args)+1; $i <= $max; $i++) {
				$patterns[] = '/\$'.$i.'/';
			}
			$tstring = preg_replace($patterns, $args, $this->textArray[$pagename][$category]);
		} else {
			$tstring = $this->textArray[$pagename][$category];
		}
		return $tstring;
	}

	/**
	 * getLanguages - returns database result of supported languages
	 *
	 * @return resource supported languages
	 */
	function getLanguages() {
		if (!isset($this->languagesRes)) {
			$this->languagesRes = db_query('SELECT * FROM supported_languages ORDER BY name ASC');
		}
		return $this->languagesRes;
	}

	/**
	 * getLanguageId - returns the language id corresponding to the language name
	 *
	 * @return int language id
	 */
	function getLanguageId() {
		if (!$this->id) {
			$this->id = db_result(db_query("SELECT language_id FROM supported_languages WHERE classname='".$this->lang."'"), 0, 0) ;
		}
		return $this->id ;
	}

	/**
	 * getLanguageName - returns the language name corresponding to the language id
	 *
	 * @return string language name
	 */
	function getLanguageName() {
		if (!$this->name) {
			$id = $this->getLanguageId () ;
			$this->name = db_result(db_query("SELECT name FROM supported_languages WHERE language_id='$id'"), 0, 0) ;
		}
		return $this->name ;
	}

	/**
	 * getLanguageCode - returns the language code corresponding to the language id
	 *
	 * @return string language code
	 */
	function getLanguageCode() {
		if (!$this->code) {
			$id = $this->getLanguageId () ;
			$this->code = db_result(db_query("SELECT language_code FROM supported_languages WHERE language_id='$id'"), 0, 0) ;
		}
		return $this->code ;
	}

	/**
	 * getEncoding - returns the content encoding for the selected language
	 *
	 * @return string content encoding
	 */
	function getEncoding() {
		if(isset($this->textArray['conf']['content_encoding'])) {
			return $this->textArray['conf']['content_encoding'];
		}
		else {
			return '';
		}
	}

	/**
	 * getFont - returns the default font for selected language if exists
	 *
	 * @return string the default font
	 */
	function getFont() {
		if(isset($this->textArray['conf']['default_font'])) {
			return $this->textArray['conf']['default_font'];
		}
		else {
			return '';
		}
	}


	/* Localization Caching System */
	
	/**
	 * getLocalizationCache - get the localization strings array from cache
	 *
	 * @param string $path path of the cache file
	 * @return array an array containing localization string
	 */
	function & getLocalizationCache($cachePath) {
		$fp = fopen($cachePath, 'r');
		flock($fp, LOCK_SH);
		$array = unserialize(fread($fp, filesize($cachePath)));
		fclose($fp);
		$this->cacheDependencies =& $array['dependencies'];
		if(isset($array['pluginDependencies'])) {
			$this->pluginDependencies =& $array['pluginDependencies'];
		} else {
			$this->pluginDependencies = array();
		}
		return $array['text'];
	}

	/**
	 * writeLocalizationCache - caches the localization array to filesystem
	 *
	 * @param string $path path of the cache file
	 */
	function writeLocalizationCache($cachePath) {
		if(posix_getuid() != 0) {
			$content = array();
			$content['dependencies'] =& $this->cacheDependencies;
			$content['pluginDependencies'] =& $this->pluginDependencies;
			$content['text'] =& $this->textArray;
			$fp = fopen($cachePath, 'a');
		    if ($fp == false) {
            	echo "fopen(".$cachePath.") failed\n";
                return;
            }
			flock($fp, LOCK_EX);
			ftruncate($fp, 0);
			$content = serialize($content);
			fwrite($fp, $content, strlen($content));
			fclose($fp);
		}
	}

	/**
	 * getLocalizationCachePath - get the path to the localization cache for the selected language
	 *
	 * @param string $lang language
	 * @return string path to the cache file
	 */
	function getLocalizationCachePath($lang) {
		return $GLOBALS['sys_localization_cache_path'].$lang.'.cache';
	}

	/**
	 * isCacheOutdated - test if the localization cache is deprecated
	 *
	 * @param string $path path of the cache file
	 * @param array $pluginNames list of installed plugins
	 * @return boolean true if the cache is deprecated, false if it's still valid
	 */
	function isCacheOutdated($cachePath, $pluginNames) {
		if(count(array_diff($pluginNames, $this->pluginDependencies)) > 0) {
			return true;
		}
		if(!empty($this->cacheDependencies)) {
			$cacheTimestamp = filemtime($cachePath);
			$cacheDependencies =& $this->cacheDependencies;
			for($i = 0, $max = sizeof($cacheDependencies); $i < $max; $i++) {
				if(file_exists($cacheDependencies[$i]) && $cacheTimestamp < filemtime($cacheDependencies[$i])) {
					return true;
				}
			}
		}
		return false;
	}

}

/**
 * getLanguageClassName - get the classname for a language id
 * 
 * @param string $acceptedLanguages HTTP_ACCEPT_LANGUAGE header string
 * @return string the language class name.
 */
function getLanguageClassName($acceptedLanguages) {
	global $cookie_language_id;

	/*
		Determine which language file to use

		It depends on whether the user has set a cookie or not using
		the account page or the left-hand nav or how their browser is
		set or whether they are logged in or not

		if logged in, use language from users table
		else check for cookie and use that value if valid
		if no cookie check browser preference and use that language if valid
		else just use default language as configured for the installation
	*/

	if ($cookie_language_id) {
		$lang=$cookie_language_id;
		$res=db_query("select classname from supported_languages where language_id='".addslashes($lang)."'");
		if (!$res || db_numrows($res) < 1) {
			return false; // we will use default language
		} else {
			return db_result($res,0,'classname');
		}
	} else {
		$matches = array();
		preg_match_all('/([a-z]{2}(?:-[a-z]{2})?)(?:;q=([0-9\.]{1,4}))?/', $acceptedLanguages, $matches, PREG_SET_ORDER);
		$languages = array();
		
		$languagesCount = count($matches);
		
		if($languagesCount > 0) {
			$delta = 0.009/$languagesCount;
		
			for($i = 0, $max = count($matches); $i < $max; $i++) {
				$languageCode = $matches[$i][1];
				$quality = (!isset($matches[$i][2]) || empty($matches[$i][2])) ? '1' : $matches[$i][2];
				$languages[$languageCode] = $quality + $delta * ($languagesCount - $i);
			}

			arsort($languages, SORT_NUMERIC);
			$languages = array_keys($languages);

			for( $i=0, $max = sizeof($languages); $i < $max; $i++){
				$languageCode = $languages[$i];
				$res = db_query("select classname from supported_languages where language_code = '".addslashes($languageCode)."'");
				if (db_numrows($res) > 0) {
					return db_result($res,0,'classname');
				}
				// If that didn't work, check if we have sublanguage specifier
				// If so, try to strip it and look for for main language only
				if (strstr($languageCode, '-')) {
					$languageCode = substr($languageCode, 0, 2);
					$res = db_query("select classname from supported_languages where language_code = '".addslashes($languageCode)."'");
					if (db_numrows($res) > 0) {
						return db_result($res,0,'classname');
					}
				}
			}
		}
		return false; // we will use default language
	}
}


/**
 * setupGettext - Set up the gettext infrastructure
 * 
 *
 * @param string $lang language name
 */	
function setupGettext ($lang) {
	$langmap = array (
		'Basque'              => 'eu_ES',
		'Bulgarian'           => 'bg_BG',
		'Catalan'             => 'ca_ES',
		'Chinese'             => 'zh_TW',
		'Dutch'               => 'nl_NL',
		'English'             => 'en_US',
		'Esperanto'           => 'eo',
		'French'              => 'fr_FR',
		'German'              => 'de_DE',
		'Greek'               => 'el_GR',
		'Hebrew'              => 'he_IL',
		'Indonesian'          => 'id_ID',
		'Italian'             => 'it_IT',
		'Japanese'            => 'ja_JP',
		'Korean'              => 'ko_KR',
// Even the Unicode consortium doesn't support a Latin locale
		// 'Latin'            => 'la',
		'Norwegian'           => 'nb_NO',
		'Polish'              => 'pl_PL',
		'PortugueseBrazilian' => 'pt_BR',
		'Portuguese'          => 'pt_PT',
		'Russian'             => 'ru_RU',
		'SimplifiedChinese'   => 'zh_CN',
		'Spanish'             => 'es_ES',
		'Swedish'             => 'sv_SE',
		'Thai'                => 'th_TH',
		) ;
		
	$locale = $langmap[$lang].'.utf8';
	setlocale(LC_ALL, $locale);
	bindtextdomain('gforge', '/usr/share/locale/');
	textdomain('gforge');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>