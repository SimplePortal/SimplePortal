<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause 
 *
 * @version 2.4
 */

function template_permission_profiles_list()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_manage_profiles">
		<form action="', $scripturl, '?action=admin;area=portalprofiles;sa=listpermission" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_profiles_remove_confirm'], '\');">
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
	
	if (empty($context['profiles']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">', $txt['error_sp_no_profiles'], '</td>
					</tr>';
	}

	foreach ($context['profiles'] as $profile)
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $profile['label'], '</td>
						<td class="sp_center">', empty($profile['articles']) ? 0 : $profile['articles'], '</td>
						<td class="sp_center">', empty($profile['blocks']) ? 0 : $profile['blocks'], '</td>
						<td class="sp_center">', empty($profile['categories']) ? 0 : $profile['categories'], '</td>
						<td class="sp_center">', empty($profile['pages']) ? 0 : $profile['pages'], '</td>
						<td class="sp_center">', empty($profile['shoutboxes']) ? 0 : $profile['shoutboxes'], '</td>
						<td class="sp_center">', implode('&nbsp;', $profile['actions']), '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $profile['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_align_left pagesection">
				<div class="sp_float_right">
					<input type="submit" name="remove_profiles" value="', $txt['sp_admin_profiles_remove'], '" class="button_submit" />
				</div>
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_permission_profiles_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_profile">
		<form action="', $scripturl, '?action=admin;area=portalprofiles;sa=editpermission" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
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
							<label for="profile_name">', $txt['sp_admin_profiles_col_name'], ':</label>
						</dt>
						<dd>
							<input type="text" name="name" id="profile_name" value="', $context['profile']['name'], '" class="input_text" />
						</dd>
						<dt>
							', $txt['sp_admin_profiles_col_permissions'], ':
						</dt>
						<dd>
							<table>
								<tr>
									<th>', $txt['sp_admin_profiles_permissions_membergroup'], '</th>
									<th title="', $txt['sp_admin_profiles_permissions_allowed'], '">', $txt['sp_admin_profiles_permissions_allowed_short'], '</th>
									<th title="', $txt['sp_admin_profiles_permissions_disallowed'], '">', $txt['sp_admin_profiles_permissions_disallowed_short'], '</th>
									<th title="', $txt['sp_admin_profiles_permissions_denied'], '">', $txt['sp_admin_profiles_permissions_denied_short'], '</th>
								</tr>';

	foreach ($context['profile']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['profile']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['profile']['groups_denied']))
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
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="profile_id" value="', $context['profile']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_style_profiles_list()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_manage_profiles">
		<form action="', $scripturl, '?action=admin;area=portalprofiles;sa=liststyle" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp_profiles_remove_confirm'], '\');">
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
	
	if (empty($context['profiles']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">', $txt['error_sp_no_profiles'], '</td>
					</tr>';
	}

	foreach ($context['profiles'] as $profile)
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $profile['label'], '</td>
						<td class="sp_center">', empty($profile['articles']) ? 0 : $profile['articles'], '</td>
						<td class="sp_center">', empty($profile['blocks']) ? 0 : $profile['blocks'], '</td>
						<td class="sp_center">', empty($profile['pages']) ? 0 : $profile['pages'], '</td>
						<td class="sp_center">', implode('&nbsp;', $profile['actions']), '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $profile['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_align_left pagesection">
				<div class="sp_float_right">
					<input type="submit" name="remove_profiles" value="', $txt['sp_admin_profiles_remove'], '" class="button_submit" />
				</div>
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_style_profiles_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_profile">
		<form action="', $scripturl, '?action=admin;area=portalprofiles;sa=editstyle" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
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
							<label for="profile_name">', $txt['sp_admin_profiles_col_name'], ':</label>
						</dt>
						<dd>
							<input type="text" name="name" id="profile_name" value="', $context['profile']['name'], '" class="input_text" />
						</dd>
						<dt>
							<label for="title_default_class">', $txt['sp_admin_profiles_styles_title_default_class'], ':</label>
						</dt>
						<dd>
							<select name="title_default_class" id="title_default_class">';

	foreach ($context['profile']['classes']['title'] as $class)
		echo '
								<option value="', $class, '"', $context['profile']['title_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

	echo '
							</select>
						</dd>
						<dt>
							<label for="title_custom_class">', $txt['sp_admin_profiles_styles_title_custom_class'], ':</label>
						</dt>
						<dd>
							<input type="text" name="title_custom_class" id="title_custom_class" value="', $context['profile']['title_custom_class'], '" class="input_text" size="40" />
						</dd>
						<dt>
							<label for="title_custom_style">', $txt['sp_admin_profiles_styles_title_custom_style'], ':</label>
						</dt>
						<dd>
							<input type="text" name="title_custom_style" id="title_custom_style" value="', $context['profile']['title_custom_style'], '" class="input_text" size="40" />
						</dd>
						<dt>
							<label for="no_title">', $txt['sp_admin_profiles_styles_no_title'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="no_title" id="no_title" value="1" onclick="document.getElementById(\'title_default_class\').disabled = document.getElementById(\'title_custom_class\').disabled = document.getElementById(\'title_custom_style\').disabled = this.checked"', $context['profile']['no_title'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
						<dt>
							<label for="body_default_class">', $txt['sp_admin_profiles_styles_body_default_class'], ':</label>
						</dt>
						<dd>
							<select name="body_default_class" id="body_default_class">';

	foreach ($context['profile']['classes']['body'] as $class)
		echo '
								<option value="', $class, '"', $context['profile']['body_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

	echo '
							</select>
						</dd>
						<dt>
							<label for="body_custom_class">', $txt['sp_admin_profiles_styles_body_custom_class'], ':</label>
						</dt>
						<dd>
							<input type="text" name="body_custom_class" id="body_custom_class" value="', $context['profile']['body_custom_class'], '" class="input_text" size="40" />
						</dd>
						<dt>
							<label for="body_custom_style">', $txt['sp_admin_profiles_styles_body_custom_style'], ':</label>
						</dt>
						<dd>
							<input type="text" name="body_custom_style" id="body_custom_style" value="', $context['profile']['body_custom_style'], '" class="input_text" size="40" />
						</dd>
						<dt>
							<label for="no_body">', $txt['sp_admin_profiles_styles_no_body'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="no_body" id="no_body" value="1" onclick="document.getElementById(\'body_default_class\').disabled = this.checked"', $context['profile']['no_body'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="profile_id" value="', $context['profile']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		document.getElementById("title_default_class").disabled = document.getElementById("no_title").checked;
		document.getElementById("title_custom_class").disabled = document.getElementById("no_title").checked;
		document.getElementById("title_custom_style").disabled = document.getElementById("no_title").checked;
		document.getElementById("body_default_class").disabled = document.getElementById("no_body").checked;
	// ]]></script>';
}