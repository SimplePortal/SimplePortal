/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2023 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.8
 */

// Define the version of SMF that we are using.
if (typeof(smf_editorArray) == "undefined")
	portal_smf_version = 1.1;
else
	portal_smf_version = 2;

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
		if (possible_images[i].className != (portal_smf_version == 1.1 ? "sp_article" : "bbc_img sp_article"))
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

		if (portal_smf_version == 1.1)
			shout_body = encodeURIComponent(textToEntities(document.getElementById('new_shout_' + shoutbox_id).value.replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");
		else
			shout_body = encodeURIComponent(document.getElementById('new_shout_' + shoutbox_id).value.replace(/&#/g, "&#").php_to8bit()).replace(/\+/g, "%2B");

		sendXMLDocument(smf_prepareScriptUrl(sp_script_url) + 'action=portal;sa=shoutbox;xml', 'shoutbox_id=' + shoutbox_id + '&shout=' + shout_body + '&' + sSessionVar + '=' + sSessionId, onShoutReceived);

		document.getElementById('new_shout_' + shoutbox_id).value = '';

		return false;
	}
}

function sp_delete_shout(shoutbox_id, shout_id, sSessionVar, sSessionId)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		sendXMLDocument(smf_prepareScriptUrl(sp_script_url) + 'action=portal;sa=shoutbox;xml', 'shoutbox_id=' + shoutbox_id +  '&delete=' + shout_id + '&' + sSessionVar + '=' + sSessionId, onShoutReceived);

		return false;
	}
}

function sp_refresh_shout(shoutbox_id, last_refresh)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		getXMLDocument(smf_prepareScriptUrl(sp_script_url) + 'action=portal;sa=shoutbox;shoutbox_id=' + shoutbox_id + ';time=' + last_refresh + ';xml', onShoutReceived);

		return false;
	}
}

// Function to handle the receiving of new shout data from the xml request.
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

// This function is for SMF 1.1.x as well as SMF 2RC1.2 and below.
function sp_compat_showMoreSmileys(postbox, sTitleText, sPickText, sCloseText, smf_theme_url, smf_smileys_url)
{
	if (this.oSmileyPopupWindow)
		this.oSmileyPopupWindow.close();

	this.oSmileyPopupWindow = window.open('', 'add_smileys', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes');
	this.oSmileyPopupWindow.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html>');
	this.oSmileyPopupWindow.document.write('\n\t<head>\n\t\t<title>' + sTitleText + '</title>\n\t\t<link rel="stylesheet" type="text/css" href="' + smf_theme_url + '/style.css" />\n\t</head>');
	this.oSmileyPopupWindow.document.write('\n\t<body style="margin: 1ex;">\n\t\t<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">\n\t\t\t<tr class="titlebg"><td align="left">' + sPickText + '</td></tr>\n\t\t\t<tr class="windowbg"><td align="left">');

	for (i = 0; i < sp_smileys.length; i++)
	{
		sp_smileys[i][2] = sp_smileys[i][2].replace(/"/g, '&quot;');
		sp_smileys[i][0] = sp_smileys[i][0].replace(/"/g, '&quot;');
		this.oSmileyPopupWindow.document.write('<a href="javascript:void(0);" onclick="window.opener.replaceText(\' ' + (portal_smf_version == 1.1 ? sp_smileys[i][0] : smf_addslashes(sp_smileys[i][0])) + '\', window.opener.document.getElementById(\'new_shout_' + postbox + '\')); window.focus(); return false;"><img src="' + smf_smileys_url + '/' + sp_smileys[i][1] + '" id="sml_' + sp_smileys[i][1] + '" alt="' + sp_smileys[i][2] + '" title="' + sp_smileys[i][2] + '" style="padding: 4px;" border="0" /></a> ');
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

/**
 * Updates the current version container with the current version found in the repository
 */
function sp_currentVersion()
{
	let oSPVersionContainer = document.getElementById("spCurrentVersion"),
		oinstalledVersionContainer = document.getElementById("spYourVersion"),
		sCurrentVersion = oinstalledVersionContainer.innerHTML,
		verCompare = new smf_ViewVersions();

	$.getJSON('https://api.github.com/repos/SimplePortal/SimplePortal/releases', {format: "json"},
		function (data, textStatus, jqXHR)
		{
			let mostRecent = {},
				previous = {},
				init_news = false;

			$.each(data, function (idx, elem)
			{
				// No drafts, thank you
				if (elem.draft)
				{
					return;
				}

				let release = sp_normalizeVersion(elem.tag_name),
					sCheckVersion = elem.tag_name.indexOf('v') === 0 ? elem.tag_name.substring(1) : elem.tag_name;

				sCheckVersion.replace('-', '').substring(1);
				if (!previous.hasOwnProperty('major') || verCompare.compareVersions(sCurrentVersion, sCheckVersion))
				{
					previous = release;
					mostRecent = elem;
				}

				// Load announcements for this release
				sp_setAnnouncement(init_news, elem);
				init_news = true;
			});

			let spVersion = mostRecent.tag_name.replace(/simpleportal/i, '').trim();

			oSPVersionContainer.innerHTML = spVersion;
			if (sCurrentVersion !== spVersion)
				oinstalledVersionContainer.innerHTML = '<span class="alert">' + sCurrentVersion + '</span>';
		}
	);
}

// Split a string representing a version number into an object
sp_normalizeVersion = function (sVersion)
{
	let splitVersion = sVersion.split(/[\s-]/),
		normalVersion = {
			major: 0,
			minor: 0,
			micro: 0,
		};

	for (let i = 0; i < splitVersion.length; i++)
	{
		if (splitVersion[i].toLowerCase() === 'simpleportal')
			continue;

		// Likely from the tag
		if (splitVersion[i].substring(0, 1) === 'v')
			splitVersion[i] = splitVersion[i].substring(1);

		// Only numbers and dots means a number
		if (splitVersion[i].replace(/[\d\.]/g, '') === '')
		{
			let ver = splitVersion[i].split('.');

			normalVersion.major = parseInt(ver[0]);
			normalVersion.minor = parseInt(ver[1]);
			normalVersion.micro = ver.length > 2 ? parseInt(ver[2]) : 0;
		}
	}

	return normalVersion;
};

/**
 * Load in any announcements
 *
 * @param init_news
 * @param announcement
 */
function sp_setAnnouncement(init_news, announcement)
{
	var oElem = document.getElementById('spAnnouncements'),
		sMessages = init_news ? oElem.innerHTML : '',
		sAnnouncementTemplate = '<dl>%content%</dl>',
		sAnnouncementMessageTemplate = '<dt><a href="%href%">%subject%</a> :: %time%</dt><dd>%message%</dd>';

	var sMessage = sAnnouncementMessageTemplate.replace('%href%', announcement.html_url).replace('%subject%', announcement.name).replace('%time%', announcement.published_at.replace(/[TZ]/g, ' ')).replace('%message%', announcement.body).replace(/\n/g, '<br />').replace(/\r/g, '');

	oElem.innerHTML = sMessages + sAnnouncementTemplate.replace('%content%', sMessage);
}