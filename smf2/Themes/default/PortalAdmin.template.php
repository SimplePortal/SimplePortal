<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.7
 */

function template_general_settings()
{
	global $context, $modSettings, $txt, $settings, $scripturl;

	echo '
	<div id="admincenter">
		<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '"', !empty($context['force_form_onsubmit']) ? ' onsubmit="' . $context['force_form_onsubmit'] . '"' : '', '>';

	if (isset($context['settings_title']))
		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['settings_title'], '
				</h3>
			</div>';

	if (!empty($context['settings_message']))
		echo '
			<div class="information">', $context['settings_message'], '</div>';

	$is_open = false;
	foreach ($context['config_vars'] as $config_var)
	{
		if (is_array($config_var) && ($config_var['type'] == 'title' || $config_var['type'] == 'desc'))
		{
			if ($is_open)
			{
				$is_open = false;
				echo '
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>';
			}

			if ($config_var['type'] == 'title')
			{
				echo '
					<div class="cat_bar">
						<h3 class="', !empty($config_var['class']) ? $config_var['class'] : 'catbg', '"', !empty($config_var['force_div_id']) ? ' id="' . $config_var['force_div_id'] . '"' : '', '>
							', ($config_var['help'] ? '<a href="' . $scripturl . '?action=helpadmin;help=' . $config_var['help'] . '" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.gif" alt="' . $txt['help'] . '" /></a>' : ''), '
							', $config_var['label'], '
						</h3>
					</div>';
			}
			else
			{
				echo '
					<p class="description">
						', $config_var['label'], '
					</p>';
			}

			continue;
		}

		if (!$is_open)
		{
			$is_open = true;
			echo '
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">';
		}

		if (is_array($config_var))
		{
			if (in_array($config_var['type'], array('message', 'warning')))
			{
				echo '
							<dd', $config_var['type'] == 'warning' ? ' class="alert"' : '', (!empty($config_var['force_div_id']) ? ' id="' . $config_var['force_div_id'] . '_dd"' : ''), '>
								', $config_var['label'], '
							</dd>';
			}
			else
			{
				echo '
							<dt', is_array($config_var) && !empty($config_var['force_div_id']) ? ' id="' . $config_var['force_div_id'] . '"' : '', '>';

				$javascript = $config_var['javascript'];
				$disabled = !empty($config_var['disabled']) ? ' disabled="disabled"' : '';
				$subtext = !empty($config_var['subtext']) ? '<br /><span class="smalltext"> ' . $config_var['subtext'] . '</span>' : '';

				if ($config_var['help'])
					echo '
								<a id="setting_', $config_var['name'], '" href="', $scripturl, '?action=helpadmin;help=', $config_var['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" /></a><span', ($config_var['disabled'] ? ' style="color: #777777;"' : ($config_var['invalid'] ? ' class="error"' : '')), '><label for="', $config_var['name'], '">', $config_var['label'], '</label>', $subtext, ($config_var['type'] == 'password' ? '<br /><em>' . $txt['admin_confirm_password'] . '</em>' : ''), '</span>
							</dt>';
				else
					echo '
								<a id="setting_', $config_var['name'], '"></a> <span', ($config_var['disabled'] ? ' style="color: #777777;"' : ($config_var['invalid'] ? ' class="error"' : '')), '><label for="', $config_var['name'], '">', $config_var['label'], '</label>', $subtext, ($config_var['type'] == 'password' ? '<br /><em>' . $txt['admin_confirm_password'] . '</em>' : ''), '</span>
							</dt>';

				echo '
							<dd', (!empty($config_var['force_div_id']) ? ' id="' . $config_var['force_div_id'] . '_dd"' : ''), '>',
								$config_var['preinput'];

				if ($config_var['type'] == 'check')
					echo '
								<input type="checkbox"', $javascript, $disabled, ' name="', $config_var['name'], '" id="', $config_var['name'], '"', ($config_var['value'] ? ' checked="checked"' : ''), ' value="1" class="input_check" />';
				elseif ($config_var['type'] == 'select')
				{
					echo '
								<select name="', $config_var['name'], '" id="', $config_var['name'], '" ', $javascript, $disabled, (!empty($config_var['multiple']) ? ' multiple="multiple"' : ''), '>';
					foreach ($config_var['data'] as $option)
						echo '
									<option value="', $option[0], '"', (($option[0] == $config_var['value'] || (!empty($config_var['multiple']) && in_array($option[0], $config_var['value']))) ? ' selected="selected"' : ''), '>', $option[1], '</option>';
					echo '
								</select>';
				}
				elseif ($config_var['type'] == 'large_text')
				{
					echo '
								<textarea rows="', ($config_var['size'] ? $config_var['size'] : 4), '" cols="30" ', $javascript, $disabled, ' name="', $config_var['name'], '" id="', $config_var['name'], '">', $config_var['value'], '</textarea>';
				}
				elseif ($config_var['type'] == 'var_message')
					echo $config_var['var_message'];
				elseif ($config_var['type'] == 'multicheck')
				{
					foreach($config_var['subsettings'] as $name => $title)
					{
						echo '
								<input type="hidden" name="', $name, '" value="0" /><input type="checkbox" name="', $name, '" id="', $name, '" ', (!empty($modSettings[$name]) ? ' checked="checked"' : ''), ' class="input_check" />
								', $title, '<br />';
					}
				}
				else
					echo '
								<input type="text"', $javascript, $disabled, ' name="', $config_var['name'], '" id="', $config_var['name'], '" value="', $config_var['value'], '"', ($config_var['size'] ? ' size="' . $config_var['size'] . '"' : ''), ' class="input_text" />';

				echo '
								', $config_var['postinput'], '
							</dd>';
			}
		}

		else
		{
			if ($config_var == '')
				echo '
						</dl>
						<hr class="hrcolor" />
						<dl class="settings">';
			else
				echo '
						<strong>' . $config_var . '</strong>';
		}
	}

	if ($is_open)
		echo '
						</dl>';

	if (empty($context['settings_save_dont_show']))
		echo '
						<p>
							<input type="submit" value="', $txt['save'], '"', (!empty($context['save_disabled']) ? ' disabled="disabled"' : ''), (!empty($context['settings_save_onclick']) ? ' onclick="' . $context['settings_save_onclick'] . '"' : ''), ' class="button_submit" />
						</p>';

	if ($is_open)
		echo '
					</div>
				<span class="botslice"><span></span></span>
			</div>';

	echo '
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<br class="clear" />';
}

function template_information()
{
	global $context, $txt;

	if ($context['in_admin'])
	{
		echo '
	<div id="sp_admin_main">
		<div id="sp_live_info" class="sp_float_left">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['sp-info_live'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<div id="spAnnouncements" style="">', $txt['sp-info_no_live'], '</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>
		<div id="sp_general_info" class="sp_float_right">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['sp-info_general'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<strong>', $txt['sp-info_versions'], ':</strong><br />
					', $txt['sp-info_your_version'], ':
					<em id="spYourVersion" style="white-space: nowrap;">', $context['sp_version'], '</em><br />
					', $txt['sp-info_current_version'], ':
					<em id="spCurrentVersion" style="white-space: nowrap;">??</em><br />
					<strong>', $txt['sp-info_managers'], ':</strong>
					', implode(', ', $context['sp_managers']), '
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>
	</div>
	<script type="text/javascript" src="https://simpleportal.net/sp/current-version.js"></script>
	<script type="text/javascript" src="https://simpleportal.net/sp/latest-news.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		function spSetAnnouncements()
		{
			if (typeof(window.spAnnouncements) == "undefined" || typeof(window.spAnnouncements.length) == "undefined")
				return;

			var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

			for (var i = 0; i < window.spAnnouncements.length; i++)
			{
				str += "\n	<div style=\"padding-bottom: 2px;\"><a hre" + "f=\"" + window.spAnnouncements[i].href + "\">" + window.spAnnouncements[i].subject + "<" + "/a> ', $txt['on'], ' " + window.spAnnouncements[i].time + "<" + "/div>";
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
				setInnerHTML(yourVer, "<span class=\"alert\">" + currentVersion + "<" + "/span>");
		}';

		echo '
		var func = function ()
		{
			spSetAnnouncements();
			spCurrentVersion();
		}
		', $context['SPortal']['core_compat'] == 'old' ? 'add_load_event(func);' : 'addLoadEvent(func);','
	// ]]></script>';
	}

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['sp-info_title'], '
		</h3>
	</div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
			<div class="sp_content_padding" id="sp_credits">';

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
				<p>';

			if (isset($group['title']))
				echo '
					<strong>', $group['title'], ':</strong> ';

			echo implode(', ', $group['members']), '
				</p>';
		}


		if (isset($section['posttext']))
			echo '
				<p>', $section['posttext'], '</p>';
	}

	echo '
				<hr />
				<p>', sprintf($txt['sp-info_contribute'], 'https://simpleportal.net/index.php?page=contribute'), '</p>
			</div>
		<span class="botslice"><span></span></span>
	</div>';
}

?>