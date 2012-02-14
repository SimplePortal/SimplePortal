<?php
// Version: 2.3.4; PortalAdminShoutbox

function template_shoutbox_list()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=manageportal;area=portalshoutbox;sa=list" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_shoutbox_remove_confirm'], '\');">
		<table cellspacing="1" cellpadding="4" class="sp_fullwidth sp_auto_align bordercolor">
			<tr class="catbg3">
				<td colspan="', count($context['columns']) + 1, '"><strong>', $txt[139], ':</strong> ', $context['page_index'], '</td>
			</tr>
			<tr class="titlebg">';

	foreach ($context['columns'] as $column)
	{
		if ($column['selected'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					<a href="', $column['href'], '">', $column['label'], '&nbsp;<img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
				</th>';
		elseif ($column['sortable'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['link'], '
				</th>';
		else
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['label'], '
				</th>';
	}

	echo '
				<th><input type="checkbox" class="check" onclick="invertAll(this, this.form);" /></th>
			</tr>';
	
	if (empty($context['shoutboxes']))
		echo '
			<tr>
				<td colspan="', count($context['columns']) + 1, '" class="sp_center windowbg">', $txt['sp_error_no_shoutbox'], '</td>
			</tr>';

	foreach ($context['shoutboxes'] as $shoutbox)
	{
		echo '
			<tr>
				<td class="sp_left windowbg">', $shoutbox['name'], '</td>
				<td class="sp_center windowbg">', $shoutbox['shouts'], '</td>
				<td class="sp_center windowbg">', $shoutbox['caching'] ? $txt['sp_yes'] : $txt['sp_no'], '</td>
				<td class="sp_center windowbg">', $shoutbox['status_image'], '</td>
				<td class="sp_center windowbg">', implode('&nbsp;', $shoutbox['actions']), '</td>
				<td class="sp_center windowbg2"><input type="checkbox" name="remove[]" value="', $shoutbox['id'], '" class="check" /></td>
			</tr>';
	}

	echo '
			<tr class="catbg3">
				<td colspan="', count($context['columns']) + 1, '" class="sp_left">
					<div class="sp_float_right">
						<input type="submit" name="remove_shoutbox" value="', $txt['sp_admin_shoutbox_remove'], '" />
					</div>
					<strong>', $txt[139], ':</strong> ', $context['page_index'], '
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_shoutbox_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $helptxt, $modSettings;

	echo '
<form action="', $scripturl, '?action=manageportal;area=portalshoutbox;sa=edit" method="post" accept-charset="', $context['character_set'], '">
	<div class="tborder" style="width: 85%; margin: 0 auto;">
		<table class="sp_table" cellpadding="4">
			<tr>
				<td colspan="3" class="sp_regular_padding catbg">', $context['page_title'], '</td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_name'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="name" value="', $context['SPortal']['shoutbox']['name'], '" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp_permissions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_permissions'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<select name="permission_set" id="shoutbox_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
						<option value="', $id, '"', $id == $context['SPortal']['shoutbox']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_shoutbox_permissions_set_' . $label], '</option>';

	echo '
					</select>
				</td>
			</tr>
			<tr>
				<td class="windowbg2" id="shoutbox_custom_permissions_help" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2" id="shoutbox_custom_permissions_label">', $txt['sp_admin_shoutbox_col_custom_permissions'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2" id="shoutbox_custom_permissions_input">
					<table>
						<tr>
							<th>', $txt['sp_admin_shoutbox_custom_permissions_membergroup'], '</td>
							<th title="', $txt['sp_admin_shoutbox_custom_permissions_allowed'], '">', $txt['sp_admin_shoutbox_custom_permissions_allowed_short'], '</th>
							<th title="', $txt['sp_admin_shoutbox_custom_permissions_disallowed'], '">', $txt['sp_admin_shoutbox_custom_permissions_disallowed_short'], '</th>
							<th title="', $txt['sp_admin_shoutbox_custom_permissions_denied'], '">', $txt['sp_admin_shoutbox_custom_permissions_denied_short'], '</th>
						</tr>';

	foreach ($context['SPortal']['shoutbox']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['SPortal']['shoutbox']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['SPortal']['shoutbox']['groups_denied']))
			$current = -1;

		echo '
						<tr>
							<td>', $label, '</td>
							<td><input type="radio" name="membergroups[', $id, ']" value="1"', $current == 1 ? ' checked="checked"' : '', ' class="input_radio"></td>
							<td><input type="radio" name="membergroups[', $id, ']" value="0"', $current == 0 ? ' checked="checked"' : '', ' class="input_radio"></td>
							<td><input type="radio" name="membergroups[', $id, ']" value="-1"', $current == -1 ? ' checked="checked"' : '', ' class="input_radio"></td>
						</tr>';
	}

	echo '
					</table>
				</td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_moderators'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<fieldset id="moderators">
						<legend><a href="javascript:void(0);" onclick="document.getElementById(\'moderators\').style.display = \'none\';document.getElementById(\'moderators_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>
						<table width="100%" border="0">';

	foreach ($context['moderator_groups'] as $group)
	{
		echo '
							<tr>
								<td align="center"><input type="checkbox" name="moderator_groups[]" value="', $group['id'], '" id="moderator_groups_', $group['id'], '"', !empty($group['checked']) ? ' checked="checked"' : '', ' class="check" /></td>
								<td><label for="moderator_groups_', $group['id'], '"', $group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['mboards_groups_post_group'] . '"' : '', '>', $group['name'], '</label></td>
							</tr>';
	}

	echo '
							<tr>
								<td colspan="2">
									<label for="moderator_groups_all"><i>', $txt[737], '</i></label> <input type="checkbox" id="moderator_groups_all" onclick="invertAll(this, this.form, \'moderator_groups[]\');" />
								</td>
							</tr>
						</table>
					</fieldset>
					<a href="javascript:void(0);" onclick="document.getElementById(\'moderators\').style.display = \'block\'; document.getElementById(\'moderators_link\').style.display = \'none\'; return false;" id="moderators_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						document.getElementById("moderators").style.display = "none";
						document.getElementById("moderators_link").style.display = "";
					// ]]></script>
				</td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp-shoutboxesWarning" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_warning'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="warning" value="', $context['SPortal']['shoutbox']['warning'], '" size="25" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp-shoutboxesBBC" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_bbc'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2">
					<select name="allowed_bbc[]" size="7" multiple="multiple">';

	foreach ($context['allowed_bbc'] as $tag => $label)
		if (!isset($context['disabled_tags'][$tag]))
			echo '
						<option value="', $tag, '"', in_array($tag, $context['SPortal']['shoutbox']['allowed_bbc']) ? ' selected="selected"' : '', '>[', $tag, '] - ', $label, '</option>';

	echo '
					</select>
				</td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_height'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="height" value="', $context['SPortal']['shoutbox']['height'], '" size="10" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_num_show'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="num_show" value="', $context['SPortal']['shoutbox']['num_show'], '" size="10" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_num_max'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="num_max" value="', $context['SPortal']['shoutbox']['num_max'], '" size="10" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_reverse'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="checkbox" name="reverse" value="1"', $context['SPortal']['shoutbox']['reverse'] ? ' checked="checked"' : '', ' class="check" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_caching'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="checkbox" name="caching" value="1"', $context['SPortal']['shoutbox']['caching'] ? ' checked="checked"' : '', ' class="check" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_refresh'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="text" name="refresh" value="', $context['SPortal']['shoutbox']['refresh'], '" size="10" /></td>
			</tr>
			<tr>
				<td class="windowbg2" valign="top" width="16">&nbsp;</td>
				<th class="sp_regular_padding sp_top sp_right windowbg2">', $txt['sp_admin_shoutbox_col_status'], ':</th>
				<td class="sp_regular_padding sp_top windowbg2"><input type="checkbox" name="status" value="1"', $context['SPortal']['shoutbox']['status'] ? ' checked="checked"' : '', ' class="check" /></td>
			</tr>
			<tr>
				<td colspan="3" class="sp_regular_padding sp_center windowbg2"><input type="submit" name="submit" value="', $context['page_title'], '" /></td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="shoutbox_id" value="', $context['SPortal']['shoutbox']['id'], '" />
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
</form>
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	sp_update_permissions();

	function sp_update_permissions()
	{
		var new_state = document.getElementById("shoutbox_permission_set").value;
		document.getElementById("shoutbox_custom_permissions_help").style.display = new_state != 0 ? "none" : "";
		document.getElementById("shoutbox_custom_permissions_label").style.display = new_state != 0 ? "none" : "";
		document.getElementById("shoutbox_custom_permissions_input").style.display = new_state != 0 ? "none" : "";
	}
// ]]></script>';
}

function template_shoutbox_prune()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_prune_shoutbox">
		<form action="', $scripturl, '?action=manageportal;area=portalshoutbox;sa=prune" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" cellspacing="1" cellpadding="4" class="tborder" width="100%">
				<tr class="catbg">
					<td>', $context['page_title'], '</td>
				</tr>
				<tr class="windowbg2">
					<td>
						<dl class="sp_form">
							<dt>
								<input type="radio" name="type" id="type_all" value="all" class="input_radio" /> <label for="type_all">', $txt['sp_admin_shoutbox_opt_all'], '</label>
							</dt>
							<dd>
							</dd>
							<dt>
								<input type="radio" name="type" id="type_days" value="days" class="input_radio" /> <label for="type_days">', $txt['sp_admin_shoutbox_opt_days'], '</label>
							</dt>
							<dd>
								<input type="text" name="days" value="" size="5" onfocus="document.getElementById(\'type_days\').checked = true;" class="input_text" />
							</dd>
							<dt>
								<input type="radio" name="type" id="type_member" value="member" class="input_radio" /> <label for="type_member">', $txt['sp_admin_shoutbox_opt_member'], '</label>
							</dt>
							<dd>
								<input type="text" name="member" id="member" value="" onfocus="document.getElementById(\'type_member\').checked = true;" size="15" class="input_text" /> <a href="', $scripturl, '?action=findmember;input=member;quote=0;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" align="top" /></a>
							</dd>
						</dl>
						<div class="sp_button_container">
							<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
						</div>
					</td>
				</tr>
			</table>
			<input type="hidden" name="shoutbox_id" value="', $context['shoutbox']['id'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_shoutbox_block_redirect()
{
	global $context;

	echo '
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="40%">
		<tr class="catbg">
			<td>', $context['page_title'], '</td>
		</tr>
		<tr class="windowbg2">
			<td align="center">
				', $context['redirect_message'], '
			</td>
		</tr>
	</table>';
}

?>