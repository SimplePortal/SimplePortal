<?php
// Version: 2.3.6; PortalAdmin

function template_general_settings()
{
	global $context, $txt, $settings, $scripturl, $modSettings;

	echo '
	<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '">
		<table width="80%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
			<tr><td>
				<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<tr class="titlebg">
						<td colspan="3">', $context['settings_title'], '</td>
					</tr>';

	if (!empty($context['settings_message']))
		echo '
					<tr>
						<td class="windowbg2" colspan="3">', $context['settings_message'], '</td>
					</tr>';

	foreach ($context['config_vars'] as $config_var)
	{
		echo '
					<tr class="windowbg2">';

		if (is_array($config_var))
		{
			if ($config_var['help'])
				echo '
						<td class="windowbg2" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=', $config_var['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>';
			else
				echo '
						<td class="windowbg2"></td>';

			echo '
						<td valign="top" ', ($config_var['disabled'] ? ' style="color: #777777;"' : ''), '><label for="', $config_var['name'], '">', $config_var['label'], ($config_var['type'] == 'password' ? '<br /><i>' . $txt['admin_confirm_password'] . '</i>' : ''), '</label></td>
						<td class="windowbg2" width="50%">';

			if ($config_var['type'] == 'check')
				echo '
							<input type="hidden" name="', $config_var['name'], '" value="0" /><input type="checkbox"', ($config_var['disabled'] ? ' disabled="disabled"' : ''), ' name="', $config_var['name'], '" id="', $config_var['name'], '" ', ($config_var['value'] ? ' checked="checked"' : ''), ' class="check" />';
			elseif ($config_var['type'] == 'password')
				echo '
							<input type="password"', ($config_var['disabled'] ? ' disabled="disabled"' : ''), ' name="', $config_var['name'], '[0]"', ($config_var['size'] ? ' size="' . $config_var['size'] . '"' : ''), ' value="*#fakepass#*" onfocus="this.value = \'\'; this.form.', $config_var['name'], '.disabled = false;" /><br />
							<input type="password" disabled="disabled" id="', $config_var['name'], '" name="', $config_var['name'], '[1]"', ($config_var['size'] ? ' size="' . $config_var['size'] . '"' : ''), ' />';
			elseif ($config_var['type'] == 'select')
			{
				echo '
							<select name="', $config_var['name'], '"', ($config_var['disabled'] ? ' disabled="disabled"' : ''), '>';
				foreach ($config_var['data'] as $option)
					echo '
								<option value="', $option[0], '"', ($option[0] == $config_var['value'] ? ' selected="selected"' : ''), '>', $option[1], '</option>';
				echo '
							</select>';
			}
			elseif ($config_var['type'] == 'large_text')
			{
				echo '
							<textarea rows="', ($config_var['size'] ? $config_var['size'] : 4), '" cols="30" ', ($config_var['disabled'] ? ' disabled="disabled"' : ''), ' name="', $config_var['name'], '">', $config_var['value'], '</textarea>';
			}
			elseif (is_array($config_var['type']))
			{

				foreach($config_var['type'] as $name => $title)
				{
					echo '
							<input type="hidden" name="', $name, '" value="0" /><input type="checkbox" name="', $name, '" id="', $name, '" ', (!empty($modSettings[$name]) ? ' checked="checked"' : ''), ' class="check" />
							', $title, '<br />';
				}
			}
			else
				echo '
							<input type="text"', ($config_var['disabled'] ? ' disabled="disabled"' : ''), ' name="', $config_var['name'], '" value="', $config_var['value'], '"', ($config_var['size'] ? ' size="' . $config_var['size'] . '"' : ''), ' />';

			echo '
						</td>';
		}
		else
		{
			if ($config_var == '')
				echo '
							<td colspan="3" class="windowbg2"><hr size="1" width="100%" class="hrcolor" /></td>';
			else
				echo '
							<td colspan="3" class="windowbg2" align="center"><b>' . $config_var . '</b></td>';
		}
		echo '
					</tr>';
	}
	echo '
					<tr>
						<td class="windowbg2" colspan="3" align="center" valign="middle"><input type="submit" value="', $txt[10], '"', (!empty($context['save_disabled']) ? ' disabled="disabled"' : ''), ' /></td>
					</tr>
				</table>
			</td></tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_information()
{
	global $context, $txt;

	if ($context['in_admin'])
	{
		echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 0.5em;">
			<tr>
				<td valign="top">
					<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">
						<tr>
							<td class="titlebg">
								', $txt['sp-info_live'], '
							</td>
						</tr><tr>
							<td class="windowbg2" valign="top" style="height: 18ex; padding: 0;">
								<div id="spAnnouncements" style="height: 18ex; overflow: auto; padding-right: 1ex;"><div style="margin: 4px; font-size: 0.85em;">', $txt['sp-info_no_live'], '</div></div>
							</td>
						</tr>
					</table>
				</td>
				<td style="width: 1ex;">&nbsp;</td>
				<td valign="top" style="width: 40%;">
					<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" id="spVersionsTable">
						<tr>
							<td class="titlebg">', $txt['sp-info_general'], '</td>
						</tr><tr>
							<td class="windowbg2" valign="top" style="height: 18ex; line-height: 1.5em;">
								<b>', $txt['sp-info_versions'], ':</b><br />
								', $txt['sp-info_your_version'], ':
								<i id="spYourVersion" style="white-space: nowrap;">', $context['sp_version'], '</i><br />
								', $txt['sp-info_current_version'], ':
								<i id="spCurrentVersion" style="white-space: nowrap;">??</i><br />
								<b>', $txt['sp-info_managers'], ':</b>
								', implode(', ', $context['sp_managers']), '
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<script language="JavaScript" type="text/javascript" src="http://www.simpleportal.net/sp/current-version.js"></script>
		<script language="JavaScript" type="text/javascript" src="http://www.simpleportal.net/sp/latest-news.js"></script>
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			function spSetAnnouncements()
			{
				if (typeof(window.spAnnouncements) == "undefined" || typeof(window.spAnnouncements.length) == "undefined")
					return;

				var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

				for (var i = 0; i < window.spAnnouncements.length; i++)
				{
					str += "\n	<div style=\"padding-bottom: 2px;\"><a hre" + "f=\"" + window.spAnnouncements[i].href + "\">" + window.spAnnouncements[i].subject + "<" + "/a> ', $txt[30], ' " + window.spAnnouncements[i].time + "<" + "/div>";
					str += "\n	<div style=\"padding-left: 2ex; margin-bottom: 1.5ex; border-top: 1px dashed;\">"
					str += "\n		" + window.spAnnouncements[i].message;
					str += "\n	<" + "/div>";
				}

				setInnerHTML(document.getElementById("spAnnouncements"), str + "<" + "/div>");
			}

			function spCurrentVersion()
			{
				var spVer, yourVer;

				if (typeof(window.spVersion) != "string")
					return;

				spVer = document.getElementById("spCurrentVersion");
				yourVer = document.getElementById("spYourVersion");

				setInnerHTML(spVer, window.spVersion);

				var currentVersion = getInnerHTML(yourVer);
				if (currentVersion != window.spVersion)
					setInnerHTML(yourVer, "<span style=\"color: red;\">" + currentVersion + "<" + "/span>");
			}';

		echo '
			var oldonload;
			if (typeof(window.onload) != "undefined")
				oldonload = window.onload;

			window.onload = function ()
			{
				spSetAnnouncements();
				spCurrentVersion();

				if (oldonload)
					oldonload();
			}
		// ]]></script>';
	}

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 2ex;">
			<tr class="titlebg">
				<td>', $txt['sp-info_title'], '</td>
			</tr><tr>
				<td class="windowbg2" style="padding: 0 10px;"><div style="line-height: 1.5em;">';

	foreach ($context['sp_credits'] as $section)
	{
		if (isset($section['pretext']))
			echo '
					<p>', $section['pretext'], '</p>';

		foreach ($section['groups'] as $group)
		{
			if (empty($group['members']))
				continue;

			echo '
					<div style="margin-top: 1ex;">';

			if (isset($group['title']))
				echo '<strong>', $group['title'], ':</strong> ';

			echo implode(', ', $group['members']), '</div>';
		}

		if (isset($section['posttext']))
			echo '
					<p style="margin: 0; padding: 2ex 0 1ex 0;">', $section['posttext'], '</p>';
	}

	echo '
					<hr />
					<p style="margin: 0; padding: 1ex 0 1ex 0;">', sprintf($txt['sp-info_contribute'], 'http://www.simpleportal.net/index.php?page=contribute'), '</p>
				</div></td>
			</tr>
		</table>';
}

?>