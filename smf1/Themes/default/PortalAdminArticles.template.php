<?php
// Version: 2.3.4; PortalAdminArticles

function template_article_list()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	<form action="', $scripturl, '?action=manageportal;area=portalarticles;sa=articles" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp-articlesConfirm'], '\');">
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
			<tr class="catbg3">
				<td colspan="8"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr><tr class="titlebg">';
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

	while ($article = $context['get_article']())
	{
		echo '
			<tr>
				<td align="left" valign="top" class="windowbg">', $article['topic']['link'], '</td>
				<td align="left" valign="top" class="windowbg">', $article['board']['link'], '</td>
				<td align="center" valign="top" class="windowbg">', $article['poster']['link'], '</td>
				<td align="center" valign="top" class="windowbg">', $article['message']['time'], '</td>
				<td align="left" valign="top" class="windowbg">', $article['category']['name'], '</td>
				<td align="center" valign="top" class="windowbg"><a href="' . $scripturl . '?action=manageportal;area=portalarticles;sa=statechange;article_id=' . $article['article']['id'] . ';type=article;sesc=' . $context['session_id'] . '" title="', empty($article['article']['approved']) ? $txt['sp-blocksActivate'] : $txt['sp-blocksDeactivate'], '">', empty($article['article']['approved']) ? $txt['sp-stateNo'] : $txt['sp-stateYes'], '</a></td>
				<td align="center" valign="top" class="windowbg">', $article['edit'], $article['delete'], '</td>
				<td align="center" valign="top" class="windowbg2"><input type="checkbox" name="remove[]" value="', $article['article']['id'], '" class="check" /></td>
			</tr>';
	}
	echo '
			<tr class="catbg3">
				<td colspan="8" align="left">
					<div style="float: left;">
						<b>', $txt[139], ':</b> ', $context['page_index'], '
					</div>
					<div style="float: right;">
						<input type="submit" name="removeArticles" value="', $txt['sp-articlesRemove'], '" />
					</div>
				</td>
			</tr>
		</table>
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_article_add()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
		<form action="' . $scripturl . '?action=manageportal;area=portalarticles;sa=addarticle;targetboard=' . $context['target_board'] . '" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" width="540" cellspacing="1" class="bordercolor" cellpadding="4" align="center">
				<tr class="catbg3">
					<td>
						<a href="', $scripturl, '?action=helpadmin;help=sp-articlesAdd" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
						', $txt['sp-articlesAdd'], '
					</td>
				</tr>
				<tr>
					<td class="windowbg" align="center">
						<table border="0" width="100%">
							<tr>
								<td class="windowbg" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp-articlesCategory" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
								<td style="text-align:right;">', $txt['sp-articlesCategory'], ':</td>
								<td width="50%">
									<select id="category" name="category">';

	foreach($context['list_categories'] as $category)
		echo '
										<option value="' . $category['id'] . '">' . $category['name'] . '</option>';

	echo '
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="3" class="titlebg">
						<table cellpadding="0" cellspacing="0" border="0"><tr>
							<td><b>' . $txt[139] . ':</b> ' . $context['page_index'] . '</td>
						</tr></table>
					</td>
				</tr>
				<tr>
					<td class="windowbg" valign="middle" align="center">
						<table border="0" width="100%">
							<tr>';

	if (!empty($context['boards']) && count($context['boards']) > 1)
	{
		echo '
								<td class="windowbg" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp-articlesBoards" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
								<td align="right"><br /><b>', $txt['smf82'], ':</b></td>
								<td align="left" width="50%">
									<br />
									<select name="targetboard" onchange="this.form.submit();">';
		foreach ($context['boards'] as $board)
			echo '
										<option value="', $board['id'], '"', $board['id'] == $context['target_board'] ? ' selected="selected"' : '', '>', $board['category'], ' - ', $board['name'], '</option>';
		echo '
									</select><noscript><input type="submit" value="', $txt['sp-articlesAdd'], '" /></noscript>
								</td>
								<td class="windowbg" valign="top" width="16"></td>';
	}

	echo '
							</tr><tr>
								<td class="windowbg" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp-articlesTopics" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
								<td align="right" valign="top"><br /><b>', $txt[64], ':</b></td>
								<td align="left" style="white-space: nowrap;" width="50%">
									<br />';

	if (!empty($context['topics']))
	{
		echo '
									<table>';

		foreach ($context['topics'] as $topic)
			echo '
										<tr>
											<td align="center" valign="top" class="windowbg">
												<input type="checkbox" name="articles[]" value="', $topic['msg_id'], '" class="check" />
											</td>
											<td valign="middle" style="white-space: nowrap;">
												<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0" target="_blank">' . $topic['subject'] . '</a> ' . $txt[109] . ' ' . $topic['poster']['link'] . '
											</td>
										</tr>';
		echo '
									</table>';
	}
	else
		echo '
									', $txt['sp-adminArticleAddNoTopics'];
	
	echo '
								</td>
								<td class="windowbg" valign="top" width="16"></td>
							</tr>
						</table>
					</td>
				</tr>';

	if (!empty($context['topics']))
		echo '
				<tr>
					<td colspan="2" class="windowbg2" align="center">
						<input type="submit" name="createArticle" value="', $txt['sp-articlesAdd'], '" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</td>
				</tr>';

	echo '
				<tr>
					<td colspan="2" class="titlebg">
						<table cellpadding="0" cellspacing="0" border="0"><tr>
							<td><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
						</tr></table>
					</td>
				</tr>
			</table>
		</form>';
}

function template_article_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="60%">
		<tr class="catbg">
			<td>
				<a href="', $scripturl, '?action=helpadmin;help=sp-articlesEdit" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
				', $txt['sp-articlesEdit'], '
			</td>
		</tr>
		<tr class="windowbg2">
			<td align="center">
				<form action="', $scripturl, '?action=manageportal;area=portalarticles;sa=editarticle" method="post" accept-charset="', $context['character_set'], '">
					<table border="0" cellspacing="0" cellpadding="4" width="100%">
						<tr>
							<td class="windowbg2" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp-articlesCategory" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
							<th style="text-align:right;" valign="top">', $txt['sp-articlesCategory'], ':</th>
							<td class="windowbg2" width="50%"">
								<select id="category" name="category">';
	foreach($context['list_categories'] as $category)
		echo '
									<option value="' . $category['id'] . '"' . ($context['article_info']['category']['id'] == $category['id'] ? ' selected="selected"' : '') . ' >' . $category['name'] . '</option>';

	echo '
								</select>
							</td>
						</tr>
						<tr>
							<td class="windowbg2" valign="top" width="16"><a href="', $scripturl, '?action=helpadmin;help=sp-articlesApproved" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a></td>
							<th style="text-align:right;" valign="top">', $txt['sp-articlesApproved'], ':</th>
							<td class="windowbg2" width="50%"">
								<input type="checkbox" name="approved" value="1" id="approved"', !empty($context['article_info']['article']['approved']) ? ' checked="checked"' : '', ' />
							</td>
						</tr>
						<tr>
							<td colspan="3" align="center"><input type="submit" name="add_article" value="', $txt['sp-articlesEdit'], '" /></td>
						</tr>
					</table>
				<input type="hidden" name="article_id" value="', $context['article_info']['article']['id'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

function template_category_list()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
			<tr class="catbg3">
				<td colspan="5" align="left">
					<div style="float: left;">
						<a href="', $scripturl, '?action=helpadmin;help=sp-categoriesCategories" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>
						<b>', $txt['sp-categoriesCategories'], '</b>
					</div>
				</td>
			</tr>';

	echo '
			<tr class="titlebg">';

	foreach ($context['columns'] as $column)
	{
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['label'], '
				</th>';
	}
	echo '
			</tr>';

	foreach($context['categories'] as $category)
	{
		echo '
			<tr>
				<td align="center" valign="top" class="windowbg">', !empty($category['picture']['href']) ? $category['picture']['image'] : '', '</td>
				<td align="left" valign="middle" class="windowbg2">', $category['name'], '</td>
				<td align="center" valign="middle" class="windowbg2">', $category['articles'], '</td>
				<td align="center" valign="middle" class="windowbg2"><a href="', $scripturl, '?action=manageportal;area=portalarticles;sa=statechange;category_id=', $category['id'], ';type=category;sesc=', $context['session_id'], '">', empty($category['publish']) ? sp_embed_image('deactive', $txt['sp-stateNo']) : sp_embed_image('active', $txt['sp-stateYes']), '</a></td>
				<td align="center" valign="middle" class="windowbg2"><a href="', $scripturl, '?action=manageportal;area=portalarticles;sa=editcategory;category_id=', $category['id'], ';sesc=', $context['session_id'], '">', sp_embed_image('modify'), '</a> <a href="', $scripturl, '?action=manageportal;area=portalarticles;sa=deletecategory;category_id=', $category['id'], ';sesc=', $context['session_id'], '"', (empty($category['articles']) ? ' onclick="return confirm(\'' . $txt['sp-categoriesDeleteConfirm'] . '\');"' : ''), '>', sp_embed_image('delete'), '</a></td>
			</tr>';
	}
	echo '
			<tr class="catbg3">
				<td colspan="5" align="left">
				</td>
			</tr>
		</table>';
}

function template_category_add()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="60%">
		<tr class="catbg">
			<td>
				<a href="', $scripturl, '?action=helpadmin;help=sp-categoriesAdd" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
				', $txt['sp-categoriesAdd'], '
			</td>
		</tr>
		<tr class="windowbg2">
			<td align="center">
				<form action="', $scripturl, '?action=manageportal;area=portalarticles;sa=addcategory" method="post" accept-charset="', $context['character_set'], '">
					<table cellpadding="4">
						<tr>
							<th align="right">', $txt['sp-categoriesName'], ':</th>
							<td align="left"><input type="text" name="category_name" value="" size="20" /></td>
						</tr><tr>
							<th align="right">', $txt['sp-categoriesPicture'], ':</th>
							<td align="left"><input type="text" name="picture_url" value="" size="30" /></td>
						</tr><tr>
							<th align="right">', $txt['sp-categoriesPublish'], ':</th>
							<td align="left"><input type="checkbox" name="show_on_index" value="1" id="show_on_index" checked="checked" /></td>
						</tr><tr>
							<td colspan="2" align="right"><input type="submit" name="add_category" value="', $txt['sp-categoriesAdd'], '" /></td>
						</tr>
					</table>
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

function template_category_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="60%">
		<tr class="catbg">
			<td>
				<a href="', $scripturl, '?action=helpadmin;help=sp-categoriesEdit" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
				', $txt['sp-categoriesEdit'], '
			</td>
		</tr>
		<tr class="windowbg2">
			<td align="center">
				<form action="', $scripturl, '?action=manageportal;area=portalarticles;sa=editcategory" method="post" accept-charset="', $context['character_set'], '">
					<table cellpadding="4">
						<tr>
							<th align="right">', $txt['sp-categoriesName'], ':</th>
							<td align="left"><input type="text" name="category_name" value="', $context['category_info']['name'], '" size="20" /></td>
						</tr><tr>
							<th align="right">', $txt['sp-categoriesPicture'], ':</th>
							<td align="left"><input type="text" name="picture_url" value="', $context['category_info']['picture']['href'], '" size="30" /></td>
						</tr><tr>
							<th align="right">', $txt['sp-categoriesPublish'], ':</th>
							<td align="left"><input type="checkbox" name="show_on_index" value="1" id="show_on_index"', !empty($context['category_info']['publish']) ? ' checked="checked"' : '', ' /></td>
						</tr><tr>
							<td colspan="2" align="right"><input type="submit" name="add_category" value="', $txt['sp-categoriesEdit'], '" /></td>
						</tr>
					</table>
				<input type="hidden" name="category_id" value="', $context['category_info']['id'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

function template_category_delete()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="60%">
		<tr class="catbg">
			<td>
				<a href="', $scripturl, '?action=helpadmin;help=sp-categoriesDelete" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt[119], '" border="0" align="top" /></a>&nbsp;
				', $txt['sp-categoriesDelete'], '
			</td>
		</tr>
		<tr class="windowbg2">
			<td align="center">
				<form action="', $scripturl, '?action=manageportal;area=portalarticles;sa=deletecategory" method="post" accept-charset="', $context['character_set'], '">
					<table cellpadding="4">
						<tr>
							<td colspan="2" align="center">';
							printf($txt['sp-categoriesDeleteCount'], $context['category_info']['articles']);
	echo '<br />', !empty($context['list_categories']) ? $txt['sp-categoriesDeleteOption1'] : $txt['sp-categoriesDeleteOption2'], '</td>
						</tr>';
	if(!empty($context['list_categories'])) {
		echo '
						<tr>
							<th align="right">', $txt['sp-categoriesMove'], ':</th>
							<td align="left"><input type="checkbox" name="category_move" value="1" id="category_move" checked="checked" /></td>
						</tr>
						<tr>
							<th align="right">', $txt['sp-categoriesMoveTo'], ':</th>
							<td align="left"><select id="category_move_to" name="category_move_to">';
								foreach($context['list_categories'] as $category) {
									if($category['id'] != $context['category_info']['id'])
										echo '
										<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
								}
								echo '
								</select></td>
						</tr>';
	}
	echo '
						<tr>
							<td colspan="2" align="center"><input type="submit" name="delete_category" value="', $txt['sp-categoriesDelete'], '" /></td>
						</tr>
					</table>
				<input type="hidden" name="category_id" value="', $context['category_info']['id'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

?>