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

function template_shoutbox_list()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_manage_shoutboxes">
		<form action="', $scripturl, '?action=admin;area=portalshoutbox;sa=list" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_shoutbox_remove_confirm'], '\');">
			<div class="sp_align_left pagesection">
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">';

	foreach ($context['columns'] as $column)
	{
		if ($column['selected'])
			echo '
						<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
							<a href="', $column['href'], '">', $column['label'], '&nbsp;<img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
						</th>';
		elseif ($column['sortable'])
			echo '
						<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
							', $column['link'], '
						</th>';
		else
			echo '
						<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
							', $column['label'], '
						</th>';
	}

	echo '
						<th scope="col" class="last_th">
							<input type="checkbox" class="input_check" onclick="invertAll(this, this.form);" />
						</th>
					</tr>
				</thead>
				<tbody>';
	
	if (empty($context['shoutboxes']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">', $txt['sp_error_no_shoutbox'], '</td>
					</tr>';
	}

	foreach ($context['shoutboxes'] as $shoutbox)
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $shoutbox['name'], '</td>
						<td class="sp_center">', $shoutbox['shouts'], '</td>
						<td class="sp_center">', $shoutbox['caching'] ? $txt['sp_yes'] : $txt['sp_no'], '</td>
						<td class="sp_center">', $shoutbox['status_image'], '</td>
						<td class="sp_center">', implode('&nbsp;', $shoutbox['actions']), '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $shoutbox['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_align_left pagesection">
				<div class="sp_float_right">
					<input type="submit" name="remove_shoutbox" value="', $txt['sp_admin_shoutbox_remove'], '" class="button_submit" />
				</div>
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_shoutbox_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $helptxt, $modSettings;

	echo '
	<div id="sp_edit_shoutbox">
		<form action="', $scripturl, '?action=admin;area=portalshoutbox;sa=edit" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<label for="shoutbox_name">', $txt['sp_admin_shoutbox_col_name'], ':</label>
						</dt>
						<dd>
							<input type="text" name="name" id="shoutbox_name" value="', $context['SPortal']['shoutbox']['name'], '" class="input_text" />
						</dd>
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp_permissions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="shoutbox_permission_set">', $txt['sp_admin_shoutbox_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permission_set" id="shoutbox_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
								<option value="', $id, '"', $id == $context['SPortal']['shoutbox']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_shoutbox_permissions_set_' . $label], '</option>';

	echo '
							</select>
						</dd>
						<dt id="shoutbox_custom_permissions_label">
							', $txt['sp_admin_shoutbox_col_custom_permissions'], ':
						</dt>
						<dd id="shoutbox_custom_permissions_input">
							<table>
								<tr>
									<th>', $txt['sp_admin_shoutbox_custom_permissions_membergroup'], '</th>
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
									<td><input type="radio" name="membergroups[', $id, ']" value="1"', $current == 1 ? ' checked="checked"' : '', ' class="input_radio" /></td>
									<td><input type="radio" name="membergroups[', $id, ']" value="0"', $current == 0 ? ' checked="checked"' : '', ' class="input_radio" /></td>
									<td><input type="radio" name="membergroups[', $id, ']" value="-1"', $current == -1 ? ' checked="checked"' : '', ' class="input_radio" /></td>
								</tr>';
	}

	echo '
							</table>
						</dd>
						<dt>
							', $txt['sp_admin_shoutbox_col_moderators'], ':
						</dt>
						<dd>
							<fieldset id="moderators">
								<legend><a href="javascript:void(0);" onclick="document.getElementById(\'moderators\').style.display = \'none\';document.getElementById(\'moderators_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>
								<ul class="permission_groups">';

	foreach ($context['moderator_groups'] as $group)
	{
		echo '
									<li><input type="checkbox" name="moderator_groups[', $group['id'], ']" id="moderator_groups_', $group['id'], '" value="', $group['id'], '"', !empty($group['checked']) ? ' checked="checked"' : '', ' class="input_check" /> <label for="moderator_groups_', $group['id'], '"', $group['is_post_group'] ? ' style="font-style: italic;"' : '', '>', $group['name'], '</label></li>';
	}
	echo '
									<li><input type="checkbox" id="moderator_groups_all" onclick="invertAll(this, this.form, \'moderator_groups\');" class="input_check" /> <label for="moderator_groups_all"><em>', $txt['check_all'], '</em></label></li>
								</ul>
							</fieldset>
							<a href="javascript:void(0);" onclick="document.getElementById(\'moderators\').style.display = \'block\'; document.getElementById(\'moderators_groups_link\').style.display = \'none\'; return false;" id="moderators_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>
							<script type="text/javascript"><!-- // --><![CDATA[
								document.getElementById("moderators").style.display = "none";
								document.getElementById("moderators_groups_link").style.display = "";
							// ]]></script>
						</dd>
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp-shoutboxesWarning" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="shoutbox_warning">', $txt['sp_admin_shoutbox_col_warning'], ':</label>
						</dt>
						<dd>
							<input type="text" name="warning" id="shoutbox_warning" value="', $context['SPortal']['shoutbox']['warning'], '" size="25" class="input_text" />
						</dd>
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp-shoutboxesBBC" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="shoutbox_bbc">', $txt['sp_admin_shoutbox_col_bbc'], ':</label>
						</dt>
						<dd>
							<select name="allowed_bbc[]" id="shoutbox_bbc" size="7" multiple="multiple">';

	foreach ($context['allowed_bbc'] as $tag => $label)
		if (!isset($context['disabled_tags'][$tag]))
			echo '
								<option value="', $tag, '"', in_array($tag, $context['SPortal']['shoutbox']['allowed_bbc']) ? ' selected="selected"' : '', '>[', $tag, '] - ', $label, '</option>';

	echo '
							</select>
						</dd>
						<dt>
							<label for="shoutbox_height">', $txt['sp_admin_shoutbox_col_height'], '</label>
						</dt>
						<dd>
							<input type="text" name="height" id="shoutbox_height" value="', $context['SPortal']['shoutbox']['height'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_num_show">', $txt['sp_admin_shoutbox_col_num_show'], ':</label>
						</dt>
						<dd>
							<input type="text" name="num_show" id="shoutbox_num_show" value="', $context['SPortal']['shoutbox']['num_show'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_num_max">', $txt['sp_admin_shoutbox_col_num_max'], ':</label>
						</dt>
						<dd>
							<input type="text" name="num_max" id="shoutbox_num_max" value="', $context['SPortal']['shoutbox']['num_max'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_reverse">', $txt['sp_admin_shoutbox_col_reverse'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="reverse" id="shoutbox_reverse" value="1"', $context['SPortal']['shoutbox']['reverse'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
						<dt>
							<label for="shoutbox_caching">', $txt['sp_admin_shoutbox_col_caching'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="caching" id="shoutbox_caching" value="1"', $context['SPortal']['shoutbox']['caching'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
						<dt>
							<label for="shoutbox_refresh">', $txt['sp_admin_shoutbox_col_refresh'], '</label>
						</dt>
						<dd>
							<input type="text" name="refresh" id="shoutbox_refresh" value="', $context['SPortal']['shoutbox']['refresh'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_status">', $txt['sp_admin_shoutbox_col_status'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="status" id="shoutbox_status" value="1"', $context['SPortal']['shoutbox']['status'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="shoutbox_id" value="', $context['SPortal']['shoutbox']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script type="text/javascript"><!-- // --><![CDATA[
		sp_update_permissions();

		function sp_update_permissions()
		{
			var new_state = document.getElementById("shoutbox_permission_set").value;
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
		<form action="', $scripturl, '?action=admin;area=portalshoutbox;sa=prune" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
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
							<input type="text" name="member" id="member" value="" onclick="document.getElementById(\'type_member\').checked = true;" size="15" class="input_text" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="shoutbox_id" value="', $context['shoutbox']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/suggest.js?fin20"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var oAddBuddySuggest = new smc_AutoSuggest({
			sSelf: \'oAddBuddySuggest\',
			sSessionId: \'', $context['session_id'], '\',
			sSessionVar: \'', $context['session_var'], '\',
			sSuggestId: \'member\',
			sControlId: \'member\',
			sSearchType: \'member\',
			sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
			bItemList: false
		});
	// ]]></script>';
}

function template_shoutbox_block_redirect()
{
	global $context;

	echo '
	<div id="sp_shoutbox_redirect">
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['page_title'], '
			</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="sp_content_padding">
				', $context['redirect_message'], '
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>';
}

?>