// Version 1.2; portal.js

// Define the version of SMF that we are using.
var portal_smf_version = 2.1;

function sp_collapse_object(id, has_image)
{
	mode = document.getElementById("sp_object_" + id).style.display == '' ? 0 : 1;
	document.getElementById("sp_object_" + id).style.display = mode ? '' : 'none';

	if (typeof(has_image) == "undefined" || has_image == true)
		document.getElementById("sp_collapse_" + id).src = smf_default_theme_url + '/images/sp' + (mode ? '/collapse.png' : '/expand.png');
}

function sp_image_resize()
{
	var possible_images = document.getElementsByTagName("img");
	for (var i = 0; i < possible_images.length; i++)
	{
		if (possible_images[i].className != "bbc_img sp_article")
			continue;

		var temp_image = new Image();
		temp_image.src = possible_images[i].src;

		if (temp_image.width > 300)
		{
			possible_images[i].height = (300 * temp_image.height) / temp_image.width;
			possible_images[i].width = 300;
		}
		else
		{
			possible_images[i].width = temp_image.width;
			possible_images[i].height = temp_image.height;
		}
	}

	if (typeof(window_oldSPImageOnload) != "undefined" && window_oldSPImageOnload)
	{
		window_oldSPImageOnload();
		window_oldSPImageOnload = null;
	}
}

function sp_delete_shout(shoutbox_id, shout_id, sSessionVar, sSessionId)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);
		var shoutx = ["shoutbox_id=" + shoutbox_id, "delete=" + shout_id, sSessionVar + "=" + sSessionId];
		sendXMLDocument(sp_prepareScriptUrl(sp_script_url) + 'action=portal;sa=shoutbox;xml', shoutx.join("&"), onShoutReceived);
		return false;
	}
}

function shoutbox_indicator(shoutbox_id, turn_on)
{
	document.getElementById('shoutbox_load_' + shoutbox_id).style.display = turn_on ? '' : 'none';
}

function sp_catch_enter(key)
{
	var keycode;

	if (window.event)
		keycode = window.event.keyCode;
	else if (key)
		keycode = key.which;

	if (keycode == 13)
		return true;
}

function sp_show_ignored_shout(shout_id)
{
	document.getElementById('ignored_shout_' + shout_id).style.display = '';
	document.getElementById('ignored_shout_link_' + shout_id).style.display = 'none';
}

function sp_show_history_ignored_shout(shout_id)
{
	document.getElementById('history_ignored_shout_' + shout_id).style.display = '';
	document.getElementById('history_ignored_shout_link_' + shout_id).style.display = 'none';
}

function style_highlight(something, mode)
{
	something.style.backgroundImage = 'url(' + smf_default_theme_url + '/images' + (mode ? '/sp_shoutbox/bbc_hoverbg.png)' : '/sp_shoutbox/bbc_bg.png)');
}

function sp_prepareScriptUrl(sUrl)
{
	return sUrl.indexOf('?') == -1 ? sUrl + '?' : sUrl + (sUrl.charAt(sUrl.length - 1) == '?' || sUrl.charAt(sUrl.length - 1) == '&' || sUrl.charAt(sUrl.length - 1) == ';' ? '' : ';');
}

// This function is for SMF 1.1.x as well as SMF 2RC1.2 and below.
function sp_compat_showMoreSmileys(postbox, sTitleText, sPickText, sCloseText, smf_theme_url, smf_smileys_url)
{
	if (this.oSmileyPopupWindow)
		this.oSmileyPopupWindow.close();

	this.oSmileyPopupWindow = window.open('', 'add_smileys', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes');
	this.oSmileyPopupWindow.document.write('<!DOCTYPE html>\n<html>');
	this.oSmileyPopupWindow.document.write('\n\t<head>\n\t\t<title>' + sTitleText + '</title>\n\t\t<link rel="stylesheet" type="text/css" href="' + smf_theme_url + '/style.css" />\n\t</head>');
	this.oSmileyPopupWindow.document.write('\n\t<body style="margin: 1ex;">\n\t\t<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">\n\t\t\t<tr class="titlebg"><td align="left">' + sPickText + '</td></tr>\n\t\t\t<tr class="windowbg"><td align="left">');

	for (i = 0; i < sp_smileys.length; i++)
	{
		sp_smileys[i][2] = sp_smileys[i][2].replace(/"/g, '&quot;');
		sp_smileys[i][0] = sp_smileys[i][0].replace(/"/g, '&quot;');
		this.oSmileyPopupWindow.document.write('<a href="javascript:void(0);" onclick="window.opener.replaceText(\' ' + smf_addslashes(sp_smileys[i][0]) + '\', window.opener.document.getElementById(\'new_shout_' + postbox + '\')); window.focus(); return false;"><img src="' + smf_smileys_url + '/' + sp_smileys[i][1] + '" id="sml_' + sp_smileys[i][1] + '" alt="' + sp_smileys[i][2] + '" title="' + sp_smileys[i][2] + '" style="padding: 4px;" border="0" /></a> ');
	}

	this.oSmileyPopupWindow.document.write('</td></tr>\n\t\t\t<tr><td align="center" class="windowbg"><a href="javascript:window.close();">' + sCloseText + '</a></td></tr>\n\t\t</table>');
	this.oSmileyPopupWindow.document.write('\n\t</body>\n</html>');
	this.oSmileyPopupWindow.document.close();
}

// This function is for SMF 2 RC2 and above.
function sp_showMoreSmileys(postbox, sTitleText, sPickText, sCloseText, smf_theme_url, smf_smileys_url)
{
	if (this.oSmileyPopupWindow != null && 'closed' in this.oSmileyPopupWindow && !this.oSmileyPopupWindow.closed)
	{
		this.oSmileyPopupWindow.focus();
		return;
	}

	if (sp_smileyRowsContent == undefined)
	{
		var sp_smileyRowsContent = '';
		for (i = 0; i < sp_smileys.length; i++)
		{
			sp_smileys[i][2] = sp_smileys[i][2].replace(/"/g, '&quot;');
			sp_smileys[i][0] = sp_smileys[i][0].replace(/"/g, '&quot;');
			sp_smileyRowsContent += '<a href="javascript:void(0);" onclick="window.opener.replaceText(\' ' + sp_smileys[i][0].php_addslashes() + '\', window.opener.document.getElementById(\'new_shout_' + postbox + '\')); window.focus(); return false;"><img src="' + smf_smileys_url + '/' + sp_smileys[i][1] + '" id="sml_' + sp_smileys[i][1] + '" alt="' + sp_smileys[i][2] + '" title="' + sp_smileys[i][2] + '" style="padding: 4px;" border="0" /></a> ';
		}

	}

	this.oSmileyPopupWindow = window.open('', 'add_smileys', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes');

	// Paste the template in the popup.
	this.oSmileyPopupWindow.document.open('text/html', 'replace');
	this.oSmileyPopupWindow.document.write(sp_moreSmileysTemplate.easyReplace({
		smileyRows: sp_smileyRowsContent
	}));

	this.oSmileyPopupWindow.document.close();
}

// This is for resizing the content section
function sp_resizeContentSection()
{
	var top = $('#top_info').height();
	var header = $('#header').height();
	var footer = 280;
	var sum = top + header + footer;
	var total = $(window).height() > $(document).height() - sum ? $(document).height() - sum : $('#sp_main').height();
	$('#sp_main').css({'min-height': total + 'px'});
}

// This is for converting HTML entities
function sp_decodeHtml(html)
{
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

$(document).ready(function() {
	var allEhShoutboxes = document.getElementsByClassName('ehshoutboxflag');
	var shoutboxidz, i;
	var i, j;
	var EhShoutTimers = [];
	var EhShoutTimersSort = [];
	var EhShoutTimersy = [];
	window.lastShout = [];
	var EhShoutConfig = {attributes: true, childList: true, subtree: true};
	EhShoutTimers[0] = {'timer':99999};
	if (typeof allEhShoutboxes == "object" && allEhShoutboxes.length > 0)
	{
		for(i=0;i<allEhShoutboxes.length;i++)
		{
			shoutboxidz = allEhShoutboxes[i].innerHTML;
			var shoutRefresh = ehshoutrefresh[shoutboxidz];
			shoutRefresh = parseInt(shoutRefresh) * 1000;
			EhShoutTimersy[i] = {'args':shoutboxidz, 'timer':shoutRefresh};
		}

		// sort all shoutboxes by ascending refresh times
		EhShoutTimersSort = EhShoutTimersy.sort(EhSortArray);
		EhShoutTimers = EhCascadeArray(EhShoutTimersSort);

		//alert(JSON.stringify(EhShoutTimers));
		// start the sorted cascade of intervals
		var ehIntervalTime = EhShoutTimers[0] ? EhShoutTimers[0].timer : 300;
		setInterval(function() {
			var EhTimeout;
			clearTimeout(EhTimeout);
			EhRecursiveSetTimeouts(EhShoutTimers, 0);
		}, ehIntervalTime, EhShoutTimers);
	}

	$.urlShoutParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^]*)').exec(window.location.href);
		if (results == null){
		   return null;
		}
		else{
		   return results[1] || 0;
		}
	};
});

function EhShoutLogin(opt)
{
	var shoutboxLogin = document.getElementById("shoutLogin");
	if (opt == true)
		document.getElementById("shoutLogin").className = "alternative2 warn_mute";
	else
		document.getElementById("shoutLogin").className = "alternative";
}

function EhCascadeArray(origArray)
{
	for(var j=0;j<origArray.length;j++)
	{
		var current = origArray[j];
		for(var i=j+1;i<origArray.length;i++)
		{
			if(current.name = origArray[i].name)
			{
				if(!isorigArray(current.value))
					current.value = [ current.value ];

				if(isorigArray(origArray[i].value))
					for(var v=0;v<origArray[i].value.length;v++)
						current.value.push(origArray[i].value[v]);
				else
					current.value.push(origArray[i].value);

				origArray.splice(i,1);
				i++;
			}
		}
	}

	return origArray;
}

function isorigArray(myorigArray) {
    return myorigArray.constructor.toString().indexOf("origArray") > -1;
}


function EhSortArray(a, b)
{
	const timeA = a.timer;
	const timeB = b.timer;

	let comparison = 0;
	if (timeA > timeB) {
		comparison = 1;
	} else if (timeA < timeB) {
		comparison = -1;
	}
	return comparison;
}

function EhRecursiveSetTimeouts(functionsArray, current)
{
	if (current == 0)
		duration = functionsArray[current].timer;
	else if (functionsArray[current].timer > functionsArray[current-1].timer)
		duration = functionsArray[current].timer - functionsArray[current-1].timer;

	let EhTimeout = setTimeout(function() {
		var l;
		var prop;
		// const prop
		for (prop in functionsArray[current])
		{
			l = prop.substring(0, 4);
			if (l == 'args')
				EhShoutRefresh(functionsArray[current][prop]);
		}

	}, duration, functionsArray, current);

	if (functionsArray.length-1 > current)
	{
		EhRecursiveSetTimeouts(functionsArray, current+1);
	}
}

function EhShoutEnter(id)
{
	var e = jQuery.Event("keypress");
	e.which = 13;
	e.keyCode = 13;
	$("#shout_message_" + id).trigger(e);
	return false;
}

function EhShoutKeyDown(event, id)
{
	var elem = document.getElementById('shout_message_' + id);
	var keycodex = event.keyCode || event.which;

	if (keycodex == 13) {
		EhShoutIndicator('shoutbox_load_'+id, true);
		var iusername = $('#shout_username_' + id).val();
		var imessage = $('#shout_message_' + id).val();
		EhShoutShouting(id, iusername, imessage);

		// prevent flood ~ 3 second delay between shout submissions
		setTimeout(function(){
			EhShoutIndicator('shoutbox_load_'+id, false);
		  }, 3000);
	}
	else
		// prevent flood ~ 3 second delay between shout submissions
		setTimeout(function(){
			EhShoutIndicator('shoutbox_load_'+id, false);
		  }, 3000);

	return false;
}

function EhShoutShouting(shoutid, iusername, imessage)
{
	var shoutDir = ehshoutreverse;
	var EhShoutSess = EhShoutSession();
	var shoutmsg = 'message_' + shoutid;
	var post_data = {
		'username': iusername,
		'shoutbox_id': shoutid,
		'shoutdir': shoutDir,
		'fetch': 1
	};
	post_data[shoutmsg] = imessage;

	//send data using jQuery $.post()
	$.post(smf_scripturl + '?action=ehportal_shout;shoutbox_id=' + shoutid + ';shoutbox_dir=' + shoutDir + ';' + EhShoutSess + ';xml2', post_data, function(data) {

		//append data into messagebox with jQuery fade effect!
		$(data).hide().appendTo('#message_box_' + shoutid).fadeIn();

		//keep scrolled to bottom or top of chat!
		var shoutbox_ref = '.message_box_' + shoutid;
		var objDivx = document.getElementById('shouts_' + shoutid);
		$(shoutbox_ref).html(data);
		if (objDivx)
		{
			if (shoutDir == 0 || !objDivx.scrollHeight)
				objDivx.scrollTop = 0;
			else
				objDivx.scrollTop = objDivx.scrollHeight;
		}

		//reset value of message box
		$('#shout_message_' + shoutid).val('');
	}).fail(function(err) {
		//alert HTTP server error
		alert('shout error: ' + err.statusText);
	});

	return EhShoutRefresh(shoutid);
}

function EhShoutDel(shoutid, shoutDel)
{
	var EhShoutSess = EhShoutSession();
	var post_data = {'delete':shoutDel, 'fetch':1, 'shoutbox_id':shoutid};
	var shoutDir = ehshoutreverse;
	//send data using jQuery $.post()
	$.post(smf_scripturl + '?action=ehportal_shout;shoutbox_id=' + shoutid + ';shoutbox_dir=' + shoutDir + ';' + EhShoutSess + ';xml2', post_data, function(data) {
		var shoutbox_ref = '.message_box_' + shoutid;
		var objDivx = document.getElementById('shouts_' + shoutid);
		$(shoutbox_ref).html(data);
		if (objDivx)
		{
			if (shoutDir == 0 || !objDivx.scrollHeight)
				objDivx.scrollTop = 0;
			else
				objDivx.scrollTop = objDivx.scrollHeight;
		}
	}).fail(function(err) {
	});
	return EhShoutRefresh(shoutid);
}

function EhShoutDelHistory(ele)
{
	var EhShoutSess = EhShoutSession();
	var shoutDel = ele.name;
	var post_data = {'delete':shoutDel, 'fetch':1};
	var shoutid = $('#shoutbox_id').val() == 0 ? $.urlShoutParam('shoutbox_id') : $('#shoutbox_id').val();
	shoutid = $('#id_shoutbox').val() && !shoutid ? $('#id_shoutbox').val() : shoutid;
	var shoutDir = $('#shoutbox_dir').val() == 0 ? $.urlShoutParam('shoutbox_dir') : $('#shoutbox_dir').val();
	//send data using jQuery $.post()
	$.post(smf_scripturl + '?action=ehportal_shout_history;shoutbox_id=' + shoutid + ';shoutbox_dir=' + shoutDir + ';' + EhShoutSess + ';xml2', post_data, function(data) {
		$('.message_box').html(data);
		var scrolltoh = $('.message_box')[0].scrollHeight;
		if (shoutDir == 0)
			$('.message_box').scrollTop(scrolltoh);
		else
		{
			items = document.querySelectorAll(".shouts");
			last = items[items.length-1];
			last.parentNode.scrollTop = last.offsetTop;
		}
	}).fail(function(err) {
	});
}

function EhShoutRefresh(id)
{
	var id = typeof id != "undefined" ? id : 0;
	var shoutid = ehshoutboxid && ehshoutboxid != 0 ? ehshoutboxid : $.urlShoutParam('shoutbox_id');
	shoutid = id > 0 ? id : shoutid;
	var shoutDir = ehshoutreverse;
	EhShoutIndicator(shoutid, true);
	var EhShoutSess = EhShoutSession();
	var load_data = {'fetch':1, 'shoutbox_id':shoutid};
	var shoutUserIDX, shoutLastTime, shoutAudioFile;
	$.post(smf_scripturl + '?action=ehportal_shout;shoutbox_id=' + shoutid + ';shoutbox_dir=' + shoutDir + ';' + EhShoutSess + ';xml2', load_data,  function(data){
		var shoutbox_ref = '.message_box_' + shoutid;
		var objDivx = document.getElementById('shouts_' + shoutid);
		$(shoutbox_ref).html(data);
		if (objDivx)
		{
			if (shoutDir == 0 || !objDivx.scrollHeight)
				objDivx.scrollTop = 0;
			else
				objDivx.scrollTop = objDivx.scrollHeight;
		}
	});

	shoutUserIDX = document.getElementById("ehlastuserid_" + shoutid) ? document.getElementById("ehlastuserid_" + shoutid).innerHTML : -2;
	if (smf_member_id != parseInt(shoutUserIDX) && shoutUserIDX != -2) {
		shoutLastTime = document.getElementById("ehlasttime_" + shoutid) ? document.getElementById("ehlasttime_" + shoutid).innerHTML : 0;
		if (window.lastShout[shoutid] == "undefined")
			window.lastShout[shoutid] = shoutLastTime;

		if (shoutLastTime != 0 && window.lastShout[shoutid] != shoutLastTime)
		{
			window.lastShout[shoutid] = shoutLastTime;
			document.getElementById("ehlasttime_" + shoutid).innerHTML = 0;
		}
		else
		{
			shoutAudioFile = document.getElementById("ehaudiofile_" + shoutid) ? document.getElementById("ehaudiofile_" + shoutid).innerHTML : false;
		}
	}
	setTimeout(function(){
		EhShoutIndicator(shoutid, false);
      }, 3000);
	return false;
}
function EhShoutSession()
{
	return ehShoutSess[0] + '=' + ehShoutSess[1];
}

function EhShoutIndicator(shoutbox_id, turn_on)
{
	// document.getElementById(shoutbox_id).style.display = turn_on ? '' : 'none';
	if (!turn_on)
		$('#shoutbox_load_'+shoutbox_id).css('display', 'none')
	else
		$('#shoutbox_load_'+shoutbox_id).css('display', 'inline')
}

function EhShoutHistory(shoutbox_id)
{
	$(location).attr('href', smf_scripturl + '?action=ehportal_shout_history;shoutbox_id=' + shoutbox_id);
	return false;
}
