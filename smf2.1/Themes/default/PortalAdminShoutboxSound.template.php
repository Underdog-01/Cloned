<?php

/*
	<id>chenzhen:shoutAudio</id>
	<name>SP-Shoutbox Audio</name>
	<version>2.1</version>
	<type>modification</type>
*/

function template_shoutbox_audio()
{
	global $scripturl, $context, $txt;

	echo '
	<form onsubmit="return confirmMp3Submission(buttonIndex);" id="audio_form" action="', $scripturl, '?action=admin;area=portalshoutbox;sa=editaudio" method="post" enctype="multipart/form-data" accept-charset="', $context['character_set'], '">
		<div style="padding-top: 10px;"><span></span></div>
		<div class="title_bar">
			<h4 class="titlebg centertext">
				', $txt['sp_sbSound_title_main'], '
			</h4>
		</div>
		<div class="information">
			<div style="padding: 2px 0px 5px 0px;">
				<span></span>
			</div>
			<audio id="player"></audio>
			<div id="playlist" style="list-style-type: none;padding-bottom: 2px;">
				<div style="display: table;border-collapse: collapse;width: 65%;margin: auto;">
					<div class="titlebg2" style="cursor: pointer;display: table-row;vertical-align: middle;position: relative;padding: 0.5em 1em;font-size: 0.9em;line-height: 1.4em;margin: 1em 1px 0 1px;text-decoration: underline;">
						<div class="smalltext" style="display: table-cell;width: 5%;padding: 5px 10px;">
							', $txt['sp_sbSound_del'], '
						</div>
						<div class="smalltext" style="display: table-cell;width: 35%;padding: 5px 10px;">
							<span>', $context['columns']['name']['link'], '</span>
						</div>
						<div class="smalltext" style="display: table-cell;width: 10%;padding: 5px 10px;">
							', $context['columns']['size']['link'], '
						</div>
						<div class="smalltext" style="display: table-cell;width: 10%;padding: 5px 10px;">
							', $context['columns']['date']['link'], '
						</div>
						<div class="smalltext" style="display: table-cell;width: 5%;padding: 5px 10px;text-align: right;">
							<span style="text-align: right;">', $txt['sp_sbSound_sel'], '</span>
						</div>
					</div>';

		foreach ($context['shoutbox_sb_files'] as $filex)
		{
			echo '
					<div class="titlebg2" style="display: table-row;cursor: pointer;padding: 5px 10px;">
						<div class="smalltext" style="display: table-cell;width: 5%;padding: 5px 10px;">
							<input class="input_check confirm_checkx" onclick="playlistCheckClick(\'' . $filex['id'] . '\')" type="checkbox" id="mp3del_', $filex['id'], '" name="del[', $filex['id'], ']" value="', $filex['name'], '" style="vertical-align: middle;" />
						</div>
						<div class="smalltext" style="display: table-cell;width: 35%;padding: 5px 10px;">
							<span id="' . $filex['id'] . '" data-mp3="', $filex['path'], '">', ($filex['name'] == $context['sp_admin_shoutbox_audioFile'] ? '&#060;&#060;' . $filex['namex'] . '&#062;&#062;' : $filex['namex']), '</span>
						</div>
						<div onclick="playlistRadioClick(\'' . $filex['id'] . '\')" id="mp3size_' . $filex['id'] . '" class="smalltext changemp3" style="display: table-cell;width: 10%;padding: 5px 10px;">
							', $filex['size'],' KB
						</div>
						<div onclick="playlistRadioClick(\'' . $filex['id'] . '\')" id="mp3date_' . $filex['id'] . '" class="smalltext changemp3" style="font-size: 8px;display: table-cell;width: 10%;padding: 5px 10px;">
							', date("Y-m-d h:i:s", $filex['date']),'
						</div>
						<div class="smalltext changemp3" style="display: table-cell;width: 5%;padding: 5px 10px;">
							<input class="input_radio confirm_radio" onclick="playlistRadioClick(\'' . $filex['id'] . '\')" id="mp3radio_' . $filex['id'] . '" type="radio" name="mp3option" value="', $filex['name'], '" style="vertical-align: middle;float: right;" />
						</div>
					</div>';
		}

		echo '
					<div class="titlebg2" style="display: table-row;cursor: pointer;padding: 5px 10px;">
						<div onclick="checkMp3All();" class="smalltext" style="display: table-cell;width: 5%;padding-left: 5px;font-size: 10px;">
							<button type="button" class="button_submit" style="vertical-align: middle;">
								<div id="checkMp3All" style="display: inline;">', $txt['sp_admin_shoutbox_audioFileDeleteAllOn'], '</div>
							</button>
						</div>
						<div class="smalltext" style="display: table-cell;width: 35%;padding: 5px 10px;">
							<span></span>
						</div>
						<div class="smalltext" style="display: table-cell;width: 10%;padding: 5px 10px;">
							<span></span>
						</div>
						<div class="smalltext" style="font-size: 8px;display: table-cell;width: 10%;padding: 5px 10px;">
							<span></span>
						</div>
						<div class="smalltext" style="display: table-cell;width: 5%;padding: 5px 10px;">
							<span></span>
						</div>
					</div>
				</div>
			</div>
			<div style="padding-bottom: 2px;display: table;width: 100%;">
				<div style="display: table-row;">
					<div style="display: table-cell;width: 50%;" class="centertext">
						<button class="button_submit" id="stop" onclick="getElementById(\'player\').pause();return false;">', $txt['sp_admin_shoutbox_audioStop'], '</button>
					</div>
					<div style="display: table-cell;width: 50%;" class="centertext">
						<input onclick="buttonIndex=1;" class="button_submit" type="submit" name="submit_file" value="', $txt['sp_admin_shoutbox_audioFileSubmit'], '" />
					</div>
				</div>
			</div>
			<div style="float: right;display: inline;padding-right: 5px;">page: ', $context['page_index'], '</div>
		</div>
		<div style="padding-top: 30px;"><span></span></div>
		<div class="title_bar">
			<h4 class="titlebg">
				<span class="smalltext" style="display: table;width: 100%;">
					<span style="display: table-row;vertical-align: middle;position: relative;padding: 0.5em 1em;font-size: 0.9em;line-height: 1.4em;margin: 1em 1px 0 1px;text-decoration: underline;">
						<span style="display: table-cell;padding-left: 5%;text-align: left;width: 33%;">', $txt['sp_sbSound_title_upload'], '</span>
						<span class="centertext" style="display: table-cell;margin: auto;width: 33%;">', $txt['sp_sbSound_title_current'], '</span>
						<span style="display: table-cell;padding-right: 5%;text-align: right;width: 33%;">', $txt['sp_sbSound_title_file'], '</span>
					</span>
				</span>
			</h4>
		</div>
		<div class="information">
			<div style="display: table;width: 100%;border-collapse: collapse;">
				<div style="display: table-row;">
					<div class="content windowbg3" style="padding: 30px;display: table-cell;">
						<div style="padding: 0.5em;" id="audio_container">
							<div style="padding: 2px 0px 15px 0px;font-family: tahoma;" class="mediumtext">
								<span style="border: 1px solid;padding: 2px;">', $txt['sp_admin_shoutbox_audioTypes'], '</span>
							</div>
							<input type="hidden" name="MAX_FILE_SIZE" value="40000" />
							<input class="input_file confirm_file" accept=".mp3" type="file" size="48" name="attachment[]" multiple /><br />
							<div style="padding: 4px 0px 4px 0px;" class="smalltext">', $txt['sp_admin_shoutbox_audioMax'], '</div>
							<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
						</div>
					</div>
					<div class="content windowbg3" style="padding: 30px 30px 30px 30px;display: table-cell;text-align: right;">
						<div>
							<input class="input_check confirm_checky" style="vertical-align: middle;" type="checkbox" name="sp_sbSoundEnable" ', (!empty($context['sp_admin_shoutbox_audioEnable']) ? 'checked ' : ''), '/>
						</div>
						<div style="padding-top: 15px;">
							<input class="input_check confirm_texty" style="vertical-align: middle;" type="checkbox" name="sp_sbSoundInterval" />
						</div>
					</div>
					<div class="content windowbg3" style="padding: 30px 30px 30px 1px;display: table-cell;">
						<div>
							<span style="width: 100px;">', $txt['sp_sbSoundEnable'], '</span>
						</div>
						<div style="padding-top: 20px;">
							<span style="width: 100px;">', $txt['sp_sbSoundIntervalReset'], '</span>
						</div>
					</div>
					<div class="content windowbg3" style="padding: 30px 30px 30px 1px;display: table-cell;text-align: right;">
						<div>
							<span style="width: 200px;">', $txt['sp_admin_shoutbox_audioFile'], '</span>
						</div>
						<div style="padding-top: 20px;">
							<span class="active" style="width: 200px;font-size: 20px;">', $context['sp_admin_shoutbox_audioFile'], '</span>
						</div>
					</div>
				</div>
			</div>
			<div style="display: table;width: 100%;border-collapse: collapse;">
				<div style="display: table-row;">
					<div class="content windowbg3" style="padding: 15px;display: table-cell;width: 10%;">
						<span></span>
					</div>
					<div class="content windowbg3" style="padding: 15px;display: table-cell;width: 80%;">
						<div id="oshoutAdminScroll" style="margin : 0px;padding : 0px;position : relative;height : 20px;overflow : hidden;">
							<div id="shoutAdminScroll" style="position : absolute;white-space : nowrap;top : 0px;">', $context['sp_admin_shoutbox_audioMessage'], '</div>
						</div>
					</div>
					<div class="content windowbg3" style="padding: 15px;display: table-cell;width: 10%;">
						<span></span>
					</div>
				</div>
			</div>
			<div class="righttext">
				<span style="padding: 15px 5px 2px 0px;display: block;position: relative;">
					<input onclick="buttonIndex=2;" class="button_submit" type="submit" name="submit" value="', $txt['sp_admin_shoutbox_audioSubmit'], '" />
				</span>
			</div>
		</div>
	</form>';
}

?>