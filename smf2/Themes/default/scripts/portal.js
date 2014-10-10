/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause 
 *
 * @version 2.4
 */
function sp_collapse_object(id, has_image)
{
	mode = document.getElementById("sp_object_" + id).style.display == '' ? 0 : 1;
	document.getElementById("sp_object_" + id).style.display = mode ? '' : 'none';

	if (typeof(has_image) == "undefined" || has_image == true)
		document.getElementById("sp_collapse_" + id).src = smf_images_url + (mode ? '/collapse.gif' : '/expand.gif');
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

function sp_submit_shout(shoutbox_id, sSessionVar, sSessionId)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		var shout_body = "";

		shout_body = escape(document.getElementById('new_shout_' + shoutbox_id).value.replace(/&#/g, "&#").php_to8bit()).replace(/\+/g, "%2B");

		sendXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=portal;sa=shoutbox;xml', 'shoutbox_id=' + shoutbox_id + '&shout=' + shout_body + '&' + sSessionVar + '=' + sSessionId, onShoutReceived);

		document.getElementById('new_shout_' + shoutbox_id).value = '';

		return false;
	}
}

function sp_delete_shout(shoutbox_id, shout_id, sSessionVar, sSessionId)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		sendXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=portal;sa=shoutbox;xml', 'shoutbox_id=' + shoutbox_id +  '&delete=' + shout_id + '&' + sSessionVar + '=' + sSessionId, onShoutReceived);

		return false;
	}
}

function sp_refresh_shout(shoutbox_id, last_refresh)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=portal;sa=shoutbox;shoutbox_id=' + shoutbox_id + ';time=' + last_refresh + ';xml', onShoutReceived);

		return false;
	}
}

function onShoutReceived(XMLDoc)
{
	var shouts = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("shout");
	var shoutbox_id, updated, error, warning, reverse, shout, id, author, time, timeclean, delete_link, content, is_me, new_body = '';

	shoutbox_id = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("shoutbox")[0].childNodes[0].nodeValue;
	updated = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("updated")[0].childNodes[0].nodeValue;

	if (updated == 1)
	{
		error = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("error")[0].childNodes[0].nodeValue;
		warning = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("warning")[0].childNodes[0].nodeValue;
		reverse = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("reverse")[0].childNodes[0].nodeValue;

		if (warning != 0)
			new_body += '<li class="shoutbox_warning smalltext">' + warning + '</li>';

		if (error != 0)
			setInnerHTML(document.getElementById('shouts_' + shoutbox_id), new_body + '<li class="smalltext">' + error + '</li>');
		else
		{
			for (var i = 0; i < shouts.length; i++)
			{
				shout = XMLDoc.getElementsByTagName("smf")[0].getElementsByTagName("shout")[i];
				id = shout.getElementsByTagName("id")[0].childNodes[0].nodeValue;
				author = shout.getElementsByTagName("author")[0].childNodes[0].nodeValue;
				time = shout.getElementsByTagName("time")[0].childNodes[0].nodeValue;
				timeclean = shout.getElementsByTagName("timeclean")[0].childNodes[0].nodeValue;
				delete_link = shout.getElementsByTagName("delete")[0].childNodes[0].nodeValue;
				content = shout.getElementsByTagName("content")[0].childNodes[0].nodeValue;
				is_me = shout.getElementsByTagName("is_me")[0].childNodes[0].nodeValue;

				new_body += '<li class="smalltext">' + (is_me == 0 ? '<strong>' + author + ':</strong> ' : '') + content + '<br />' + (delete_link != 0 ? ('<span class="shoutbox_delete">' + delete_link + '</span>') : '') + '<span class="smalltext shoutbox_time">' + time + '</span></li>';
			}

			setInnerHTML(document.getElementById('shouts_' + shoutbox_id), new_body);

			if (reverse != 0)
				document.getElementById('shouts_' + shoutbox_id).scrollTop = document.getElementById('shouts_' + shoutbox_id).scrollHeight;
			else
				document.getElementById('shouts_' + shoutbox_id).scrollTop = 0;

			var sp_date = new Date;
			eval("last_refresh_" + shoutbox_id + " = " + Math.round(sp_date.getTime() / 1000, 0) + ";");
		}
	}

	shoutbox_indicator(shoutbox_id, false);

	return false;
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
	something.style.backgroundImage = 'url(' + smf_images_url + (mode ? '/bbc/bbc_hoverbg.gif)' : '/bbc/bbc_bg.gif)');
}

function smf_prepareScriptUrl(sUrl)
{
	return sUrl.indexOf('?') == -1 ? sUrl + '?' : sUrl + (sUrl.charAt(sUrl.length - 1) == '?' || sUrl.charAt(sUrl.length - 1) == '&' || sUrl.charAt(sUrl.length - 1) == ';' ? '' : ';');
}

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

	this.oSmileyPopupWindow.document.open('text/html', 'replace');
	this.oSmileyPopupWindow.document.write(sp_moreSmileysTemplate.easyReplace({
		smileyRows: sp_smileyRowsContent
	}));

	this.oSmileyPopupWindow.document.close();
}