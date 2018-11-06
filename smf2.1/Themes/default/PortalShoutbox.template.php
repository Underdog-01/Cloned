<?php
// Version: 1.2; PortalShoutbox

function template_shoutbox_all()
{
	global $context;
	template_shoutbox_all_default();
}

function template_shoutbox_all_default()
{
	global $context, $txt;

	if (!empty($context['SPortal']['shouts_history']))
		echo '
	<form id="ehshout_history" method="POST" action="', $context['ehportal_page_index'], '">';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', (!empty($context['SPortal']['shoutbox']['name']) ? $context['SPortal']['shoutbox']['name'] : ''), '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div class="sp_content_padding">
			<div class="shoutbox_page_index smalltext">
					', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<div class="shoutbox_body">
				<ul class="shoutbox_list_all" id="shouts">';

	$x = 1;
	if (!empty($context['SPortal']['shouts_history']))
		foreach ($context['SPortal']['shouts_history'] as $shout)
		{
			echo '
					<li id="shoutindex' . $x . '" style="padding-left: 5px;" class="shouts smalltext">
						', !$shout['is_me'] ? '<strong>' . $shout['author']['link'] . ':</strong>' : '<span style="color: ' . $shout['author']['color'] . ';">' . $shout['author']['name'] . ':</span>', '
					</li>
					<li style="padding-left: 10px;" class="shouts smalltext">
						', str_replace('ignored_shout', 'history_ignored_shout', $shout['text']), '
					</li>
					<li style="padding-left: 5px;position: relative;" class="smalltext shoutbox_time"><div style="display: inline;position: relative;left: 2px;bottom: 0px;vertical-align: bottom;">', $shout['delete_link_js_hist'], '</div><div style="position: relative;display: inline;padding-left: 5px;bottom: 5px;left: 5px;">', $shout['time'], '</div></li>';
			$x++;
		}
	else
			echo '
					<li class="smalltext">', $txt['sp_shoutbox_no_shout'], '</li>';

	echo '
				</ul>
			</div>
			<div class="shoutbox_page_index smalltext">
					', $txt['pages'], ': ', $context['page_index'], '
			</div>
		</div>
		<span class="botslice"><span></span></span>
	</div>';
	if (!empty($context['SPortal']['shouts_history']))
		echo '
	<input class="ehShoutSess" type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	<input style="display: none;" type="submit" value="submit" />
	</form>';

	// <input type="hidden" id="shoutbox_id" name="shoutbox_id" value="', $context['SPortal']['shouts_history'][0]['id'], '" />
}

function template_shoutbox_embed($shoutbox)
{
	global $context, $scripturl, $settings, $txt, $user_info, $boarddir, $sourcedir, $modSettings;

	echo '
		<div class="shout_box">
			<div class="shoutbox_header">
				<div id="shoutbox_load_', $shoutbox['id'], '" style="float: right; display: inline;"><img src="', $settings['sp_images_url'], '/loading.gif" alt="" /></div>
				<button style="border: 0px;vertical-align: top;padding: 0px;" type="button" onclick="EhShoutRefresh(', $shoutbox['id'], ');">', sp_embed_image('refresh'), '</button> <button type="button" style="border: 0px;vertical-align: top;padding: 0px;" onclick="EhShoutHistory(\'', $shoutbox['id'], '\');">', sp_embed_image('history'), '</button>';

	if ($context['can_shout'])
		echo ' <button type="button" style="border: 0px;vertical-align: top;padding: 0px;" onclick="sp_collapse_object(\'sb_smiley_', $shoutbox['id'], '\', false); return false;">', sp_embed_image('smiley'), '</button> <button type="button" style="border: 0px;vertical-align: top;padding: 0px;" onclick="sp_collapse_object(\'sb_style_', $shoutbox['id'], '\', false); return false;">', sp_embed_image('style'), '</button>';

	echo '
			</div>';

	if ($context['can_shout'])
	{
		echo '
			<div class="shoutbox_smileys" id="sp_object_sb_smiley_', $shoutbox['id'], '" style="display: none;">';

		foreach ($shoutbox['smileys']['normal'] as $smiley)
		{
			if (empty($modSettings['simple_smileys_useall']))
				echo '
				<a href="javascript:void(0);" onclick="replaceText(\' ', $smiley['code'], '\', document.getElementsByClassName(\'new_shout_', $shoutbox['id'], '\')[0]); return false;"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" alt="', $smiley['description'], '" title="', $smiley['description'], '" /></a>';
			else
			{
				$path = array_pop(explode('/', $settings['smileys_url']));
				$newPath = getSimpleSmileyImgShoutbox($path, $smiley['filename']);
				$file = rtrim($settings['smileys_url'], $path) . $newPath . '/' . $smiley['filename'];
				echo '
				<a href="javascript:void(0);" onclick="replaceText(\' ', $smiley['code'], '\', document.getElementsByClassName(\'new_shout_', $shoutbox['id'], '\')[0]); return false;"><img src="', $file, '" alt="', $smiley['description'], '" title="', $smiley['description'], '" /></a>';
			}
		}

		if (!empty($shoutbox['smileys']['popup']))
		{
				echo '
				<a onclick="sp_showMoreSmileys(\'', $shoutbox['id'], '\', \'', $txt['more_smileys_title'], '\', \'', $txt['more_smileys_pick'], '\', \'', $txt['more_smileys_close_window'], '\', \'', $settings['theme_url'], '\', \'', $settings['smileys_url'], '\'); return false;" href="javascript:void(0);">[', $txt['more_smileys'], ']</a>';
		}

		echo '
			</div>
			<div class="shoutbox_styles" id="sp_object_sb_style_', $shoutbox['id'], '" style="display: none;">';

		foreach ($shoutbox['bbc'] as $image => $tag)
		{
			if (!in_array($tag['code'], $shoutbox['allowed_bbc']))
				continue;

			if (!isset($tag['after']))
				echo '<a href="javascript:void(0);" onclick="replaceText(\'', $tag['before'], '\', document.getElementsByClassName(\'new_shout_', $shoutbox['id'], '\')[0]); return false;">';
			else
				echo '<a href="javascript:void(0);" onclick="surroundText(\'', $tag['before'], '\', \'', $tag['after'], '\', document.getElementsByClassName(\'new_shout_', $shoutbox['id'], '\')[0]); return false;">';

			echo '<img onmouseover="style_highlight(this, true);" onmouseout="if (window.style_highlight) style_highlight(this, false);" src="', $settings['images_url'], '/sp_shoutbox/', $image, '.png" align="bottom" alt="', $tag['description'], '" title="', $tag['description'], '" style="width: 23px; height: 23px; background-image: url(', $settings['images_url'], '/sp_shoutbox/bbc_bg.png); margin: 1px 2px 1px 1px;" /></a>';
		}

		echo '
			</div>';
	}

	if (!empty($shoutbox['warning']))
			echo '
			<div class="shoutbox_warning smalltext">', $shoutbox['warning'], '</div>';

	// this will be populated via jQuery & the shoutbox_refresh function but populate it here at its onset
	echo '
			<div class="message_box message_box_', $shoutbox['id'],'" id="message_box_', $shoutbox['id'],'" style="overflow: hidden;">
				<ul class="shoutbox_list_compact shouts_', $shoutbox['id'], '" id="shouts_', $shoutbox['id'], '"', !empty($shoutbox['height']) ? ' style="height: ' . $shoutbox['height'] . 'px;"' : '', '>';

	if (!empty($shoutbox['shouts']))
	{
		$x = 0;
		foreach ($shoutbox['shouts'] as $shout)
		{
			$x++;
			echo '
					<li ', ($x == count($shoutbox['shouts']) ? 'id="lastshout_' . $shoutbox['id'] . '" ' : ''),'class="shouts smalltext">', ($shout['is_me'] ? '<i><span style="color: ' . $shout['author']['color'] . '">' . $shout['author']['name']. ':</span></i> ' : '<strong>' . $shout['author']['private'] . '</strong>: '),  $shout['text'], '<br />', !empty($shout['delete_link_js']) ? '<span class="shoutbox_delete">' . $shout['delete_link_js'] . '</span>' : '' , '<span class="smalltext shoutbox_time">', $shout['time'], '</span></li>';

		}
	}
	else
		echo '
					<li class="smalltext">', $txt['sp_shoutbox_no_shout'], '</li>';

	echo '
				</ul>
			</div>';

	if ($context['can_shout'])
		echo '
			 <div class="user_info">
				<input onkeypress="EhShoutKeyDown(event, ' . $shoutbox['id'] . ');" maxlength="300" name="shout_message_' . $shoutbox['id'] . '" id="shout_message_' . $shoutbox['id'] . '" class="new_shout_' . $shoutbox['id'] . '" type="text" placeholder="Type Message Hit Enter" />
				<div style="padding: 4px;"><button class="button" onclick="EhShoutEnter(' . $shoutbox['id'] . ');" name="shout_username_', $shoutbox['id'], '" id="shout_username_', $shoutbox['id'], '" type="button" value="', $user_info['name'],'">', $txt['sp_shoutbox_button'], '</button></div>
			</div>';

	echo '
		</div>
	<div class="ehshoutboxflag" id="ehshoutboxflag', $shoutbox['id'], '" style="display: none;">', $shoutbox['id'], '</div>
	<script><!-- // --><![CDATA[
		var last_refresh_', $shoutbox['id'], ' = ', time(), ';
		var ehshoutreverse = ', !empty($shoutbox['reverse']) ? 1 : 0, ';
		var ehshoutboxid = ', $shoutbox['id'], ';
		var ehShoutSess = ["', $context['session_var'], '","', $context['session_id'], '"];
		if (typeof ehshoutrefresh != "object")
			var ehshoutrefresh = [];
		ehshoutrefresh[' . $shoutbox['id'] . '] = ', $shoutbox['refresh'], ';';

	if ($shoutbox['reverse'])
		echo '
		var objDiv = document.getElementById("shouts_', $shoutbox['id'], '");
		objDiv.scrollTop = objDiv.scrollHeight;';

	// Setup the data for the popup smileys.
	if (!empty($shoutbox['smileys']['popup']))
	{
		echo '
		if (sp_smileys == undefined)
			var sp_smileys = [';
		foreach ($shoutbox['smileys']['popup'] as $smiley)
		{
			echo '
					["', $smiley['code'], '","', $smiley['filename'], '","', $smiley['js_description'], '"]';
			if (empty($smiley['last']))
				echo ',';
		}
		echo ']';

		echo '
		if (sp_moreSmileysTemplate == undefined)
		{
			var sp_moreSmileysTemplate =  ', JavaScriptEscape('<!DOCTYPE html>
					<html>
						<head>
							<title>' . $txt['more_smileys_title'] . '</title>
							<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/index' . $context['theme_variant'] . '.css?fin20" />
						</head>
						<body id="help_popup">
							<div class="padding windowbg">
								<div class="cat_bar">
									<h3 class="catbg">
										' . $txt['more_smileys_pick'] . '
									</h3>
								</div>
								<div class="padding">
									%smileyRows%
								</div>
								<div class="smalltext centertext">
									<a href="javascript:window.close();">' . $txt['more_smileys_close_window'] . '</a>
								</div>
							</div>
						</body>
					</html>'), '
		}';
	}

	echo '
	// ]]></script>';

	// shout audio mutation callback
	if (!empty($shoutbox['shouts']) && !empty($modSettings['sp_sbSoundEnable']) && !empty($user_info['ehportal_enable_audio']))
		echo '
	<script>
		$(document).ready(function() {
			var targetNode = document.getElementById("message_box_', $shoutbox['id'],'");
			var config = {attributes: false, childList: true, subtree: false};
			var targetTime = [];
			var oldTime = [];
			var targetUser = [];
			var targetRefreshTime = [];
			var targetNowTime = [];
			oldTime["', $shoutbox['id'], '"] = 0;
			var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
			var callback = function(mutationsList) {
				for(var mutation of mutationsList) {
					if (mutation.type == "childList") {
						targetTime["', $shoutbox['id'], '"] = document.getElementById("ehlasttime_', $shoutbox['id'], '") ? document.getElementById("ehlasttime_', $shoutbox['id'], '").innerHTML : 0;
						targetUser["', $shoutbox['id'], '"] = document.getElementById("ehlastuserid_', $shoutbox['id'], '") ? document.getElementById("ehlastuserid_', $shoutbox['id'], '").innerHTML : 0;
						targetRefreshTime["', $shoutbox['id'], '"] = document.getElementById("ehrefreshtime_', $shoutbox['id'], '") ? document.getElementById("ehrefreshtime_', $shoutbox['id'], '").innerHTML : 0;
						targetNowTime["', $shoutbox['id'], '"] = document.getElementById("ehnowtime_', $shoutbox['id'], '") ? document.getElementById("ehnowtime_', $shoutbox['id'], '").innerHTML : 0;
						if (oldTime["', $shoutbox['id'], '"] != targetTime["', $shoutbox['id'], '"] && oldTime["', $shoutbox['id'], '"] != 0 && targetUser["', $shoutbox['id'], '"] != 0 && targetUser["', $shoutbox['id'], '"] != "', $user_info['id'], '")
						{
							if (parseInt(targetNowTime["', $shoutbox['id'], '"]) - parseInt(targetTime["', $shoutbox['id'], '"]) <= parseInt(targetRefreshTime["', $shoutbox['id'], '"])+2)
							{
								spSound();
							}
						}
						else
							oldTime["', $shoutbox['id'], '"] = targetTime["', $shoutbox['id'], '"];
					}
				}
			};
			var observer = new MutationObserver(callback);
			observer.observe(targetNode, config);
		});
	</script>';
}

function template_shoutbox_refresh()
{
	global $context, $txt, $modSettings, $user_info, $boardurl;

	$id = !empty($_POST['shoutbox_id']) ? $_POST['shoutbox_id'] : (!empty($context['SPortal']['shouts']) ? $context['SPortal']['shouts'][0]['shoutbox_id'] : 0);
	list($time, $userid, $audio, $refresh, $nowTime) = array(0, 0, '', 100, time());
	$file = !empty($modSettings['sp_sbSound']) ? $modSettings['sp_sbSound'] : 'default.mp3';

	// shout audio
	if (!empty($context['SPortal']['shouts']) && !empty($modSettings['sp_sbSoundEnable']) && !empty($user_info['ehportal_enable_audio']))
	{
		$refArray = $context['SPortal']['shouts'];
		$key = empty($context['SPortal']['shouts'][0]['reverse']) ? 0 : count($refArray) - 1;
		$refresh = $refArray[$key]['refresh'];
		$userid = $refArray[$key]['author']['id'];
		$time = $refArray[$key]['raw_time'];
	}
	else
		$audio = '';

	echo '
				<div style="display: none;" id="ehaudiofile_', $id, '">', rtrim($file, '.mp3'), '</div>
				<div style="display: none;" id="ehlasttime_', $id, '">', $time, '</div>
				<div style="display: none;" id="ehlastuserid_', $id, '">', $userid, '</div>
				<div style="display: none;" id="ehrefreshtime_', $id, '">', $refresh, '</div>
				<div style="display: none;" id="ehnowtime_', $id, '">', $nowTime, '</div>
				<ul class="shoutbox_list_compact shouts_', $id, '" id="shouts_', $id, '"', !empty($context['SPortal']['shouts'][0]['height']) ? ' style="height: ' . $context['SPortal']['shouts'][0]['height'] . 'px;"' : '', '>';

	if (!empty($context['SPortal']['shouts']) && !empty($id))
	{
		$x = 0;
		if (count($context['SPortal']['shouts']) > 1)
			foreach ($context['SPortal']['shouts'] as $shout)
			{
				$x++;
				if (empty($context['SPortal']['shouts'][0]['reverse']) && $x != 1)
					$audio = '';
				elseif (!empty($context['SPortal']['shouts'][0]['reverse']) && $x != count($context['SPortal']['shouts']))
					$audio = '';

				echo '
					<li ', ($x == count($context['SPortal']['shouts']) ? 'id="lastshout_' . $shout['shoutbox_id'] . '" ' : ''),'class="shouts smalltext">', ($shout['is_me'] ? '<i><span style="color: ' . $shout['color'] . '">' . $shout['name']. ':</span></i> ' : '<strong>' . $shout['author']['private'] . '</strong>: '),  preg_replace('~<a([^>]+>)([^<]+)</a>~', (!empty($shout['disable_links']) ? '<a href="#">' . $txt['sp_link'] . '</a>': '<a$1' . $txt['sp_link'] . '</a>'), autoShoutLink(parse_bbc($shout['text']))), '<br />', !empty($shout['delete_link_js']) ? '<span class="shoutbox_delete">' . $shout['delete_link_js'] . '</span>' : '' , '<span class="smalltext shoutbox_time">', $shout['time'], '</span>' . $audio . '</li>';
			}
	}
	else
			echo '
					<li class="smalltext">', $txt['sp_shoutbox_no_shout'], '</li>';

	echo '
				</ul>
				<script>
					console.clear();';

	if (!empty($context['SPortal']['shouts'][0]['reverse']))
		echo '
					var objDiv = document.getElementById("shouts_', $id, '");
					objDiv.scrollTop = objDiv.scrollHeight;';

	echo '
				</script>';
}

?>