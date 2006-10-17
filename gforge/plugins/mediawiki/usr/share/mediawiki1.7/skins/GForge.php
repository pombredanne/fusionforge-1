<?php
/**
 * GForge nouveau
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/** */
require_once('includes/SkinTemplate.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinGForge extends SkinTemplate {
	/** Using gforge. */
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'gforge';
		$this->stylename = 'gforge';
		$this->template  = 'GForgeTemplate';
	}
}

/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class GForgeTemplate extends QuickTemplate {
	/**
	 * Template filter callback for GForge skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
		$project=group_get_object_by_name(strtolower($this->data['skin']->mTitle->mTextform));
		if ($project) {
			$params['group']=$project->getID();
			$params['toptab']='mediawiki';
		}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
<head>
	<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
	<?php $this->html('headlinks') ?>
	<title><?php $this->text('pagetitle') ?></title>
	<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css?9"; /*]]>*/</style>
	<link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/common/commonPrint.css" />
	<!--[if lt IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE50Fixes.css";</style><![endif]-->
	<!--[if IE 5.5000]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE55Fixes.css";</style><![endif]-->
	<!--[if IE 6]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE60Fixes.css";</style><![endif]-->
	<!--[if IE 7]><style type="text/css">@import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/IE70Fixes.css?1";</style><![endif]-->
	<!--[if lt IE 7]><script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath') ?>/common/IEFixes.js"></script>
	<meta http-equiv="imagetoolbar" content="no" /><![endif]-->
	<script type="<?php $this->text('jsmimetype') ?>">var skin = '<?php $this->text('skinname')?>';var stylepath = '<?php $this->text('stylepath')?>';</script>
	<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js?1"><!-- wikibits js --></script>
		<?php	if($this->data['jsvarurl'  ]) { ?>
	<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl'  ) ?>"><!-- site js --></script>
		<?php	} ?>
		<?php	if($this->data['pagecss'   ]) { ?>
	<style type="text/css"><?php $this->html('pagecss'   ) ?></style>
		<?php	}
		if($this->data['usercss'   ]) { ?>
	<style type="text/css"><?php $this->html('usercss'   ) ?></style>
		<?php	}
		if($this->data['userjs'    ]) { ?>
	<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
		<?php	}
		if($this->data['userjsprev']) { ?>
	<script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
		<?php	}
		if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
	<!-- Head Scripts -->
		<?php $this->html('headscripts') ?>

	<!-- GFORGE Stylesheet BEGIN -->
	<?php
		/* check if a personalized css stylesheet exist, if yes include only
   		this stylesheet
   		new stylesheets should use the <themename>.css file
		*/
		$theme_cssfile=$GLOBALS['sys_themeroot'].$GLOBALS['sys_theme'].'/css/'.$GLOBALS['sys_theme'].'.css';
		if (file_exists($theme_cssfile)){
			echo '
	<link rel="stylesheet" type="text/css" href="/themes/'.$GLOBALS['sys_theme'].'/css/'.$GLOBALS['sys_theme'].'.css"/>
			';
		} else {
		/* if this is not our case, then include the compatibility stylesheet
   		that contains all removed styles from the code and check if a
   		custom stylesheet exists. 
   		Used for compatibility with existing stylesheets
		*/
		?>
	<link rel="stylesheet" type="text/css" href="/themes/css/gforge-compat.css" />
		<?php
		$theme_cssfile=$GLOBALS['sys_themeroot'].$GLOBALS['sys_theme'].'/css/theme.css';
		if (file_exists($theme_cssfile)){
			echo '
	<link rel="stylesheet" type="text/css" href="/themes/'.$GLOBALS['sys_theme'].'/css/theme.css" />
			';
		}
	}
?>
	<!-- GFORGE Stylesheet END -->
</head>

<body <?php if($this->data['body_ondblclick']) { ?>ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload'    ]) { ?>onload="<?php     $this->text('body_onload')     ?>"<?php } ?>
 class="<?php $this->text('nsclass') ?> <?php $this->text('dir') ?>">

	<!-- GFORGE BodyHeader BEGIN -->
<table border="0" width="100%" cellspacing="0" cellpadding="0" id="headertable" >
	<tr>
		<td><a href="/"><?php echo html_image('logo.png',198,52,array('border'=>'0')); ?></a></td>
		<td><?php echo $GLOBALS['HTML']->searchBox(); ?></td>
		<td align="right"><?php
			global $Language;
			if (session_loggedin()) {
				?>
				<a class="lnkutility" href="/account/logout.php?return_to=<?php echo $_SERVER['REQUEST_URI']; ?>"><?php echo $Language->getText('common','logout'); ?></a><br />
				<a class="lnkutility" href="/account/"><?php echo $Language->getText('common','myaccount'); ?></a>
				<?php
			} else {
				?>
				<b><a class="lnkutility" href="/account/login.php?return_to=<?php echo $_SERVER['REQUEST_URI']; ?>"><?php echo $Language->getText('common','login'); ?></a></b><br />
				<b><a class="lnkutility" href="/account/register.php"><?php echo $Language->getText('common','newaccount'); ?></a></b>
				<?php
			}
			echo $GLOBALS['HTML']->quickNav();

		?></td>
		<td>&nbsp;&nbsp;</td>
	</tr>

</table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" >
	<tr>
		<td>&nbsp;</td>
		<td colspan="3">
		<?php echo $GLOBALS['HTML']->outerTabs($params); ?>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="left" class="toptab" width="9"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>tabs/topleft.png" height="9" width="9" alt="" /></td>
		<td class="toptab" width="30"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>clear.png" width="30" height="1" alt="" /></td>
		<td class="toptab"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>clear.png" width="1" height="1" alt="" /></td>
		<td class="toptab" width="30"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>clear.png" width="30" height="1" alt="" /></td>
		<td align="right" class="toptab" width="9"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>tabs/topright.png" height="9" width="9" alt="" /></td>
	</tr>
	<tr>
		<!-- Outer body row -->
		<td class="toptab"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>clear.png" width="10" height="1" alt="" /></td>
		<td valign="top" width="99%" class="toptab" colspan="3">
			<!-- Inner Tabs / Shell -->
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<?php if (isset($params['group']) && $params['group']) { ?>
			<tr>
				<td>&nbsp;</td>
				<td>
				<?php echo $GLOBALS['HTML']->projectTabs($params['toptab'],$params['group']); ?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<?php } ?>
			<tr>
				<td align="left" class="projecttab" width="9"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>tabs/topleft-inner.png" height="9" width="9" alt="" /></td>
				<td class="projecttab" ><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>clear.png" width="1" height="1" alt="" /></td>
				<td align="right" class="projecttab"  width="9"><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>tabs/topright-inner.png" height="9" width="9" alt="" /></td>
			</tr>

			<tr>
				<td class="projecttab" ><img src="<?php echo $GLOBALS['HTML']->imgroot; ?>clear.png" width="10" height="1" alt="" /></td>
				<td valign="top" width="99%" class="projecttab" >
	<!-- GFORGE BodyHeader END -->

	<div id="globalWrapper">
		<div id="column-content">
	<div id="content">
		<a name="top" id="top"></a>
		<?php if($this->data['sitenotice']) { ?><div id="siteNotice"><?php $this->html('sitenotice') ?></div><?php } ?>
		<h1 class="firstHeading"><?php $this->data['displaytitle']!=""?$this->text('title'):$this->html('title') ?></h1>
		<div id="bodyContent">
			<h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
			<div id="contentSub"><?php $this->html('subtitle') ?></div>
			<?php if($this->data['undelete']) { ?><div id="contentSub2"><?php     $this->html('undelete') ?></div><?php } ?>
			<?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
			<?php if($this->data['showjumplinks']) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>
			<!-- start content -->
			<?php $this->html('bodytext') ?>
			<?php if($this->data['catlinks']) { ?><div id="catlinks"><?php       $this->html('catlinks') ?></div><?php } ?>
			<!-- end content -->
			<div class="visualClear"></div>
		</div>
	</div>
		</div>
		<div id="column-one">
	<div class="portlet" id="p-personal">
		<h5><?php $this->msg('personaltools') ?></h5>
		<div class="pBody">
			<ul>
<?php 			// XXXXXX CB don't display login as it's done by GForge
			$this->data['personal_urls']['login']=array();
			$this->data['personal_urls']['logout']=array();
			foreach($this->data['personal_urls'] as $key => $item) { ?>
				<li id="pt-<?php echo htmlspecialchars($key) ?>"<?php
					if ($item['active']) { ?> class="active"<?php } ?>><a href="<?php
				echo htmlspecialchars($item['href']) ?>"<?php
				if(!empty($item['class'])) { ?> class="<?php
				echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
				echo htmlspecialchars($item['text']) ?></a></li>
<?php			} ?>
			</ul>
		</div>
	</div>
	<div id="p-cactions" class="portlet">
		<h5><?php $this->msg('views') ?></h5>
		<ul>
<?php			foreach($this->data['content_actions'] as $key => $tab) { ?>
				 <li id="ca-<?php echo htmlspecialchars($key) ?>"<?php
				 	if($tab['class']) { ?> class="<?php echo htmlspecialchars($tab['class']) ?>"<?php }
				 ?>><a href="<?php echo htmlspecialchars($tab['href']) ?>"><?php
				 echo htmlspecialchars($tab['text']) ?></a></li>
<?php			 } ?>
		</ul>
	</div>
	<div class="portlet" id="p-logo">
		<a style="background-image: url(<?php $this->text('logopath') ?>);" <?php
			?>href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" <?php
			?>title="<?php $this->msg('mainpage') ?>"></a>
	</div>
	<script type="<?php $this->text('jsmimetype') ?>"> if (window.isMSIE55) fixalpha(); </script>
	<?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
	<div class='portlet' id='p-<?php echo htmlspecialchars($bar) ?>'>
		<h5><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?></h5>
		<div class='pBody'>
			<ul>
<?php 			foreach($cont as $key => $val) { ?>
				<li id="<?php echo htmlspecialchars($val['id']) ?>"<?php
					if ( $val['active'] ) { ?> class="active" <?php }
				?>><a href="<?php echo htmlspecialchars($val['href']) ?>"><?php echo htmlspecialchars($val['text']) ?></a></li>
<?php			} ?>
			</ul>
		</div>
	</div>
	<?php } ?>
	<div id="p-search" class="portlet">
		<h5><label for="searchInput"><?php $this->msg('search') ?></label></h5>
		<div id="searchBody" class="pBody">
			<form action="<?php $this->text('searchaction') ?>" id="searchform">
			<div>
				<input id="searchInput" name="search" type="text" <?php
					if($this->haveMsg('accesskey-search')) {
						?>accesskey="<?php $this->msg('accesskey-search') ?>"<?php }
					if( isset( $this->data['search'] ) ) {
						?> value="<?php $this->text('search') ?>"<?php } ?> />
				<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('go') ?>" />&nbsp;
				<input type='submit' name="fulltext" class="searchButton" value="<?php $this->msg('search') ?>" />
			</div>
			</form>
		</div>
	</div>
	<div class="portlet" id="p-tbx">
		<h5><?php $this->msg('toolbox') ?></h5>
		<div class="pBody">
			<ul>
<?php
		if($this->data['notspecialpage']) { ?>
				<li id="t-whatlinkshere"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
				?>"><?php $this->msg('whatlinkshere') ?></a></li>
<?php
			if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
				<li id="t-recentchangeslinked"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
				?>"><?php $this->msg('recentchangeslinked') ?></a></li>
<?php 		}
		}
		if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
			<li id="t-trackbacklink"><a href="<?php
				echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
				?>"><?php $this->msg('trackbacklink') ?></a></li>
<?php 	}
		if($this->data['feeds']) { ?>
			<li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
					?><span id="feed-<?php echo htmlspecialchars($key) ?>"><a href="<?php
					echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
					<?php } ?></li><?php
		}

		foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {

			if($this->data['nav_urls'][$special]) {
				?><li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
				?>"><?php $this->msg($special) ?></a></li>
<?php		}
		}

		if(!empty($this->data['nav_urls']['print']['href'])) { ?>
				<li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
				?>"><?php $this->msg('printableversion') ?></a></li><?php
		}

		if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
				<li id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
				?>"><?php $this->msg('permalink') ?></a></li><?php
		} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
				<li id="t-ispermalink"><?php $this->msg('permalink') ?></li><?php
		}

		wfRunHooks( 'GForgeTemplateToolboxEnd', array( &$this ) );
?>
			</ul>
		</div>
	</div>
<?php
		if( $this->data['language_urls'] ) { ?>
	<div id="p-lang" class="portlet">
		<h5><?php $this->msg('otherlanguages') ?></h5>
		<div class="pBody">
			<ul>
<?php		foreach($this->data['language_urls'] as $langlink) { ?>
				<li class="<?php echo htmlspecialchars($langlink['class'])?>"><?php
				?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></li>
<?php		} ?>
			</ul>
		</div>
	</div>
<?php	} ?>
		</div><!-- end of the left (by default at least) column -->
			<div class="visualClear"></div>
			<div id="footer">
<?php
		if($this->data['poweredbyico']) { ?>
				<div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div>
<?php 	}
		if($this->data['copyrightico']) { ?>
				<div id="f-copyrightico"><?php $this->html('copyrightico') ?></div>
<?php	}

		// Generate additional footer links
?>
			<ul id="f-list">
<?php
		$footerlinks = array(
			'lastmod', 'viewcount', 'numberofwatchingusers', 'credits', 'copyright',
			'privacy', 'about', 'disclaimer', 'tagline',
		);
		foreach( $footerlinks as $aLink ) {
			if( $this->data[$aLink] ) {
?>				<li id="<?php echo$aLink?>"><?php $this->html($aLink) ?></li>
<?php 		}
		}
?>
			</ul>
		</div>
	<script type="text/javascript"> if (window.runOnloadHook) runOnloadHook();</script>
</div>
<?php $this->html('reporttime') ?>

<?php $GLOBALS['HTML']->footer($params); ?>
<?php
	wfRestoreWarnings();
	} // end of execute() method
} // end of class
?>