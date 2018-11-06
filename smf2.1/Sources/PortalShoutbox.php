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
	void sportal_shoutbox()
		// !!!
*/

function sportal_shoutbox()
{
	global $sourcedir, $smcFunc, $context, $user_info, $scripturl, $txt, $settings;

	$context['template_layers'] = array();
	$context['aeva_disable'] = true;
	$context['sub_template'] = 'shoutbox_refresh';
	$context['SPortal']['shouts'] = array();
	$shoutbox_id = isset($_REQUEST['shoutbox_id']) ? (int)$_REQUEST['shoutbox_id'] : (!empty($_POST['idshoutbox']) ? (int)$_POST['idshoutbox'] : 0);
	$_SESSION['shoutbox_id'] = !empty($shoutbox_id) ? $shoutbox_id : (!empty($_SESSION['shoutbox_id']) ? (int)$_SESSION['shoutbox_id'] : 0);

	$context['SPortal']['shoutbox'] = sportal_get_shoutbox($_SESSION['shoutbox_id'], true, true);
	$message = !empty($_SESSION['shoutbox_id']) && isset($_POST['message_' . $_SESSION['shoutbox_id']]) ? $_POST['message_' . $_SESSION['shoutbox_id']] : '';

	if (empty($context['SPortal']['shoutbox']))
		fatal_lang_error('error_sp_shoutbox_not_exist', false);

	$context['SPortal']['shoutbox']['warning'] = parse_bbc($context['SPortal']['shoutbox']['warning']);	
	if (isset($_SESSION['old_url']) && stripos($_SESSION['old_url'], $scripturl . '?action=login2;sa=check;member=' . $user_info['id']) !== false)
	{
		$_SESSION['old_url'] = $scripturl . '?action=portal';
		redirectexit($scripturl . '?action=portal');
	}

	require_once($sourcedir . '/Subs-Post.php');
	loadTemplate('PortalShoutbox');
	$can_moderate = allowedTo('sp_admin') || allowedTo('sp_manage_shoutbox');
	if (!$can_moderate && !empty($context['SPortal']['shoutbox']['moderator_groups']))
		$can_moderate = count(array_intersect($user_info['groups'], $context['SPortal']['shoutbox']['moderator_groups'])) > 0;

	if(!empty($_POST))
	{
		//check if its an ajax request, exit if not
		if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			die();
		}

		if(isset($message) &&  strlen($message)>0)
		{
			is_not_guest();
			preparsecode($message);
			$message = censorText($message);
			$username = filter_var(trim($user_info['name']),FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
			$message = filter_var(trim($message),FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
			checkSession('request');
			// check again after the filters
			if(isset($message) &&  strlen($message)>0)
				sportal_create_shout($context['SPortal']['shoutbox'], $message);
		}

		if (!empty($_POST['delete']))
		{
			checkSession('request');

			if (!$can_moderate)
				fatal_lang_error('error_sp_cannot_shoutbox_moderate', false);

			$_POST['delete'] = str_replace('delshout_', '', $_POST['delete']);
			$_POST['delete'] = (int) $_POST['delete'];

			if (!empty($_REQUEST['delete']))
				sportal_delete_shout($shoutbox_id, $_POST['delete']);
		}

		if(!empty($_POST["fetch"]))
		{
			$request = $smcFunc['db_query']('','
				SELECT sh.id_shout, sh.id_shoutbox, sh.member_name, sh.log_time, sh.body,
				IFNULL(mem.real_name, sh.member_name) AS member_name, IFNULL(mem.id_member, 0) AS id_member,
				mg.online_color AS member_group_color, pg.online_color AS post_group_color
				FROM {db_prefix}sp_shouts AS sh
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sh.id_member)
				LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
				WHERE sh.id_shoutbox = {int:idshoutbox}
				ORDER BY id_shout DESC
				LIMIT {raw:show}',
				array(
					'show' => !empty($context['SPortal']['shoutbox']['num_show']) ? $context['SPortal']['shoutbox']['num_show'] : 10,
					'dir' => !empty($context['SPortal']['shoutbox']['reverse']) ? 'DESC' : 'ASC',
					'idshoutbox' => $_SESSION['shoutbox_id'],
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if (substr($row['body'], 0, 2) == '@[' && strpos($row['body'], ']') !== false)
				{
					$apos = strpos($row['body'], ']');
					$pvtId = abs(floatval(substr($row['body'], 2, $apos-2)));
					if ($pvtId > 0 && empty($user_info['is_admin']) && $user_info['id'] != $pvtId && $row['id_member'] != $pvtId && $user_info['id'] != $row['id_member'])
						continue;
					else
						$row['body'] = '@' . substr($row['body'], $apos+1);
				}
				$color = !empty($row['member_group_color']) ? $row['member_group_color'] : $row['post_group_color'];
				$pvt = '@[' . $row['id_member'] . ']' . $row['member_name'] . ':';
				$context['SPortal']['shouts'][] = array(
					'shoutbox_id' => $shoutbox_id,
					'session' => array('name' => $context['session_var'], 'value' => $context['session_id']),
					'height' => $context['SPortal']['shoutbox']['height'],
					'refresh' => $context['SPortal']['shoutbox']['refresh'],
					'reverse' => $context['SPortal']['shoutbox']['reverse'],
					'last_update' => $context['SPortal']['shoutbox']['last_update'],
					'disable_links' => !empty($context['SPortal']['shoutbox']['disable_links']) ? 1 : 0,
					'is_me' => $row['id_member'] == $user_info['id'] ? true : false,
					'author' => array(
						'link' => $scripturl . '?action=profile;u=' . $row['id_member'],
						'private' => '<a href="javascript:void(0);" onclick="replaceText(\'' . $pvt . ' \', document.getElementsByClassName(\'new_shout_' . $_SESSION['shoutbox_id'] . '\')[0]); return false;"><span id="shoutName' . $row['id_member'] . '" style="color: ' . $color . '">' . $row['member_name'] . '</span></a>',
						'id' => $row['id_member'],
						'name' => $row['member_name'],
						'color' => $color,
					),
					'text' => $row['body'],
					'name' => $row['member_name'],
					'delete_link_js' => $can_moderate ? '<input onclick="EhShoutDel('. $shoutbox_id . ', \'delshout_' . $row['id_shout'] . '\');return false;" style="padding: 0px;border: 0px;width: 9px;height: 9px;vertical-align: top;" type="image" src="' . $settings['default_theme_url'] . '/images/sp/delete_small.png" class="delshout" name="delshout_' . $row['id_shout'] .'" /> ' : '',
					'delete_link_js_hist' => $can_moderate ? '<input onclick="EhShoutDelHistory(this);form.submit();return false;" style="padding: 0px;border: 0px;width: 9px;height: 9px;vertical-align: top;" type="image" src="' . $settings['default_theme_url'] . '/images/sp/delete_small.png" class="delshout" name="delshout_' . $row['id_shout'] .'" /> ' : '',
					'time' => timeformat($row['log_time']),
					'raw_time' => $row['log_time'],
					'color' => $color,
				);
			}
			$smcFunc['db_free_result']($request);

			if (empty($context['SPortal']['shouts']))
				$context['SPortal']['shouts'][] = array(
					'shoutbox_id' => $shoutbox_id,
					'session' => array('name' => $context['session_var'], 'value' => $context['session_id']),
					'height' => $context['SPortal']['shoutbox']['height'],
					'refresh' => $context['SPortal']['shoutbox']['refresh'],
					'reverse' => $context['SPortal']['shoutbox']['reverse'],
				);
		}
		else
		{
			header('HTTP/1.1 500 Access Denied');
			exit();
		}
	}

	if (!empty($context['SPortal']['shoutbox']['reverse']))
		$context['SPortal']['shouts'] = array_reverse($context['SPortal']['shouts']);

	$context['ehportal_page_index'] = $scripturl . '?action=ehportal_shout_history;shoutbox_id=' . $shoutbox_id . (!empty($context['start']) ? ';start=' . $context['start'] : '');
	$context['page_title'] = $context['SPortal']['shoutbox']['name'];
}

function sportal_shoutbox_history()
{
	global $smcFunc, $context, $user_info, $scripturl;

	$context['aeva_disable'] = true;
	loadTemplate('PortalShoutbox');
	$context['sub_template'] = 'shoutbox_history';
	$context['SPortal']['shouts'] = array();
	$shoutbox_id = isset($_REQUEST['shoutbox_id']) ? (int)$_REQUEST['shoutbox_id'] : 0;
	$context['SPortal']['shoutbox'] = sportal_get_shoutbox($shoutbox_id, true, true);
	$can_moderate = allowedTo('sp_admin') || allowedTo('sp_manage_shoutbox');
	if (!$can_moderate && !empty($context['SPortal']['shoutbox']['moderator_groups']))
		$can_moderate = count(array_intersect($user_info['groups'], $context['SPortal']['shoutbox']['moderator_groups'])) > 0;

	if (empty($context['SPortal']['shoutbox']))
		fatal_lang_error('error_sp_shoutbox_not_exist', false);

	if (!empty($_POST['delete']))
	{
		checkSession('request');

		if (!$can_moderate)
			fatal_lang_error('error_sp_cannot_shoutbox_moderate', false);

		$_POST['delete'] = str_replace('delshout_', '', $_POST['delete']);
		$_POST['delete'] = (int) $_POST['delete'];

		if (!empty($_REQUEST['delete']))
			sportal_delete_shout($shoutbox_id, $_POST['delete']);
	}

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_shouts
		WHERE id_shoutbox = {int:current}',
		array(
			'current' => $shoutbox_id,
		)
	);
	list ($total_shouts) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['per_page'] = !empty($context['SPortal']['shoutbox']['num_show']) ? $context['SPortal']['shoutbox']['num_show'] : 20;
	$context['start'] = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
	$context['page_index'] = constructPageIndex($scripturl . '?action=ehportal_shout_history;shoutbox_id=' . $shoutbox_id, $context['start'], $total_shouts, $context['per_page']);
	$context['ehportal_page_index'] = $scripturl . '?action=ehportal_shout_history;shoutbox_id=' . $shoutbox_id . (!empty($context['start']) ? ';start=' . $context['start'] : '');
	$caching = !empty($context['SPortal']['shoutbox']['caching']) ? $context['SPortal']['shoutbox']['caching'] : false;
	$bbc = !empty($context['SPortal']['shoutbox']['allowed_bbc']) ? $context['SPortal']['shoutbox']['allowed_bbc'] : array();
	$name = !empty($context['SPortal']['shoutbox']['name']) ? $context['SPortal']['shoutbox']['name'] : '';

	$shout_parameters = array(
		'start' => $context['start'],
		'limit' => $context['per_page'],
		'bbc' => $bbc,
		'cache' => $caching,
		'can_moderate' => $can_moderate,
	);
	$context['SPortal']['shouts_history'] = sportal_get_shouts($shoutbox_id, $shout_parameters);

	$context['SPortal']['shoutbox_id'] = $shoutbox_id;
	$context['sub_template'] = 'shoutbox_all';
	$context['page_title'] = $name;
}

?>