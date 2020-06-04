<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2020 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.3.8
 */

function template_block_list()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">';

	if (!empty($context['block_move']))
		echo '
			<tr class="windowbg2" height="30">
				<td colspan="4">', $context['move_title'], ' [<a href="', $scripturl, '?action=manageportal;area=portalblocks">', $txt['sp-blocks_cancel_moving'], '</a>]', '</td>
			</tr>';

	foreach($context['sides'] as $side) {

		echo '
				<tr class="catbg3">
					<td colspan="4" align="left">
						<a class="sp_float_right" href="', $scripturl, '?action=manageportal;area=portalblocks;sa=add;col=', $side['id'], '">', sp_embed_image('add', sprintf($txt['sp-blocksCreate'], $side['label'])), '</a>
						<a href="', $scripturl, '?action=helpadmin;help=', $side['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>
						<strong>', $side['label'], '</strong>
					</td>
				</tr>';

		echo '
				<tr class="titlebg">';

		if ($context['block_move'])
			echo '
				<th width="5%">', $txt['sp-adminColumnMove'], '</th>';

		foreach ($context['columns'] as $column)
		{
				echo '
					<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
						', $column['label'], '
					</th>';
		}
		echo '
				</tr>';

		if (empty($context['blocks'][$side['name']]))
		{
			echo '

					<td class="sp_center windowbg2" colspan="4">', $txt['error_sp_no_block'], '</td>
				</tr>';
		}

		foreach($context['blocks'][$side['name']] as $block)
		{
			echo '
				<tr>';

			if ($context['block_move'])
				echo '
					<td align="center" valign="top" class="windowbg2">', $block['id'] != $context['block_move'] ? $block['move_insert'] : '', '</td>';

			echo '
					<td align="left" valign="top" class="windowbg">', $block['id'] == $context['block_move'] ? '<strong>' . $block['label'] . '</strong>' : $block['label'], '</td>
					<td align="left" valign="top" class="windowbg2">', $block['type_text'], '</td>
					<td align="center" valign="top" class="windowbg2">', implode(' ', $block['actions']), '</td>
				</tr>';
		}

		if ($context['block_move'] && (empty($side['last']) || $context['block_move'] != $side['last']))
		{
			echo '
			<tr>
				<td align="center" valign="top" class="windowbg2"><a href="', $scripturl, '?action=manageportal;area=portalblocks;sa=move;block_id=', $context['block_move'], ';col=', $side['id'], ';sesc=', $context['session_id'], '">', sp_embed_image('arrow', $txt['sp-blocks_move_here']), '</a></td>
				<td align="left" valign="top" class="windowbg"></td>
				<td align="left" valign="top" class="windowbg2"></td>
				<td align="center" valign="top" class="windowbg2"></td>
			</tr>';
		}
	}

	echo '
			<tr class="catbg3">
				<td colspan="4" align="left">
				</td>
			</tr>
		</table>';
}

function template_block_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $helptxt, $modSettings;

	if (!empty($context['SPortal']['preview']))
	{
		echo '
<div style="width: ', $context['widths'][$context['SPortal']['block']['column']], '; margin: 0 auto;">';

		template_block($context['SPortal']['block']);

		echo '
</div>';
	}

	echo '<br />
<form name="sp_block" action="', $scripturl, '?action=manageportal;area=portalblocks;sa=edit" method="post" accept-charset="', $context['character_set'], '">
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="65%">
		<tr class="catbg">
			<td>
				<a href="', $scripturl, '?action=helpadmin;help=sp-blocks', $context['SPortal']['is_new'] ? 'Edit' : 'Add', '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
				', $context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '
			</td>
		</tr>
		<tr class="windowbg2">
			<td align="center">
					<table border="0" cellspacing="0" cellpadding="4" width="100%">';

	if (empty($modSettings['showleft']) || empty($modSettings['showright']))
	{
		echo '
						<tr class="windowbg3">
							<td colspan="3" style="font-weight: bold; text-align: center;">';

		if (empty($modSettings['showleft']) && empty($modSettings['showright']))
			echo $txt['sp-blocksDisabledBoth'];
		elseif (empty($modSettings['showleft']))
			echo $txt['sp-blocksDisabledLeft'];
		else
			echo $txt['sp-blocksDisabledRight'];

		echo '
							</td>
						</tr>';
	}

	echo '
						<tr>
							<td class="windowbg2" valign="top" width="16">&nbsp;</td>
							<th style="text-align:right;" valign="top">', $txt['sp-adminColumnType'], ':</th>
							<td class="windowbg2">', $context['SPortal']['block']['type_text'], '</td>
						</tr>
						<tr>
							<td class="windowbg2" valign="top" width="16">&nbsp;</td>
							<th style="text-align:right;" valign="top">', $txt['sp-adminColumnName'], ':</th>
							<td class="windowbg2"><input type="text" name="block_name" value="', $context['SPortal']['block']['label'], '" size="30" /></td>
						</tr>
						<tr>
							<td class="windowbg2" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp_permissions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
							<th style="text-align:right;" valign="top">', $txt['sp_admin_blocks_col_permissions'], ':</th>
							<td class="windowbg2">
								<select name="permission_set" id="block_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
									<option value="', $id, '"', $id == $context['SPortal']['block']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_blocks_permissions_set_' . $label], '</option>';

	echo '
								</select>
							</td>
						</tr>
						<tr>
							<td class="windowbg2" id="block_custom_permissions_help" valign="top" width="16">&nbsp;</td>
							<th id="block_custom_permissions_label" style="text-align:right;" valign="top">', $txt['sp_admin_blocks_col_custom_permissions'], ':</th>
							<td class="windowbg2" id="block_custom_permissions_input">
								<table>
									<tr>
										<th>', $txt['sp_admin_blocks_custom_permissions_membergroup'], '</th>
										<th title="', $txt['sp_admin_blocks_custom_permissions_allowed'], '">', $txt['sp_admin_blocks_custom_permissions_allowed_short'], '</th>
										<th title="', $txt['sp_admin_blocks_custom_permissions_disallowed'], '">', $txt['sp_admin_blocks_custom_permissions_disallowed_short'], '</th>
										<th title="', $txt['sp_admin_blocks_custom_permissions_denied'], '">', $txt['sp_admin_blocks_custom_permissions_denied_short'], '</th>
									</tr>';

	foreach ($context['SPortal']['block']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['SPortal']['block']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['SPortal']['block']['groups_denied']))
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
							</td>
						</tr>';

	foreach ($context['SPortal']['block']['options'] as $name => $type)
	{
		if (empty($context['SPortal']['block']['parameters'][$name]))
			$context['SPortal']['block']['parameters'][$name] = '';

		echo '
						<tr>
							<td class="windowbg2" valign="top" width="16">';

		if (!empty($helptxt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name]))
			echo '
								<a href="', $scripturl, '?action=helpadmin;help=sp_param_', $context['SPortal']['block']['type'] , '_' , $name, '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>';

		echo '
							</td>
							<th style="text-align:right;" valign="top">', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name], ':</th>
							<td class="windowbg2">';

		if ($type == 'bbc')
		{
			echo '
							<table>
								<tr>';

			theme_postbox($context['SPortal']['block']['parameters'][$name]);

			echo '
								<input type="hidden" name="bbc_name" value="', $name, '" />
								<input type="hidden" name="bbc_parameter" value="', $context['SPortal']['bbc'], '" />
								</tr>
							</table>';
		}
		elseif ($type == 'boards' || $type == 'board_select')
		{
					echo '
									<input type="hidden" name="parameters[', $name, ']" value="" />';
				if($type == 'boards')
					echo '
								<select name="parameters[', $name, '][]" id="', $name, '" size="7" multiple="multiple">';
				else
					echo '
								<select name="parameters[', $name, '][]" id="', $name, '">';

				foreach ($context['SPortal']['block']['board_options'][$name] as $option)
					echo '
									<option value="', $option['value'], '"', ($option['selected'] ? ' selected="selected"' : ''), ' >', $option['text'], '</option>';
				echo '
								</select>';
		}
		elseif ($type == 'int')
			echo '
								<input type="text" name="parameters[', $name, ']" value="', $context['SPortal']['block']['parameters'][$name], '" size="7" />';
		elseif ($type == 'text')
			echo '
								<input type="text" name="parameters[', $name, ']" value="', $context['SPortal']['block']['parameters'][$name] ,'" size="25" />';
		elseif ($type == 'check')
				echo '
							<input type="checkbox" name="parameters[', $name, ']"', !empty($context['SPortal']['block']['parameters'][$name]) ? ' checked="checked"' : '', ' class="check" />';
		elseif ($type == 'select')
		{
				$options = explode('|', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name . '_options']);

				echo '
							<select name="parameters[', $name, ']">';

				foreach ($options as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif (is_array($type))
		{
				echo '
							<select name="parameters[', $name, ']">';

				foreach ($type as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif ($type == 'textarea')
		{
			echo '
							<textarea name="parameters[', $name, ']" id="', $name, '" cols="60" rows="10">', $context['SPortal']['block']['parameters'][$name], '</textarea>
							<input type="button" value="-" onclick="document.getElementById(\'', $name, '\').rows -= 10" />
							<input type="button" value="+" onclick="document.getElementById(\'', $name, '\').rows += 10" />';
		}

		echo '
							</td>
						</tr>';
	}

	if (empty($context['SPortal']['block']['column']))
		echo '
						<tr>
							<td class="windowbg2" valign="top" width="16">&nbsp;</td>
							<th style="text-align:right;" valign="top">', $txt['sp-blocksColumn'], ':</th>
							<td class="windowbg2">
								<select id="block_column" name="block_column">
									<option value="5">', $txt['sp-positionHeader'], '</option>
									<option value="1">', $txt['sp-positionLeft'], '</option>
									<option value="2">', $txt['sp-positionTop'], '</option>
									<option value="3">', $txt['sp-positionBottom'], '</option>
									<option value="4">', $txt['sp-positionRight'], '</option>
									<option value="6">', $txt['sp-positionFooter'], '</option>
								</select>
							</td>
						</tr>';
	else
		echo '
					<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	if (count($context['SPortal']['block']['list_blocks']) > 1)
	{
		echo '
						<tr>
							<td class="windowbg2" valign="top" width="16">&nbsp;</td>
							<th style="text-align:right;" valign="top">', $txt['sp-blocksRow'], ':</th>
							<td class="windowbg2">
								<select id="order" name="placement"', !$context['SPortal']['is_new'] ? ' onchange="this.form.block_row.disabled = this.options[this.selectedIndex].value == \'\';"' : '', '>
									', !$context['SPortal']['is_new'] ? '<option value="nochange">' . $txt['sp-placementUnchanged'] . '</option>' : '', '
									<option value="before">', $txt['sp-placementBefore'], '...</option>
									<option value="after">', $txt['sp-placementAfter'], '...</option>
								</select>
								<select id="block_row" name="block_row"', !$context['SPortal']['is_new'] ? ' disabled="disabled"' : '', '>';

		foreach ($context['SPortal']['block']['list_blocks'] as $block)
			if ($block['id'] != $context['SPortal']['block']['id'])
				echo '
										<option value="', $block['row'], '">', $block['label'], '</option>';

		echo '
								</select>
							</td>
						</tr>';
	}

	if ($context['SPortal']['block']['type'] != 'sp_boardNews')
	{
		echo '
						<tr>
							<td class="windowbg2" valign="top" width="16">&nbsp;</td>
							<th style="text-align:right;" valign="top">', $txt['sp-blocksForce'], ':</th>
							<td class="windowbg2"><input type="checkbox" name="block_force" value="1" id="block_force"', $context['SPortal']['block']['force_view'] ? ' checked="checked"' : '', ' /></td>
						</tr>';
	}

	echo '
						<tr>
							<td class="windowbg2" valign="top" width="16">&nbsp;</td>
							<th style="text-align:right;" valign="top">', $txt['sp-blocksActive'], ':</th>
							<td class="windowbg2"><input type="checkbox" name="block_active" value="1" id="block_active"', $context['SPortal']['block']['state'] ? ' checked="checked"' : '', ' /></td>
						</tr>
						<tr>
							<td colspan="3" align="center"><input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" /> <input type="submit" name="preview_block" value="', $txt['sp-blocksPreview'], '" /></td>
						</tr>
					</table>
					<input type="hidden" name="block_type" value="', $context['SPortal']['block']['type'], '" />
					<input type="hidden" name="block_id" value="', $context['SPortal']['block']['id'], '" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</td>
		</tr>
	</table>';

	if (!empty($modSettings['sp_enableIntegration']))
	{
		echo '
		<br />
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="65%">
			<tr class="catbg">
				<td>
					<a href="', $scripturl, '?action=helpadmin;help=sp-blocksDisplayOptions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
					', $txt['sp-blocksDisplayOptions'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="left">
					<table width="100%">
						<tr>
							<td colspan="2">
								<div style="float: right;">', $txt['sp-blocksAdvancedOptions'], '<input type="checkbox" name="display_advanced" id="display_advanced" onclick="document.getElementById(\'sp_display_advanced\').style.display = this.checked ? \'block\' : \'none\'; document.getElementById(\'display_simple\').disabled = this.checked;" class="check"', empty($context['SPortal']['block']['display_type']) ? '' : ' checked="checked"', ' /></div>
								', $txt['sp-blocksShowBlock'], '
								<select name="display_simple" id="display_simple"', empty($context['SPortal']['block']['display_type']) ? '' : ' disabled="disabled"', '>';

		foreach ($context['simple_actions'] as $action => $label)
			echo '
									<option value="', $action, '"', in_array($action, $context['SPortal']['block']['display']) ? ' selected="selected"' : '', '>', $label, '</option>';

		echo '
								</select>
							</td>
						</tr>
					</table>
					<div id="sp_display_advanced"', empty($context['SPortal']['block']['display_type']) ? ' style="display: none;"' : '', '>
					<div style="padding: 5px;"><a href="javascript:void(0);" onclick="sp_collapseObject(\'actions\')"><img id="sp_collapse_actions" src="', $settings['images_url'], '/expand.gif" alt="*" border="0" /></a>&nbsp;', $txt['sp-blocksSelectActions'], '</div>
					<table width="100%" id="sp_object_actions" style="display: none;">';

		$counter = 1;
		foreach ($context['display_actions'] as $index => $action)
		{
			if ($counter == 1)
				echo '
						<tr>';

			echo '
							<td valign="top" width="50%">
								<input type="checkbox" name="display_actions[]" value="', $index, '"', in_array($index, $context['SPortal']['block']['display']) ? ' checked="checked"' : '', ' />', $action, '
							</td>';

			if ($counter == 2)
			{
				$counter = 0;
				echo '
						</tr>';
			}

			$counter++;
		}

		if (count($context['display_actions']) % 2 != 0)
			echo '
							<td valign="top" width="50%">
								&nbsp;
							</td>
						</tr>';

		echo '
						<tr>
							<td colspan="2" align="right">
								<input type="checkbox" onclick="invertAll(this, this.form, \'display_actions[]\');" /> <em>', $txt[737], '</em>
							</td>
						</tr>
					</table>
					<div style="padding: 5px;"><a href="javascript:void(0);" onclick="sp_collapseObject(\'boards\')"><img id="sp_collapse_boards" src="', $settings['images_url'], '/expand.gif" alt="*" /></a>&nbsp;', $txt['sp-blocksSelectBoards'], '</div>
					<table width="100%" id="sp_object_boards" style="display: none;">';

		$counter = 1;
		foreach ($context['display_boards'] as $index => $board)
		{
			if ($counter == 1)
				echo '
						<tr>';

			echo '
							<td valign="top" width="50%">
								<input type="checkbox" name="display_boards[]" value="', $index, '"', in_array($index, $context['SPortal']['block']['display']) ? ' checked="checked"' : '', ' />', $board, '
							</td>';

			if ($counter == 2)
			{
				$counter = 0;
				echo '
						</tr>';
			}

			$counter++;
		}

		if (count($context['display_boards']) % 2 != 0)
			echo '
							<td valign="top" width="50%">
								&nbsp;
							</td>
						</tr>';

		echo '
						<tr>
							<td colspan="2" align="right">
								<input type="checkbox" onclick="invertAll(this, this.form, \'display_boards[]\');" /> <em>', $txt[737], '</em>
							</td>
						</tr>
					</table>';

		if (!empty($context['display_pages']))
		{
			echo '
					<div style="padding: 5px;"><a href="javascript:void(0);" onclick="sp_collapseObject(\'pages\')"><img id="sp_collapse_pages" src="', $settings['images_url'], '/expand.gif" alt="*" /></a>&nbsp;', $txt['sp-blocksSelectPages'], '</div>
					<table width="100%" id="sp_object_pages" style="display: none;">';

			$counter = 1;
			foreach ($context['display_pages'] as $index => $page)
			{
				if ($counter == 1)
					echo '
						<tr>';

				echo '
							<td valign="top" width="50%">
								<input type="checkbox" name="display_pages[]" value="', $index, '"', in_array($index, $context['SPortal']['block']['display']) ? ' checked="checked"' : '', ' />', $page, '
							</td>';

				if ($counter == 2)
				{
					$counter = 0;
					echo '
						</tr>';
				}

				$counter++;
			}

			if (count($context['display_pages']) % 2 != 0)
				echo '
							<td valign="top" width="50%">
								&nbsp;
							</td>
						</tr>';

			echo '
						<tr>
							<td colspan="2" align="right">
								<input type="checkbox" onclick="invertAll(this, this.form, \'display_pages[]\');" /> <em>', $txt[737], '</em>
							</td>
						</tr>
					</table>';
		}

		echo '
					<br />
					<a href="', $scripturl, '?action=helpadmin;help=sp-blocksCustomDisplayOptions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;', $txt['sp_display_custom'], ': <input type="text" name="display_custom" value="', $context['SPortal']['block']['display_custom'], '" />
					<br /><br />
					</div>
					<div style="text-align: center;"><input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" /></div>
				</td>
			</tr>
		</table>';
	}

	echo '
		<br />
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="65%">
			<tr class="catbg">
				<td>
					<a href="', $scripturl, '?action=helpadmin;help=sp-blocksStyleOptions" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
					', $txt['sp-blocksStyleOptions'], '
				</td>
			</tr>
			<tr class="windowbg2">
				<td align="left">
					<table width="100%">
						<tr>
							<td style="text-align: right;">
								', $txt['sp-blocksTitleDefaultClass'], ':
							</td>
							<td>
								<select name="title_default_class" id="title_default_class">
									<option value="catbg"', $context['SPortal']['block']['style']['title_default_class'] == 'catbg' ? ' selected="selected"' : '', '>catbg</option>
									<option value="catbg2"', $context['SPortal']['block']['style']['title_default_class'] == 'catbg2' ? ' selected="selected"' : '', '>catbg2</option>
									<option value="catbg3"', $context['SPortal']['block']['style']['title_default_class'] == 'catbg3' ? ' selected="selected"' : '', '>catbg3</option>
									<option value="titlebg"', $context['SPortal']['block']['style']['title_default_class'] == 'titlebg' ? ' selected="selected"' : '', '>titlebg</option>
									<option value="titlebg2"', $context['SPortal']['block']['style']['title_default_class'] == 'titlebg2' ? ' selected="selected"' : '', '>titlebg2</option>
								</select>
							</td>
							<td style="text-align: right;">
								', $txt['sp-blocksBodyDefaultClass'], ':
							</td>
							<td>
								<select name="body_default_class" id="body_default_class">
									<option value="windowbg"', $context['SPortal']['block']['style']['body_default_class'] == 'windowbg' ? ' selected="selected"' : '', '>windowbg</option>
									<option value="windowbg2"', $context['SPortal']['block']['style']['body_default_class'] == 'windowbg2' ? ' selected="selected"' : '', '>windowbg2</option>
									<option value="windowbg3"', $context['SPortal']['block']['style']['body_default_class'] == 'windowbg3' ? ' selected="selected"' : '', '>windowbg3</option>
								</select>
							</td>
						</tr>
						<tr>
							<td style="text-align: right;">
								', $txt['sp-blocksTitleCustomClass'], ':
							</td>
							<td>
								<input type="text" name="title_custom_class" id="title_custom_class" value="', $context['SPortal']['block']['style']['title_custom_class'], '" />
							</td>
							<td style="text-align: right;">
								', $txt['sp-blocksBodyCustomClass'], ':
							</td>
							<td>
								<input type="text" name="body_custom_class" id="body_custom_class" value="', $context['SPortal']['block']['style']['body_custom_class'], '" />
							</td>
						</tr>
						<tr>
							<td style="text-align: right;">
								', $txt['sp-blocksTitleCustomStyle'], ':
							</td>
							<td>
								<input type="text" name="title_custom_style" id="title_custom_style" value="', $context['SPortal']['block']['style']['title_custom_style'], '" />
							</td>
							<td style="text-align: right;">
								', $txt['sp-blocksBodyCustomStyle'], ':
							</td>
							<td>
								<input type="text" name="body_custom_style" id="body_custom_style" value="', $context['SPortal']['block']['style']['body_custom_style'], '" />
							</td>
						</tr>
						<tr>
							<td style="text-align: right;">
								', $txt['sp-blocksNoTitle'], ':
							</td>
							<td>
								<input type="checkbox" name="no_title" id="no_title" value="1"', !empty($context['SPortal']['block']['style']['no_title']) ? ' checked="checked"' : '', ' onclick="document.getElementById(\'title_default_class\').disabled = document.getElementById(\'title_custom_class\').disabled = document.getElementById(\'title_custom_style\').disabled = this.checked;" />
							</td>
							<td style="text-align: right;">
								', $txt['sp-blocksNoBody'], ':
							</td>
							<td>
								<input type="checkbox" name="no_body" id="no_body" value="1"', !empty($context['SPortal']['block']['style']['no_body']) ? ' checked="checked"' : '', ' onclick="document.getElementById(\'body_default_class\').disabled = this.checked;" />
							</td>
						</tr>
					</table>
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						document.getElementById("title_default_class").disabled = document.getElementById("no_title").checked;
						document.getElementById("title_custom_class").disabled = document.getElementById("no_title").checked;
						document.getElementById("title_custom_style").disabled = document.getElementById("no_title").checked;
						document.getElementById("body_default_class").disabled = document.getElementById("no_body").checked;
					// ]]></script>
					<div style="text-align: center;"><input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" /></div>
				</td>
			</tr>
		</table>
	</form>
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		sp_update_permissions();

		function sp_update_permissions()
		{
			var new_state = document.getElementById("block_permission_set").value;
			document.getElementById("block_custom_permissions_help").style.display = new_state != 0 ? "none" : "";
			document.getElementById("block_custom_permissions_label").style.display = new_state != 0 ? "none" : "";
			document.getElementById("block_custom_permissions_input").style.display = new_state != 0 ? "none" : "";
		}
	// ]]></script>';
}

function template_block_select_type()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="80%">
		<tr class="catbg">
			<td>
				<a href="', $scripturl, '?action=helpadmin;help=sp-blocksSelectType" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
				', $txt['sp-blocksSelectType'], '
			</td>
		</tr>
		<tr class="windowbg">
			<td align="center">
				<form action="', $scripturl, '?action=manageportal;area=portalblocks;sa=add" method="post" accept-charset="', $context['character_set'], '">
					<table cellpadding="4">';

	$row_check = 0;
	$function_count = 0;

	foreach ($context['SPortal']['block_types'] as $type)
	{
		$function_count = $function_count + 1;

		if ($row_check == 0)
			echo '
						<tr>';

		echo '
							<td width="190px" valign="top" class="windowbg2">
								<input type="radio" name="selected_type[]" id="block_', $type['function'], '" value="', $type['function'], '" class="check" />&nbsp;&nbsp;<label for="block_', $type['function'], '"><b>', $txt['sp_function_' . $type['function'] . '_label'], '</b></label><br /><br />
								<span class="smalltext">', $txt['sp_function_' . $type['function'] . '_desc'], '<br /><br /></span>
							</td>';

		$row_check = $row_check + 1;
		if ($row_check == 3)
		{
			echo '
						</tr>';
			$row_check = 0;
		}
	}

	if ($function_count % 3 != 0)
	{
		$empty_cells = 3 - ($function_count % 3);
		while($empty_cells > 0)
		{
			echo '
							<td class="windowbg2">
								&nbsp;
							</td>';
			$empty_cells = $empty_cells - 1;
		}

			echo '
						</tr>';
	}

	echo '
						<tr>
							<td colspan="3" align="center"><input type="submit" name="select_type" value="', $txt['sp-blocksSelectType'], '" /></td>
						</tr>
					</table>';

	if (!empty($context['SPortal']['block']['column']))
		echo '
					<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	echo '
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

?>