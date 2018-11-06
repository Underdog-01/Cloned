<?php
/*
	<id>ChenZhen:EhPortal</id>
	<name>EhPortal</name>
	<version>1.2</version>
*/
/*
 * EhPortal is a ported version of SimplePortal 2.3.6 (Copyright (c) 2014 SimplePortal Team.)
 * This software is in no way affiliated with the original developers
 * EhPortal ~ Copyright (c) 2018 WebDev (http://web-develop.ca)
 * Distributed under the BSD 2-Clause License (http://opensource.org/licenses/BSD-2-Clause)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_admin_shoutbox_main()
		// !!!

	void sportal_admin_shoutbox_list()
		// !!!

	void sportal_admin_shoutbox_edit()
		// !!!

	void sportal_admin_shoutbox_delete()
		// !!!

	void sportal_admin_shoutbox_status()
		// !!!

	void sportal_admin_shoutbox_fix()
		// !!!

	void sportal_admin_shoutbox_block_redirect()
		// !!!
*/

function sportal_admin_shoutbox_main()
{
	global $context, $txt, $sourcedir;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_shoutbox');

	require_once($sourcedir . '/Subs-PortalAdmin.php');
	sp_smf_version();

	loadLanguage('SportalShoutSounds');
	loadTemplate('PortalAdminShoutbox');

	$subActions = array(
		'list' => 'sportal_admin_shoutbox_list',
		'add' => 'sportal_admin_shoutbox_edit',
		'fix' => 'sportal_admin_shoutbox_fix',
		'edit' => 'sportal_admin_shoutbox_edit',
		'prune' => 'sportal_admin_shoutbox_prune',
		'delete' => 'sportal_admin_shoutbox_delete',
		'status' => 'sportal_admin_shoutbox_status',
		'blockredirect' => 'sportal_admin_shoutbox_block_redirect',
		'audio' => 'sportal_admin_shoutbox_audio',
		'editaudio' => 'sportal_admin_shoutbox_edit_audio',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp_admin_shoutbox_title'],
		'help' => 'sp_ShoutboxArea',
		'description' => $txt['sp_admin_shoutbox_desc'],
		'tabs' => array(
			'list' => array(
			),
			'audio' => array(
			),
			'add' => array(
			),
		),
	);

	$subActions[$_REQUEST['sa']]();
}

function sportal_admin_shoutbox_list()
{
	global $txt, $smcFunc, $context, $scripturl;

	if (!empty($_POST['remove_shoutbox']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $page_id)
			$_POST['remove'][(int) $index] = (int) $page_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_shoutboxes
			WHERE id_shoutbox IN ({array_int:shoutbox})',
			array(
				'shoutbox' => $_POST['remove'],
			)
		);

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_shouts
			WHERE id_shoutbox IN ({array_int:shoutbox})',
			array(
				'shoutbox' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'name' =>  array(
			'down' => 'name ASC',
			'up' => 'name DESC'
		),
		'num_shouts' => array(
			'down' => 'num_shouts ASC',
			'up' => 'num_shouts DESC'
		),
		'caching' => array(
			'down' => 'caching ASC',
			'up' => 'caching DESC'
		),
		'status' => array(
			'down' => 'status ASC',
			'up' => 'status DESC'
		),
	);

	$context['columns'] = array(
		'name' => array(
			'width' => '40%',
			'label' => $txt['sp_admin_shoutbox_col_name'],
			'class' => 'first_th',
			'sortable' => true
		),
		'num_shouts' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_shouts'],
			'sortable' => true
		),
		'caching' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_caching'],
			'sortable' => true
		),
		'status' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_status'],
			'sortable' => true
		),
		'actions' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_shoutbox_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalshoutbox;sa=list;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_shoutboxes'
	);
	list ($total_shoutbox) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalshoutbox;sa=list;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_shoutbox, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_shoutbox, name, caching, status, num_shouts
		FROM {db_prefix}sp_shoutboxes
		ORDER BY id_shoutbox, {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['shoutboxes'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['shoutboxes'][$row['id_shoutbox']] = array(
			'id' => $row['id_shoutbox'],
			'name' => $row['name'],
			'shouts' => $row['num_shouts'],
			'caching' => $row['caching'],
			'status' => $row['status'],
			'status_image' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=status;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image(empty($row['status']) ? 'deactive' : 'active', $txt['sp_admin_shoutbox_' . (!empty($row['status']) ? 'de' : '') . 'activate']) . '</a>',
			'actions' => array(
				'fix' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=fix;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('fixbox') . '</a>',
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=edit;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'prune' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=prune;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('bin') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalshoutbox;sa=delete;shoutbox_id=' . $row['id_shoutbox'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_shoutbox_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);

	$context['sub_template'] = 'shoutbox_list';
	$context['page_title'] = $txt['sp_admin_shoutbox_list'];
}

function sportal_admin_shoutbox_edit()
{
	global $txt, $context, $modSettings, $smcFunc;

	$context['SPortal']['is_new'] = empty($_REQUEST['shoutbox_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_shoutbox_name_empty', false);

		$result = $smcFunc['db_query']('','
			SELECT id_shoutbox
			FROM {db_prefix}sp_shoutboxes
			WHERE name = {string:name}
				AND id_shoutbox != {int:current}
			LIMIT 1',
			array(
				'limit' => 1,
				'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
				'current' => (int) $_POST['shoutbox_id'],
			)
		);
		list ($has_duplicate) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		if (!empty($has_duplicate))
			fatal_lang_error('sp_error_shoutbox_name_duplicate', false);

		$permission_set = 0;
		$groups_allowed = $groups_denied = '';

		if (!empty($_POST['permission_set']))
			$permission_set = (int) $_POST['permission_set'];
		elseif (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
		{
			$groups_allowed = $groups_denied = array();

			foreach ($_POST['membergroups'] as $id => $value)
			{
				if ($value == 1)
					$groups_allowed[] = (int) $id;
				elseif ($value == -1)
					$groups_denied[] = (int) $id;
			}

			$groups_allowed = implode(',', $groups_allowed);
			$groups_denied = implode(',', $groups_denied);
		}

		if (isset($_POST['moderator_groups']) && is_array($_POST['moderator_groups']) && count($_POST['moderator_groups']) > 0)
		{
			foreach ($_POST['moderator_groups'] as $id => $group)
				$_POST['moderator_groups'][$id] = (int) $group;

			$_POST['moderator_groups'] = implode(',', $_POST['moderator_groups']);
		}
		else
			$_POST['moderator_groups'] = '';

		if (!empty($_POST['allowed_bbc']) && is_array($_POST['allowed_bbc']))
		{
			foreach ($_POST['allowed_bbc'] as $id => $tag)
				$_POST['allowed_bbc'][$id] = $smcFunc['htmlspecialchars']($tag, ENT_QUOTES);

			$_POST['allowed_bbc'] = implode(',', $_POST['allowed_bbc']);
		}
		else
			$_POST['allowed_bbc'] = '';

		$fields = array(
			'name' => 'string',
			'permission_set' => 'int',
			'groups_allowed' => 'string',
			'groups_denied' => 'string',
			'moderator_groups' => 'string',
			'warning' => 'string',
			'allowed_bbc' => 'string',
			'height' => 'int',
			'num_show' => 'int',
			'num_max' => 'int',
			'reverse' => 'int',
			'caching' => 'int',
			'refresh' => 'int',
			'status' => 'int',
		);

		$shoutbox_info = array(
			'id' => (int) $_POST['shoutbox_id'],
			'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			'permission_set' => $permission_set,
			'groups_allowed' => $groups_allowed,
			'groups_denied' => $groups_denied,
			'moderator_groups' => $_POST['moderator_groups'],
			'warning' => $smcFunc['htmlspecialchars']($_POST['warning'], ENT_QUOTES),
			'allowed_bbc' => $_POST['allowed_bbc'],
			'height' => (int) $_POST['height'],
			'num_show' => (int) $_POST['num_show'],
			'num_max' => (int) $_POST['num_max'],
			'reverse' => !empty($_POST['reverse']) ? 1 : 0,
			'caching' => !empty($_POST['caching']) ? 1 : 0,
			'refresh' => (int) $_POST['refresh'],
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($context['SPortal']['is_new'])
		{
			unset($shoutbox_info['id']);

			$smcFunc['db_insert']('',
				'{db_prefix}sp_shoutboxes',
				$fields,
				$shoutbox_info,
				array('id_shoutbox')
			);
			$shoutbox_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_shoutboxes', 'id_shoutbox');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_shoutboxes
				SET ' . implode(', ', $update_fields) . '
				WHERE id_shoutbox = {int:id}',
				$shoutbox_info
			);
		}

		sportal_update_shoutbox($shoutbox_info['id']);

		if ($context['SPortal']['is_new'] && (allowedTo(array('sp_admin', 'sp_manage_blocks'))))
			redirectexit('action=admin;area=portalshoutbox;sa=blockredirect;shoutbox=' . $shoutbox_info['id']);
		else
			redirectexit('action=admin;area=portalshoutbox');
	}

	if ($context['SPortal']['is_new'])
	{
		$context['SPortal']['shoutbox'] = array(
			'id' => 0,
			'name' => $txt['sp_shoutbox_default_name'],
			'permission_set' => 3,
			'groups_allowed' => array(),
			'groups_denied' => array(),
			'moderator_groups' => array(),
			'warning' => '',
			'allowed_bbc' => array('b', 'i', 'u', 's', 'url', 'code', 'quote', 'me'),
			'height' => 200,
			'num_show' => 20,
			'num_max' => 1000,
			'reverse' => 0,
			'caching' => 1,
			'refresh' => 0,
			'status' => 1,
		);
	}
	else
	{
		$_REQUEST['shoutbox_id'] = (int) $_REQUEST['shoutbox_id'];
		$context['SPortal']['shoutbox'] = sportal_get_shoutbox($_REQUEST['shoutbox_id']);
	}

	loadLanguage('Post');

	$context['SPortal']['shoutbox']['groups'] = sp_load_membergroups();
	sp_loadMemberGroups($context['SPortal']['shoutbox']['moderator_groups'], 'moderator', 'moderator_groups');

	$context['allowed_bbc'] = array(
		'b' => $txt['sp_bold'],
		'i' => $txt['sp_italic'],
		'u' => $txt['sp_underline'],
		's' => $txt['sp_strike'],
		'pre' => $txt['sp_preformatted'],
		'flash' => $txt['sp_flash'],
		'img' => $txt['sp_image'],
		'url' => $txt['sp_hyperlink'],
		'email' => $txt['sp_insert_email'],
		'ftp' => $txt['sp_ftp'],
		'glow' => $txt['sp_glow'],
		'shadow' => $txt['sp_shadow'],
		'sup' => $txt['sp_superscript'],
		'sub' => $txt['sp_subscript'],
		'tt' => $txt['sp_teletype'],
		'code' => $txt['sp_bbc_code'],
		'quote' => $txt['sp_bbc_quote'],
		'size' => $txt['sp_font_size'],
		'font' => $txt['sp_font_face'],
		'color' => $txt['sp_change_color'],
		'me' => 'me',
	);

	$disabled_tags = array();
	if (!empty($modSettings['disabledBBC']))
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
	if (empty($modSettings['enableEmbeddedFlash']))
		$disabled_tags[] = 'flash';

	foreach ($disabled_tags as $tag)
	{
		if ($tag == 'list')
			$context['disabled_tags']['orderlist'] = true;

		$context['disabled_tags'][trim($tag)] = true;
	}

	$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp_admin_shoutbox_add'] : $txt['sp_admin_shoutbox_edit'];
	$context['sub_template'] = 'shoutbox_edit';
}

function sportal_admin_shoutbox_prune()
{
	global $smcFunc, $context, $txt;

	$shoutbox_id = empty($_REQUEST['shoutbox_id']) ? 0 : (int) $_REQUEST['shoutbox_id'];
	$context['shoutbox'] = sportal_get_shoutbox($shoutbox_id);

	if (empty($context['shoutbox']))
		fatal_lang_error('error_sp_shoutbox_not_exist', false);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!empty($_POST['type']))
		{
			$where = array('id_shoutbox = {int:shoutbox_id}');
			$parameters = array('shoutbox_id' => $shoutbox_id);

			if ($_POST['type'] == 'days' && !empty($_POST['days']))
			{
				$where[] = 'log_time < {int:time_limit}';
				$parameters['time_limit'] = time() - $_POST['days'] * 86400;
			}
			elseif ($_POST['type'] == 'member' && !empty($_POST['member']))
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_member
					FROM {db_prefix}members
					WHERE member_name = {string:member}
						OR real_name = {string:member}
					LIMIT {int:limit}',
					array(
						'member' => strtr(trim($smcFunc['htmlspecialchars']($_POST['member'], ENT_QUOTES)), array('\'' => '&#039;')),
						'limit' => 1,
					)
				);
				list ($member_id) =  $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				if (!empty($member_id))
				{
					$where[] = 'id_member = {int:member_id}';
					$parameters['member_id'] = $member_id;
				}
			}

			if ($_POST['type'] == 'all' || count($where) > 1)
			{
				$smcFunc['db_query']('','
					DELETE FROM {db_prefix}sp_shouts
					WHERE ' . implode(' AND ', $where),
					$parameters
				);

				if ($_POST['type'] != 'all')
				{
					$request = $smcFunc['db_query']('', '
						SELECT COUNT(*)
						FROM {db_prefix}sp_shouts
						WHERE id_shoutbox = {int:shoutbox_id}
						LIMIT {int:limit}',
						array(
							'shoutbox_id' => $shoutbox_id,
							'limit' => 1,
						)
					);
					list ($total_shouts) =  $smcFunc['db_fetch_row']($request);
					$smcFunc['db_free_result']($request);
				}
				else
					$total_shouts = 0;

				$smcFunc['db_query']('','
					UPDATE {db_prefix}sp_shoutboxes
					SET num_shouts = {int:total_shouts}
					WHERE id_shoutbox = {int:shoutbox_id}',
					array(
						'shoutbox_id' => $shoutbox_id,
						'total_shouts' => $total_shouts,
					)
				);

				sportal_update_shoutbox($shoutbox_id);
			}
		}

		redirectexit('action=admin;area=portalshoutbox');
	}

	$context['page_title'] = $txt['sp_admin_shoutbox_prune'];
	$context['sub_template'] = 'shoutbox_prune';
}

function sportal_admin_shoutbox_delete()
{
	global $smcFunc;

	checkSession('get');

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_shoutboxes
		WHERE id_shoutbox = {int:id}',
		array(
			'id' => $shoutbox_id,
		)
	);

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_shouts
		WHERE id_shoutbox = {int:id}',
		array(
			'id' => $shoutbox_id,
		)
	);

	redirectexit('action=admin;area=portalshoutbox');
}

function sportal_admin_shoutbox_fix()
{
	global $smcFunc;

	checkSession('get');

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_shouts
		WHERE id_shoutbox = {int:shoutbox_id}
		LIMIT {int:limit}',
		array(
			'shoutbox_id' => $shoutbox_id,
			'limit' => 1,
		)
	);
	list ($total_shouts) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$smcFunc['db_query']('','
		UPDATE {db_prefix}sp_shoutboxes
		SET num_shouts = {int:total_shouts}
		WHERE id_shoutbox = {int:shoutbox_id}',
		array(
			'shoutbox_id' => $shoutbox_id,
			'total_shouts' => $total_shouts,
		)
	);

	sportal_update_shoutbox($shoutbox_id);
	redirectexit('action=admin;area=portalshoutbox');
}

function sportal_admin_shoutbox_status()
{
	global $smcFunc;

	checkSession('get');

	$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}sp_shoutboxes
		SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
		WHERE id_shoutbox = {int:id}',
		array(
			'is_active' => 1,
			'id' => $shoutbox_id,
		)
	);

	redirectexit('action=admin;area=portalshoutbox');
}

function sportal_admin_shoutbox_audio()
{
	global $boardurl, $boarddir, $scripturl, $context, $modSettings, $txt;

	$path = $boarddir . '/sp_shout_sounds';
	$soundfiles = array_diff(scandir($path), array('.', '..'));
	list ($context['shoutbox_sb_files'], $files, $x) = array(array(), array(), 0);
	$context['sp_sbSound_sortArray'] = array('name', 'size', 'date');
	$context['sp_admin_shoutbox_audioFile'] = !empty($modSettings['sp_sbSound']) ? $modSettings['sp_sbSound'] : 'default.mp3';
	$context['sp_admin_shoutbox_audioFilex'] = strlen(mb_substr($context['sp_admin_shoutbox_audioFile'], 0, -4)) > 39 ? mb_substr(mb_substr($context['sp_admin_shoutbox_audioFile'], 0, -4), 0, 37) . '...' : mb_substr($context['sp_admin_shoutbox_audioFile'], 0, -4);
	$context['sp_admin_shoutbox_audioEnable'] = !empty($modSettings['sp_sbSoundEnable']) ? 1 : 0;
	$sort = isset($_REQUEST['sort']) && in_array(mb_strtolower($_REQUEST['sort'], 'UTF-8'), $context['sp_sbSound_sortArray']) ? mb_strtolower($_REQUEST['sort'], 'UTF-8') : 'date';
	$_REQUEST['start'] = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;

	// javascript
	$jsFunctions1 = '
			function playlistCheckClick(clickedId)
			{
				var delVal = document.getElementById("mp3del_"+clickedId).value;
				var isSelected = document.getElementById("mp3radio_"+clickedId).checked;
				if (delVal == "' . $context['sp_admin_shoutbox_audioFile'] . '")
				{
					alert("' . $txt['sp_admin_shoutbox_audioDelCurrent'] . '");
					document.getElementById("mp3del_"+clickedId).checked = false;
					return false;

				}
				else if (delVal == "default.mp3")
				{
					alert("' . $txt['sp_admin_shoutbox_audioDelDefault'] . '");
					document.getElementById("mp3del_"+clickedId).checked = false;
					return false;

				}

				if (isSelected == true)
				{
					alert("' . $txt['sp_admin_shoutbox_audioDelSame'] . '");
					document.getElementById("mp3del_"+clickedId).checked = false;
					return false;
				}
			}
			function playlistRadioClick(clickedId)
			{
				var radios = document.getElementsByTagName("input");
				for (i = 0; i < radios.length; i++)
				{
					if (radios[i].type == "radio" && radios[i].checked)
						radios[i].checked = false;
				}

				document.getElementById("mp3radio_"+clickedId).checked = true;
				document.getElementById("mp3del_"+clickedId).checked = false;
				document.getElementById(clickedId).click();
			}
			function confirmMp3Changes()
			{
				var checksx=document.getElementsByClassName("confirm_checkx"), radiosx=document.getElementsByClassName("confirm_radio"), checksi = false, radiosi = false, text="";
				for (i=0; i<checksx.length; i++) {
					checksi = checksx[i].checked == true ? true : checksi;
				}
				for (i=0; i<radiosx.length; i++) {
					radiosi = radiosx[i].checked == true ? true : radiosi;
				}
				if (checksi == true && radiosi == true) {
					text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmDel'] . $txt['sp_admin_shoutbox_audioConfirmDivider'] . $txt['sp_admin_shoutbox_audioConfirmChange'])) . '";
				}
				else if (checksi == true && radiosi == false) {
					text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmDel'])) . '";
				}
				else if (checksi == false && radiosi == true) {
					text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmChange'])) . '";
				}
				if (text != "" && confirm(text)) {
					return true;
				}
				else {
					return false;
				}
			}
			function confirmMp3Submit()
			{
				var filesy=document.getElementsByClassName("confirm_file"), texty=document.getElementsByClassName("confirm_texty"), checksy=document.getElementsByClassName("confirm_checky"), filesi = false, text="";
				for (i=0; i<filesy.length; i++) {
					filesi = filesy[i].value != "" ? true : filesi;
				}
				if (filesi == true) {
					if (texty[0].checked == true && checksy[0].checked != "' . ($context['sp_admin_shoutbox_audioEnable'] == 1 ? true : false) . '") {
						text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmSettings'] . $txt['sp_admin_shoutbox_audioConfirmDivider'] . $txt['sp_admin_shoutbox_audioConfirmFiles'] . $txt['sp_admin_shoutbox_audioConfirmDivider'] .  $txt['sp_admin_shoutbox_audioConfirmSetRefresh'])) . '";
					}
					else if (texty[0].checked == false && checksy[0].checked != "' . ($context['sp_admin_shoutbox_audioEnable'] == 1 ? true : false) . '") {
						text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmSettings'] . $txt['sp_admin_shoutbox_audioConfirmDivider'] . $txt['sp_admin_shoutbox_audioConfirmFiles'])) . '";
					}
					else if (texty[0].checked == true && checksy[0].checked == "' . ($context['sp_admin_shoutbox_audioEnable'] == 1 ? true : false) . '") {
						text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmSetRefresh'] . $txt['sp_admin_shoutbox_audioConfirmDivider'] . $txt['sp_admin_shoutbox_audioConfirmFiles'])) . '";
					}
					else {
						text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmFiles'])) . '";
					}
				}
				else if (texty[0].checked == true && checksy[0].checked != "' . ($context['sp_admin_shoutbox_audioEnable'] == 1 ? true : false) . '") {
					text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmSettings'] . $txt['sp_admin_shoutbox_audioConfirmDivider'] .  $txt['sp_admin_shoutbox_audioConfirmSetRefresh'])) . '";
				}
				else if (texty[0].checked == false && checksy[0].checked != "' . ($context['sp_admin_shoutbox_audioEnable'] == 1 ? true : false) . '") {
					text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmSettings'])) . '";
				}
				else if (texty[0].checked == true && checksy[0].checked == "' . ($context['sp_admin_shoutbox_audioEnable'] == 1 ? true : false) . '") {
					text = "' . ($txt['sp_admin_shoutbox_audioConfirm'] . '\n\r' . sprintf($txt['sp_admin_shoutbox_audioConfirmActions'], $txt['sp_admin_shoutbox_audioConfirmSetRefresh'])) . '";
				}
				if (text != "" && confirm(text)) {
					return true;
				}
				else {
					return false;
				}

			}
			function confirmMp3Submission(buttonIndex)
			{
				if (buttonIndex == 1) {
					return confirmMp3Changes();
				}
				else if (buttonIndex == 2) {
					return confirmMp3Submit();
				}

				return false;
			}
			function checkMp3All()
			{
				var checksx=document.getElementsByClassName("confirm_checkx"), checkall = document.getElementById("checkMp3All").innerHTML, idx, idy, mp3file, mp3check;

				for (i=0; i<checksx.length; i++) {
					idx = checksx[i].id.replace("mp3del_", "mp3radio_");
					idy = checksx[i].id;
					mp3file = document.getElementById(idx);
					mp3check = document.getElementById(idy);

					if (mp3check)
					{
						if (mp3file.value == "' . $context['sp_admin_shoutbox_audioFile'] . '" || mp3file.value == "default.mp3" || mp3file.checked == true) {
							mp3check.checked = false;
						}
						else {
							if (checkall == "' . $txt['sp_admin_shoutbox_audioFileDeleteAllOn'] . '") {
								mp3check.checked = true;
							}
							else {
								mp3check.checked = false;
							}
						}
					}
				}

				if (checkall == "' . $txt['sp_admin_shoutbox_audioFileDeleteAllOn'] . '") {
					document.getElementById("checkMp3All").innerHTML = "' . $txt['sp_admin_shoutbox_audioFileDeleteAllOff'] . '";
				}
				else {
					document.getElementById("checkMp3All").innerHTML = "' . $txt['sp_admin_shoutbox_audioFileDeleteAllOn'] . '";
				}
			}

			function shoutAdminScroll(oid,iid)
			{
				this.oCont=document.getElementById(oid)
				this.ele=document.getElementById(iid)
				this.width=this.ele.clientWidth;
				this.n=this.oCont.clientWidth;
				this.move=function(){
					this.ele.style.left=this.n+"px"
					this.n--
					if(this.n<(-this.width)){
						this.n=this.oCont.clientWidth
					}
				}
			}
			var vshoutAdminScroll;
			function shoutAdminScrollSetup()
			{
				vshoutAdminScroll=new shoutAdminScroll("oshoutAdminScroll","shoutAdminScroll");
				setInterval("vshoutAdminScroll.move()",20);
			}
			';

	$jsFunctions2 = '
			shoutAdminScrollSetup();
			var _player = document.getElementById("player"),
			_playlist = document.getElementById("playlist"),
			_stop = document.getElementById("stop"),
			buttonIndex = 0;

			function playlistItemClick(clickedElement)
			{
				var idx = clickedElement.getAttribute("id");
				var selected = _playlist.querySelector(".selected");
				if (selected)
				{
					selected.classList.remove("selected");
					selected.classList.remove("shoutbox_me");
					var radios = document.getElementsByTagName("input");
					var divs = document.getElementsByClassName("changemp3");
					for (i = 0; i < radios.length; i++) {
						if (radios[i].type == "radio" && radios[i].checked) {
							radios[i].checked = false;
							radios[i].classList.remove("shoutbox_me");
						}
					}
					for (i = 0; i < divs.length; i++) {
						divs[i].classList.remove("shoutbox_me");
					}
				}

				clickedElement.classList.add("selected");
				clickedElement.classList.add("shoutbox_me");
				document.getElementById("mp3radio_" + idx).checked = true;
				document.getElementById("mp3del_"+idx).checked = false;
				document.getElementById("mp3date_" + idx).classList.add("shoutbox_me");
				document.getElementById("mp3radio_" + idx).classList.add("shoutbox_me");
				document.getElementById("mp3size_" + idx).classList.add("shoutbox_me");

				_player.src = clickedElement.getAttribute("data-mp3");
				_player.play();
			}

			_stop.addEventListener("click", function () {_player.pause();});
			_playlist.addEventListener("click", function (e) {
				if (e.target && e.target.nodeName === "SPAN")
					playlistItemClick(e.target);
			});';

	if (strpos($context['html_headers'], 'function playlistCheckClick(clickedId)') === false)
		$context['html_headers'] .= '
	<script>
		' . $jsFunctions1 . '
		if(window.attachEvent) {
			window.attachEvent("onload", function() {' . $jsFunctions2 . '});
		}
		else {
			window.addEventListener("load", function() {' . $jsFunctions2 . '}, false);
		}
	</script>';

	foreach($soundfiles as $file)
	{
		$filepath = $boarddir . '/sp_shout_sounds/' . $file;
		$fileurl = $boardurl . '/sp_shout_sounds/' . $file;
		$path_parts = pathinfo($filepath);
		$sizex = filesize($filepath)/1000;
		$size = number_format((float)$sizex, 2, '.', '');
		$date = filemtime($filepath);
		$ext = !empty($path_parts['extension']) ? $path_parts['extension'] : '';
		$filex = strlen(mb_substr($file, 0, -4)) > 39 ? mb_substr(mb_substr($file, 0, -4), 0, 37) . '...' : mb_substr($file, 0, -4);
		$key = $sort == 'size' ? ($size*100)+$x : ($sort == 'date' ? $date+$x : $x);
		if ($file !== 'index.php' && $ext == 'mp3')
			$files[$key] = array('name' => $file, 'namex' => $filex, 'size' => $size, 'type' => $ext, 'path' => $fileurl, 'id' => $x, 'filepath' => $filepath, 'date' => $date);
		$x++;
	}

	if (!isset($_REQUEST['sort']) || !in_array(mb_strtolower($_REQUEST['sort'], 'UTF-8'), $context['sp_sbSound_sortArray']))
		$_REQUEST['sort'] = 'date';

	foreach ($context['sp_sbSound_sortArray'] as $col)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalshoutbox;sa=audio;sort=' . $col;
		$context['columns'][$col]['label'] = $txt['sp_sbSound_' . $col];

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';
	$desc = isset($_REQUEST['desc']) ? 'desc' : '';
	$alt = $sort == 'name' ? 'date' : 'name';
	// sort array using usort or ksort method
	switch ($sort)
	{
		case 'name':
			if ($desc)
				usort($files, function ($a, $b) use($sort) { return $a[$sort] < $b[$sort] ? 1 : -1; });
			else
				usort($files, function ($a, $b) use($sort) { return $a[$sort] > $b[$sort] ? 1 : -1; });
			break;
		default:
			if ($desc)
				krsort($files, SORT_NUMERIC);
			else
				ksort($files, SORT_NUMERIC);
	}

	$context['sp_admin_shoutbox_audioMessage'] = sportal_admin_shoutbox_scrollmsg_audio(0);
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalshoutbox;sa=audio;sort=' . $sort . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], count($files), 20);
	$context['start'] = $_REQUEST['start'];
	$end = ($context['start']+20) > (count($soundfiles)-1) ? (count($soundfiles)-1) : ($context['start']+20);
	$context['shoutbox_sb_files'] = array_slice($files, $context['start'], $end);
	$context['post_url'] = $scripturl . '?action=admin;area=portalshoutbox;sa=editaudio;';
	$context['settings_title'] = $txt['sp_admin_shoutbox_audio'];
	$context['page_title'] = $txt['sp_admin_shoutbox_audio'];
	loadTemplate('PortalAdminShoutboxSound');
	$context['sub_template'] = 'shoutbox_audio';
}

function sportal_admin_shoutbox_scrollmsg_audio($auto = false)
{
	global $sourcedir, $smcFunc, $txt;

	if (!allowedTo('sp_admin'))
		redirectexit('action=forum');

	require_once($sourcedir.'/Subs.php');

	$request = $smcFunc['db_query']('','
		SELECT id_shoutbox, name, refresh
		FROM {db_prefix}sp_shoutboxes
		ORDER BY id_shoutbox',
		array()
	);
	list($shoutboxesData1, $shoutboxesData2, $message) = array(array(), array(), '');
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$refresh = !empty($row['refresh']) ? (int)$row['refresh'] : 0;
		if ($refresh < 5)
			$shoutboxesData1[] = array(
				'id' => $row['id_shoutbox'],
				'refresh' => $row['refresh'],
				'name' => $row['name']
			);
		else
			$shoutboxesData2[] = array(
				'id' => $row['id_shoutbox'],
				'refresh' => $row['refresh'],
				'name' => $row['name']
			);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($auto))
	{
		foreach ($shoutboxesData1 as $shoutbox)
		{
			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_shoutboxes
				SET refresh = {int:refresh}
				WHERE id_shoutbox = {int:id}',
				array('id' => $shoutbox['id'], 'refresh' => 10)
			);
		}

		foreach ($shoutboxesData2 as $shoutbox)
		{
			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_shoutboxes
				SET refresh = {int:refresh}
				WHERE id_shoutbox = {int:id}',
				array('id' => $shoutbox['id'], 'refresh' => 10)
			);
		}
	}
	else
	{
		if (!empty($shoutboxesData1))
			$message = sprintf($txt['sp_admin_shoutbox_audioDetect'], count($shoutboxesData1)) . $txt['sp_admin_shoutbox_audioMessageDivider'];

		$message .= $txt['sp_admin_shoutbox_audioMessage'] . $txt['sp_admin_shoutbox_audioMessageDivider'] . $txt['sp_admin_shoutbox_audioAuto'];

		return $message;
	}

	return true;
}

function sportal_admin_shoutbox_edit_audio()
{
	global $boarddir, $settings, $smcFunc, $modSettings, $sourcedir, $txt;

	if (!allowedTo('sp_admin'))
		redirectexit('action=forum');

	checkSession();
	require_once($sourcedir.'/Subs.php');

	if(isset($_POST['submit_file']) && isset($_POST['del']))
	{
		$delFiles = (array)$_POST['del'];
		$current = !empty($modSettings['sp_sbSound']) ? $modSettings['sp_sbSound'] : 'default.mp3';

		foreach($delFiles as $delete)
		{
			if (file_exists($boarddir . '/sp_shout_sounds/' . $delete) && $delete !== $current && $delete !== 'default.mp3')
				unlink($boarddir . '/sp_shout_sounds/' . $delete);
		}
	}

	if (isset($_POST['submit_file']) && isset($_POST['mp3option']))
	{
		$file = $_POST['mp3option'];
		if (file_exists($boarddir . '/sp_shout_sounds/' . $file))
		{
			$name = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $file);
			$name = basename(mb_strtolower($name), '.mp3');
			$name = trim($name, '.') . '.mp3';

			if ($file !== $name)
				@rename($boarddir . '/sp_shout_sounds/' . $file, $boarddir . '/sp_shout_sounds/' . $name);

			if (file_exists($boarddir . '/sp_shout_sounds/' . $name))
			{
				updateSettings(array('sp_sbSound' => $_POST['mp3option']));
				$modSettings['sp_sbSound'] = $_POST['mp3option'];
			}
			else
				log_error(sprintf($txt['sp_admin_shoutbox_audioLogFile'], $name));
		}
	}
	elseif (isset($_POST['submit']))
	{
		$enable = isset($_POST['sp_sbSoundEnable']) && !empty($_POST['sp_sbSoundEnable']) ? 1 : 0;
		$interval = isset($_POST['sp_sbSoundInterval']) && !empty($_POST['sp_sbSoundInterval']) ?  $_POST['sp_sbSoundInterval'] : false;
		updateSettings(
			array(
				'sp_sbSoundEnable' => $enable
			)
		);

		$files = array();
		foreach ($_FILES['attachment']['name'] as $key => $value)
		{
			if (!empty($value))
				$files[] = array(
					'name' => $value,
					'tmp_name' => $_FILES['attachment']['tmp_name'][$key],
					'type' => $_FILES['attachment']['type'][$key],
					'error' => $_FILES['attachment']['error'][$key],
					'size' => $_FILES['attachment']['size'][$key]
				);
		}

		foreach ($files as $file)
		{
			if ($file['type'] == 'audio/mp3' && $file['error'] == 0 && $file['size'] < 40001)
			{
				$name = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', $file['name']);
				$name = basename(mb_strtolower($name), '.mp3');
				$name = trim($name, '.');
				if (!file_exists($boarddir . '/sp_shout_sounds/' . $name . '.mp3'))
					move_uploaded_file($file['tmp_name'], $boarddir . '/sp_shout_sounds/' . $name . '.mp3');
			}
		}

		if ($interval == true)
			sportal_admin_shoutbox_scrollmsg_audio('1');
	}

	redirectexit('action=admin;area=portalshoutbox;sa=audio');
}

function sportal_admin_shoutbox_block_redirect()
{
	global $context, $scripturl, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_blocks');

	$context['page_title'] = $txt['sp_admin_shoutbox_add'];
	$context['redirect_message'] = sprintf($txt['sp_admin_shoutbox_block_redirect_message'], $scripturl . '?action=admin;area=portalblocks;sa=add;selected_type=sp_shoutbox;parameters[]=shoutbox;shoutbox=' . $_GET['shoutbox'], $scripturl . '?action=admin;area=portalshoutbox');
	$context['sub_template'] = 'shoutbox_block_redirect';
}

?>