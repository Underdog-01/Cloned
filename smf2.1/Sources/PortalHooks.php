<?php
/*
	<id>ChenZhen:EhPortal</id>
	<name>EhPortal</name>
	<version>1.2</version>
*/
/*
 * EhPortal is a ported version of SimplePortal 2.3.6 (Copyright (c) 2014 SimplePortal Team.)
 * This software is in no way affiliated with the original developers
 * EhPortal Portal ~ Copyright (c) 2018 WebDev (http://web-develop.ca)
 * Distributed under the BSD 2-Clause License (http://opensource.org/licenses/BSD-2-Clause)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_user_info()
		// !!!

	void sportal_mark_read_button()
		// !!!

	void sportal_display_message_list(&$messages, &$posters)
		// !!!

	void sportal_buffer($buffer)
		// !!!

	void sportal(&$message, &$smileys, &$cache_id, &$parse_tags)
		// !!!

	void sportal_pre_parsebbc()
		// !!!

	void sportal_helpadmin()
		// !!!

	void sportal_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
		// !!!

	void sportal_admin_areas(&$admin_areas)
		// !!!

	void sportal_admin_search(&$language_files, &$include_files, &$settings_search)
		// !!!

	void sportal_actions(&$actionArray)
		// !!!

	void sportal_initialize(&$no_stat_actions)
		// !!!

	void sportal_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
		// !!!

	void sportal_getconst($const)
		// !!!

*/

function sportal_user_info()
{
	global $sourcedir, $sp_standalone, $modSettings, $smcFunc, $user_info, $language, $txt;

	// we can catch SSI when defined!
	if (sportal_getconst('SMF') == 'SSI')
	{
		require_once($sourcedir . '/Subs-Portal.php');
		sportal_init();
	}
	else
	{
		// Maybe we have a portal specific theme?
		if (!isset($_GET['action']) && !isset($_GET['board']) && !isset($_GET['topic']) && ($modSettings['sp_portal_mode'] == 1 || !empty($sp_standalone)) && !empty($modSettings['portaltheme']))
		{
			$user_info['theme'] = (int)$modSettings['portaltheme'];
			unset($_REQUEST['theme']);

			// SMF doesn't seem to be liking -1...
			if ($user_info['theme'] == -1 && !empty($_SESSION['id_theme']))
			{
				unset($_SESSION['id_theme']);
				$user_info['theme'] = $modSettings['theme_guests'];
			}
		}

		// load our language here
		loadLanguage('SPortal', '', false);
		$cur_language = isset($user_info['language']) ? $user_info['language'] : $language;
		if ($cur_language !== 'english')
			loadLanguage('SPortal', 'english', false);

		// load EhPortal profile settings and add them to $user_info
		if (!empty($user_info['id']) && $user_info['id'] > 0)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, ehportal_ignore_members, ehportal_enable_audio
				FROM {db_prefix}sp_profiles
				WHERE id_member = {int:member}',
				array(
					'member' => $user_info['id'],
				)
			);

			$updates = array('ehportal_ignore_members', 'ehportal_enable_audio');
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				foreach ($updates as $update)
					$user_info[$update] = !empty($row[$update]) ? $row[$update] : 0;
			}

			$smcFunc['db_free_result']($request);
		}
	}

}

function sportal_mark_read_button()
{
	global $context;

	if ((!empty($_GET)) && $_GET !== array('action' => 'forum'))
		$context['robot_no_index'] = true;
	else
		$context['robot_no_index'] = false;
}

function sportal_display_message_list(&$messages, &$posters)
{
	global $context, $smcFunc;

	// Is this already an article?
	$request = $smcFunc['db_query']('','
		SELECT id_message
		FROM {db_prefix}sp_articles
		WHERE id_message = {int:message}',
		array(
			'message' => $context['topic_first_message'],
		)
	);
	list ($context['topic_is_article']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
}

function sportal_buffer($buffer)
{
	global $modSettings, $scripturl, $context;
	@ini_set('memory_limit', '128M');

	if (function_exists('sp_query_string'))
		$buffer = sp_query_string($buffer);

	// This should work even in 4.2.x, just not CGI without cgi.fix_pathinfo.
	if (!empty($modSettings['queryless_urls']) && (!$context['server']['is_cgi'] || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && ($context['server']['is_apache'] || $context['server']['is_lighttpd'] || $context['server']['is_litespeed']))
	{
		// Let's do something special for session ids!
		if (sportal_getconst('SID'))
			$buffer = preg_replace_callback('~"' . preg_quote($scripturl, '/') . '\?(?:' . SID . '(?:;|&|&amp;))((?:page)=[^#"]+?)(#[^"]*?)?"~', function ($m)
			{
				global $scripturl; return '"' . $scripturl . "/" . strtr("$m[1]", '&;=', '//,') . ".html?" . SID . (isset($m[2]) ? $m[2] : "") . '"';
			}, $buffer);
		else
			$buffer = preg_replace_callback('~"' . preg_quote($scripturl, '/') . '\?((?:page)=[^#"]+?)(#[^"]*?)?"~', function ($m)
			{
				global $scripturl; return '"' . $scripturl . '/' . strtr("$m[1]", '&;=', '//,') . '.html' . (isset($m[2]) ? $m[2] : "") . '"';
			}, $buffer );
	}

	return $buffer;
}

function sportal(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	static $default_disabled, $parse_tag_cache, $bbc_codes;

	if (!empty($parse_tags) && !empty($temp_bbc))
	{
		$bbc_codes = $temp_bbc;
		$temp_bbc = array();
	}

}

function sportal_pre_parsebbc()
{
	return;
}

function sportal_helpadmin()
{
	global $txt, $helptxt;

	// Load the EhPortal Help file.
	loadLanguage('SPortalHelp', sp_languageSelect('SPortalHelp'));
}

function sportal_prepare_db_settings(&$config_vars)
{
	global $txt, $helptxt;

	// Load the EhPortal Help file.
	loadLanguage('SPortalHelp', sp_languageSelect('SPortalHelp'));
}

function sportal_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);

	// Key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}

	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position, true),
			$insert,
			array_slice($input, $position, null, true)
		);
}

function sportal_admin_areas(&$admin_areas)
{
	global $context, $modSettings, $scripturl, $txt;
	loadLanguage('SPortal');

	sportal_array_insert($admin_areas, 'members',
		array(
			'portal' => array(
				'title' => $txt['sp-adminCatTitle'],
				'permission' => array('sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox'),
				'areas' => array(
					'portalconfig' => array(
						'label' => $txt['sp-adminConfiguration'],
						'file' => 'PortalAdminMain.php',
						'function' => 'sportal_admin_config_main',
						'icon' => 'configuration.png',
						'permission' => array('sp_admin', 'sp_manage_settings'),
						'subsections' => array(
							'information' => array($txt['sp-info_title']),
							'generalsettings' => array($txt['sp-adminGeneralSettingsName']),
							'blocksettings' => array($txt['sp-adminBlockSettingsName']),
							'articlesettings' => array($txt['sp-adminArticleSettingsName']),
						),
					),
					'portalblocks' => array(
						'label' => $txt['sp-blocksBlocks'],
						'file' => 'PortalAdminBlocks.php',
						'function' => 'sportal_admin_blocks_main',
						'icon' => 'blocks.png',
						'permission' => array('sp_admin', 'sp_manage_blocks'),
						'subsections' => array(
							'list' => array($txt['sp-adminBlockListName']),
							'add' => array($txt['sp-adminBlockAddName']),
							'header' => array($txt['sp-positionHeader']),
							'left' => array($txt['sp-positionLeft']),
							'top' => array($txt['sp-positionTop']),
							'bottom' => array($txt['sp-positionBottom']),
							'right' => array($txt['sp-positionRight']),
							'footer' => array($txt['sp-positionFooter']),
						),
					),
					'portalarticles' => array(
						'label' => $txt['sp-adminColumnArticles'],
						'file' => 'PortalAdminArticles.php',
						'function' => 'sportal_admin_articles_main',
						'icon' => 'articles.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
						'subsections' => array(
							'articles' => array($txt['sp-adminArticleListName']),
							'addarticle' => array($txt['sp-adminArticleAddName']),
							'categories' => array($txt['sp-adminCategoryListName']),
							'addcategory' => array($txt['sp-adminCategoryAddName']),
						),
					),
					'portalpages' => array(
						'label' => $txt['sp_admin_pages_title'],
						'file' => 'PortalAdminPages.php',
						'function' => 'sportal_admin_pages_main',
						'icon' => 'pages.png',
						'permission' => array('sp_admin', 'sp_manage_pages'),
						'subsections' => array(
							'list' => array($txt['sp_admin_pages_list']),
							'add' => array($txt['sp_admin_pages_add']),
						),
					),
					'portalshoutbox' => array(
						'label' => $txt['sp_admin_shoutbox_title'],
						'file' => 'PortalAdminShoutbox.php',
						'function' => 'sportal_admin_shoutbox_main',
						'icon' => 'shoutbox.png',
						'permission' => array('sp_admin', 'sp_manage_shoutbox'),
						'subsections' => array(
							'list' => array($txt['sp_admin_shoutbox_list']),
							'audio' => array($txt['sp_admin_shoutbox_audio']),
							'add' => array($txt['sp_admin_shoutbox_add']),
						),
					),
				),
			),
		),
		'before',
		false
	);
}

function sportal_admin_search(&$language_files, &$include_files, &$settings_search)
{
	$include_files[] = 'PortalAdminMain';
	$settings_search = array_merge($settings_search, array(
		array('sportal_admin_general_settings', 'area=portalconfig;sa=generalsettings'),
		array('sportal_admin_block_settings', 'area=portalconfig;sa=blocksettings'),
		array('sportal_admin_article_settings', 'area=portalconfig;sa=articlesettings'),
	));
}

function sportal_actions(&$actionArray)
{
	global $context;

	if (empty($context['disable_sp']))
	{
		$actionArray['portal'] = array('PortalMain.php', 'sportal_main');
		$actionArray['forum'] = array('BoardIndex.php', 'BoardIndex');
		$actionArray['ehportal_shout'] = array('PortalShoutbox.php', 'sportal_shoutbox');
		$actionArray['ehportal_shout_history'] = array('PortalShoutbox.php', 'sportal_shoutbox_history');
	}
}

function sportal_initialize(&$no_stat_actions)
{
	global $maintenance, $modSettings, $context, $settings, $sourcedir, $user_info;

	// if ($context['browser_body_id'] == 'mobile')
	require_once($sourcedir . '/PortalMobileDetect.php');
	$detect = new Portal_Mobile_Detect;
	$context['html_headers'] = !empty($context['html_headers']) ? $context['html_headers'] : '';
	$context['html_headers'] .= '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/portal.css">';
	// responsive layout for mobile and tablets
	if ($detect->isMobile() || $detect->isTablet() && !empty($modSettings['sp_display_responsive']))
	{
		$_SESSION['isPortalMobile'] = true;
		$context['html_headers'] .= '
	<script>
		$(document).ready(function() {
				var newEhDiv;
				$("table").each(function() {
					$(this).wrap("<div class="table-responsive"></div>");
					$(this).addClass("table");
					$(this).addClass("table-sm");
				});
				$("td").each(function() {
					$(this).addClass("table");
					$(this).addClass("table-sm");
				});
		});
	</script>
	<meta name="viewport" content="width=device-width, initial-scale=0.93, maximum-scale=3.0, minimum-scale=0.93">
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/portal_responsive.css">
	<script src="' . $settings['default_theme_url'] . '/scripts/portal_popper.js"></script>
	<script src="' . $settings['default_theme_url'] . '/scripts/portal_bootstrap.js"></script>
	<!--[if !IE]><!-->
	<style>
		@media
		only screen and (max-width: 760px),
		(min-device-width: 768px) and (max-device-width: 1024px) and (overflow-block: paged) {
			#main-content .sidebar img {
				display: none;
			}
			table, thead, tbody, th, td, tr {
				display: block;
			}
			thead tr {
				position: absolute;
				top: -9px;
				left: -9px;
			}
			tr {
				border: 1px solid #ccc;
			}
			td {
				border: none;
				border-bottom: 1px solid #eee;
				position: relative;
				min-width: 95vw;
				max-width: 95vw;
				width: 95vw;
				left: -2px;
				padding-left: 1px;
			}
			#main_content_section
			{
				padding: 0px;
				padding-left: 0.6%;
				border: none;
				border-bottom: 1px solid #eee;
				position: relative;
				width: 97vw;
				left: -1px;
			}
			.game_table
			{
				padding: 0px;
				justify-content: center;
				overflow-y: auto;
				border: 0px;
				border-bottom: 1px solid #eee;
				position: relative;
				width: 98vw;
				left: -5px;
			}
			#wrapper
			{
				padding: 0px;
				border: 0px;
				border-bottom: 1px solid #eee;
				position: relative;
				width: 97vw;
				padding-left: 7px;
				left: -3px;
			}
			td:before {
				position: absolute;
				top: -2px;
				left: -2px;
				min-width: 45%;
				white-space: nowrap;
			}
			html, body {
				overflow-x: hidden;
			}
			body {
				position: relative;
				display: block;
				flex-wrap: wrap;
				margin-left: auto;
				margin-right: auto;
				direction: ' . (!empty($context['right_to_left']) ? 'rtl' : 'ltr') . ';
				width: 100vw;
				justify-content: space-around;
				line-height: 150%;
				left: -4px;
				padding-left: 5px;
				overflow-x: hidden;
			}
			.popup_content {
				position: relative;
				display: block;
				flex-wrap: wrap;
				margin-left: auto;
				margin-right: auto;
				direction: ' . (!empty($context['right_to_left']) ? 'rtl' : 'ltr') . ';
				justify-content: space-around;
				line-height: 150%;
				left: -4px;
				padding-left: 5px;
				overflow-x: hidden;
				width: 97vw;
			}
			#profile_menu {
				position: relative;
				display: block;
				flex-wrap: wrap;
				margin-left: auto;
				margin-right: auto;
				direction: ' . (!empty($context['right_to_left']) ? 'rtl' : 'ltr') . ';
				justify-content: space-around;
				line-height: 150%;
				left: -4px;
				padding-left: 5px;
				overflow-x: hidden;
				width: 97vw;
			}
			.sp_block_section {
				position: relative;
				display: block;
				flex-wrap: wrap;
				margin-left: auto;
				margin-right: auto;
				direction: ' . (!empty($context['right_to_left']) ? 'rtl' : 'ltr') . ';
				justify-content: space-around;
				line-height: 150%;
				left: -4px;
				padding-left: 5px;
				overflow-x: hidden;
				width: 97vw;
			}
			input
			{
				max-width: 80vw;
				border-style: solid;
				border-width: 1px;
				padding: 2px;
			}
		}
	</style>
	<script>
	$(document).ready(function(){
		$( "input" ).css( "max-width", function( index ) {
			return "80%";
		});
		$( window ).on("orientationchange", function(event) {
			setTimeout(function(){
				var scrollLeft = $(window).scrollLeft();
				var scrollTop = $(window).scrollTop();
				if (scrollLeft > 0)
					window.scrollTo(0, scrollTop);
			}, 1000);
		});
	});
	</script>
	<!--<![endif]-->';
	}
	else
		$_SESSION['isPortalMobile'] = false;

	if (empty($_REQUEST['action']) || !($_REQUEST['action'] == 'portal' && isset($_GET['xml'])) && !in_array($_REQUEST['action'], $no_stat_actions))
	{
		// Log this user as online.
		writeLog();

		// Track forum statistics and hits...?
		if (!empty($modSettings['hitStats']))
			trackStats(array('hits' => '+'));

		$no_stat_actions[] = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
	}

	// Load SimplePortal.
	sportal_init();

	if (!empty($maintenance) && !allowedTo('admin_forum'))
		return;
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('coppa', 'login', 'login2', 'register', 'register2', 'reminder', 'activate', 'help', 'helpadmin', 'smstats', 'verificationcode', 'signup', 'signup2'))))
		return;
	elseif (empty($_REQUEST['action']) || (!empty($_GET['page']) && $_REQUEST['action'] == 'portal'))
	{
		$sp_action = sportal_catch_action();
		//sp_theme_copyright();
		if ($sp_action)
		{
			$_REQUEST['action'] = 'portal';

			$actions = array(
				'sportal_add_article' => 'addarticle',
				'sportal_articles' => 'articles',
				'sportal_credits' => 'credits',
				'sportal_pages' => 'pages',
				'sportal_remove_article' => 'removearticle',
				'sportal_shoutbox' => 'shoutbox',
				'ehportal_shout_history' => 'shoutbox',
			);

			if (array_key_exists($sp_action, $actions))
				$_REQUEST['sa'] = $actions[$sp_action];

			return $sp_action;
		}
	}
}

function sportal_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context, $modSettings;
	$version = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	$permissionList['membergroup'] += array(
			'sp_admin' => array(false, 'sp', 'sp'),
			'sp_manage_settings' => array(false, 'sp', 'sp'),
			'sp_manage_blocks' => array(false, 'sp', 'sp'),
			'sp_manage_articles' => array(false, 'sp', 'sp'),
			'sp_manage_pages' => array(false, 'sp', 'sp'),
			'sp_manage_shoutbox' => array(false, 'sp', 'sp'),
			'sp_add_article' => array(false, 'sp', 'sp'),
			'sp_auto_article_approval' => array(false, 'sp', 'sp'),
			'sp_remove_article' => array(false, 'sp', 'sp'),
			'sp_own_profile' => array(false, 'sp', 'sp'),
	);

	if ($version === 'v2.0')
	{
		$permissionGroups['membergroup']['simple'] += array(
				'sp',
		);

		$permissionGroups['membergroup']['classic'] += array(
				'sp',
		);
	}
	else
		$permissionGroups['membergroup'] += array(
				'sp',
		);

	$context['non_guest_permissions'] += array(
		'sp_admin',
		'sp_manage_settings',
		'sp_manage_blocks',
		'sp_manage_articles',
		'sp_manage_pages',
		'sp_manage_shoutbox',
		'sp_add_article',
		'sp_auto_article_approval',
		'sp_remove_article',
	);
}

function EhPortal_profile_areas(&$profile_areas)
{
	global $modSettings, $txt, $context, $settings;

	$profile_areas['edit_profile']['areas'] += array(
		'portalSettings' => array(
			'label' => $txt['EhPortalSettings'],
			'file' => 'PortalProfile.php',
			'function' => 'portalProfileSettings',
			'icon' => 'portal_profile',
			'enabled' => true,
			'permission' => array(
				'own' => array('sp_own_profile'),
				'any' => array('admin'),
			),
		),
	);
}

function EhPortal_whos_online($actions)
{
	global $txt, $scripturl;

	$actions['action'] = !empty($actions['action']) ? $actions['action'] : '';
	switch($actions['action'])
	{
		case 'ehportal_shout_history':
			$shoutbox_id = !empty($_REQUEST['shoutbox_history']) ? (int)$_REQUEST['shoutbox_history'] : 0;
			$description = sprintf($txt['sp_who_shout_history'], $scripturl . '?action=ehportal_shout;sa=shoutbox_history;shoutbox_id=' . $shoutbox_id);
			break;
		case 'ehportal_shout':
			$description = $txt['whoall_who'];
			break;
		default:
			$description = '';
	}

	return $description;
}

function EhPortal_log_stats(&$no_stat_actions)
{
	// do not show shoutbox in the online list
	$no_stat_actions += array('whoehportalshout');
}

function sportal_getconst($const)
{
    return (defined($const)) ? constant($const) : false;
}

?>