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
	void portalProfileSettings()
		// !!!
*/

function portalProfileSettings($memID)
{
	global $scripturl, $context, $txt, $smcFunc, $cur_profile, $profile_vars;

	$updates = array('ehportal_ignore_members', 'ehportal_enable_audio');
	$request = $smcFunc['db_query']('', '
		SELECT id_member, ehportal_ignore_members, ehportal_enable_audio
		FROM {db_prefix}sp_profiles
		WHERE id_member = {int:member}',
		array(
			'member' => $memID,
		)
	);

	$updates = array('ehportal_ignore_members', 'ehportal_enable_audio');
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		foreach ($updates as $update)
			$profile_vars[$update] = !empty($row[$update]) ? $row[$update] : 0;
	}

	$smcFunc['db_free_result']($request);

	$context['profile_fields'] = array(
		'ehportal_ignore_members' => array(
			'type' => 'check',
			'label' => $txt['EhPortalIgnoreShouts'],
			'permission' => 'sp_own_profile',
			'input_attr' => '',
			'value' => !empty($profile_vars['ehportal_ignore_members']) ? 1 : 0,
			'enabled' => allowedTo('sp_own_profile') ? true : false,
		),
		'ehportal_enable_audio' => array(
			'type' => 'check',
			'label' => $txt['EhPortalDisableAudio'],
			'permission' => 'sp_own_profile',
			'input_attr' => '',
			'value' => !empty($profile_vars['ehportal_enable_audio']) ? 1 : 0,
			'enabled' => allowedTo('sp_own_profile') ? true : false,
		),
	);

	if (isset($_REQUEST['save']))
	{
		checkSession('post');
		$errors = false;

		foreach ($context['profile_fields'] as $id => $field)
		{
			if ($id == 'notifications' || !isset($_POST[$id]))
				continue;

			if ($field['type'] == 'check')
				$_POST[$id] = (int)$_POST[$id];

			if ($field['type'] == 'select')
			{
				if (isset($field['options'][$_POST[$id]]))
					$updates[] = array($memID, $id, $_POST[$id]);
			}
		}

		$smcFunc['db_insert']('replace',
			'{db_prefix}sp_profiles',
			array(
				'id_member' => 'int', 'ehportal_ignore_members' => 'int', 'ehportal_enable_audio' => 'int',
			),
			array(
				$memID, $_POST['ehportal_ignore_members'], $_POST['ehportal_enable_audio'],
			),
			array('id_member')
		);

		unset($_REQUEST['save']);
		redirectexit($scripturl . '?action=profile;area=portalSettings;u=' . $memID);
	}

	$context['profile_custom_submit_url'] = $scripturl . '?action=profile;area=portalSettings;u=' . $memID . ';save';
	$context['page_desc'] = $txt['EhPortalSettingsDesc'];
	$context['sub_template'] = 'edit_options';
}

?>