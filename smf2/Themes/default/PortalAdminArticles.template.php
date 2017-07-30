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

function template_article_list()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_manage_articles">
		<form action="', $scripturl, '?action=admin;area=portalarticles;sa=articles" method="post" accept-charset="', $context['character_set'], '" onsubmit="return confirm(\'', $txt['sp-articlesConfirm'], '\');">
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

	if (empty($context['total_articles']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">&nbsp;</td>
					</tr>';
	}

	while ($article = $context['get_article']())
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_left">', $article['topic']['link'], '</td>
						<td class="sp_left">', $article['board']['link'], '</td>
						<td class="sp_center">', $article['poster']['link'], '</td>
						<td class="sp_center">', $article['message']['time'], '</td>
						<td class="sp_left">', $article['category']['name'], '</td>
						<td class="sp_center"><a href="', $scripturl, '?action=admin;area=portalarticles;sa=statechange;article_id=', $article['article']['id'], ';type=article;', $context['session_var'], '=', $context['session_id'], '">', empty($article['article']['approved']) ? sp_embed_image('deactive', $txt['sp-stateNo']) : sp_embed_image('active', $txt['sp-stateYes']), '</a></td>
						<td class="sp_center">', $article['edit'], ' ', $article['delete'], '</td>
						<td class="sp_center"><input type="checkbox" name="remove[]" value="', $article['article']['id'], '" class="input_check" /></td>
					</tr>';
	}

	echo '
				</tbody>
			</table>
			<div class="sp_align_left pagesection">
				<div class="sp_float_right">
					<input type="submit" name="removeArticles" value="', $txt['sp-articlesRemove'], '" class="button_submit" />
				</div>
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_article_add()
{
	global $context, $settings, $txt, $scripturl;

	echo '
	<div id="sp_article_add">
		<form action="' . $scripturl . '?action=admin;area=portalarticles;sa=addarticle;targetboard=' . $context['target_board'] . '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					<a href="', $scripturl, '?action=helpadmin;help=sp-articlesAdd" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>&nbsp;
					', $txt['sp-articlesAdd'], '
				</h3>
			</div>
			<div id="sp_add_articles_category" class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp-articlesCategory" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="category">', $txt['sp-articlesCategory'], ':</label>
						</dt>
						<dd>
							<select id="category" name="category">';

	foreach($context['list_categories'] as $category)
		echo '
								<option value="', $category['id'], '">', $category['name'], '</option>';

	echo '
							</select>
						</dd>
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<div class="sp_align_left pagesection">
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">';

	if (!empty($context['boards']) && count($context['boards']) > 1)
	{
		echo '
					<dl class="sp_form">
						<dt>				
							<a href="', $scripturl, '?action=helpadmin;help=sp-articlesBoards" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="targetboard">', $txt['board'], ':</label>
						</dt>
						<dd>
							<select name="targetboard" id="targetboard" onchange="this.form.submit();">';

		foreach ($context['boards'] as $board)
			echo '
								<option value="', $board['id'], '"', $board['id'] == $context['target_board'] ? ' selected="selected"' : '', '>', $board['category'], ' - ', $board['name'], '</option>';
		echo '
							</select><noscript>
							<input type="submit" value="', $txt['sp-articlesAdd'], '" class="button_submit" /></noscript>
						</dd>
					</dl>';
	}

	echo '
					<div id="sp_add_articles_list_header">
						<a href="', $scripturl, '?action=helpadmin;help=sp-articlesTopics" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
						', $txt['topics'], ':';
	
	if (!empty($context['topics']))
	{
		echo '
					</div>
					<ul id="sp_add_articles_list">';

		foreach ($context['topics'] as $topic)
			echo '
						<li>
							<input type="checkbox" name="articles[]" value="', $topic['msg_id'], '" class="input_check" />
							<a href="' . $scripturl . '?topic=' . $topic['id'] . '.0" target="_blank">' . $topic['subject'] . '</a> ' . $txt['started_by'] . ' ' . $topic['poster']['link'] . '
						</li>';
		echo '
					</ul>
					<div id="sp_add_articles_button" class="sp_button_container">
						<input type="submit" name="createArticle" value="', $txt['sp-articlesAdd'], '" class="button_submit" />
					</div>';
	}
	else
		echo '
						', $txt['sp-adminArticleAddNoTopics'], '
					</div>';

	echo '
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<div class="sp_align_left pagesection">
				', $txt['pages'], ': ', $context['page_index'], '
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_article_edit()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_edit_category">
		<form action="', $scripturl, '?action=admin;area=portalarticles;sa=editarticle" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					<a href="', $scripturl, '?action=helpadmin;help=sp-articlesEdit" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
					', $txt['sp-articlesEdit'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp-articlesCategory" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="category">', $txt['sp-articlesCategory'], ':</label>
						</dt>
						<dd>
							<select id="category" name="category">';

	foreach($context['list_categories'] as $category)
		echo '
								<option value="' . $category['id'] . '"' . ($context['article_info']['category']['id'] == $category['id'] ? ' selected="selected"' : '') . ' >' . $category['name'] . '</option>';

	echo '
							</select>
						</dd>
						<dt>
							<a href="', $scripturl, '?action=helpadmin;help=sp-articlesApproved" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
							<label for="approved">', $txt['sp-articlesApproved'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="approved" value="1" id="approved"', !empty($context['article_info']['article']['approved']) ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="add_article" value="', $txt['sp-articlesEdit'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="article_id" value="', $context['article_info']['article']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_category_list()
{
	global $context, $scripturl, $txt;

	echo '
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">';

	foreach ($context['columns'] as $column)
	{
			echo '
					<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
						', $column['label'], '
					</th>';
	}

	echo '
				</tr>
			</thead>
			<tbody>';

	if (empty($context['categories']))
	{
		echo '
					<tr class="windowbg2">
						<td class="sp_center" colspan="', count($context['columns']) + 1, '">&nbsp;</td>
					</tr>';
	}

	foreach($context['categories'] as $category)
	{
		echo '
				<tr class="windowbg2">
					<td class="sp_center">', !empty($category['picture']['href']) ? $category['picture']['image'] : '', '</td>
					<td class="sp_left">', $category['name'], '</td>
					<td class="sp_center">', $category['articles'], '</td>
					<td class="sp_center"><a href="', $scripturl, '?action=admin;area=portalarticles;sa=statechange;category_id=', $category['id'], ';type=category;', $context['session_var'], '=', $context['session_id'], '">', empty($category['publish']) ? sp_embed_image('deactive', $txt['sp-stateNo']) : sp_embed_image('active', $txt['sp-stateYes']), '</a></td>
					<td class="sp_center"><a href="', $scripturl, '?action=admin;area=portalarticles;sa=editcategory;category_id=', $category['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('modify'), '</a> <a href="', $scripturl, '?action=admin;area=portalarticles;sa=deletecategory;category_id=', $category['id'], ';', $context['session_var'], '=', $context['session_id'], '"', (empty($category['articles']) ? ' onclick="return confirm(\'' . $txt['sp-categoriesDeleteConfirm'] . '\');"' : ''), '>', sp_embed_image('delete'), '</a></td>
				</tr>';
	}
	echo '
			</tbody>
		</table>';
}

function template_category_edit()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_edit_category">
		<form action="', $scripturl, '?action=admin;area=portalarticles;sa=', $context['category_action'], 'category" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					<a href="', $scripturl, '?action=helpadmin;help=sp-categories', ucfirst($context['category_action']), '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
					', $context['page_title'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<label for="category_name">', $txt['sp-categoriesName'], ':</label>
						</dt>
						<dd>
							<input type="text" name="category_name" id="category_name" value="', !empty($context['category_info']['name']) ? $context['category_info']['name'] : '', '" size="20" class="input_text"/>
						</dd>
						<dt>
							<label for="category_picture">', $txt['sp-categoriesPicture'], ':</label>
						</dt>
						<dd>
							<input type="text" name="picture_url" id="category_picture" value="', !empty($context['category_info']['picture']['href']) ? $context['category_info']['picture']['href'] : '', '" size="30" class="input_text"/>
						</dd>
						<dt>
							<label for="category_publish">', $txt['sp-categoriesPublish'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="show_on_index" id="category_publish" value="1"', !empty($context['category_info']['publish']) || $context['category_action'] == 'add' ? ' checked="checked"' : '', ' class="input_check"/>
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="edit_category" value="1" />';

	if ($context['category_action'] == 'edit')
		echo '
			<input type="hidden" name="category_id" value="', $context['category_info']['id'], '" />';

	echo '
		</form>
	</div>';
}

function template_category_delete()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_edit_category">
		<form action="', $scripturl, '?action=admin;area=portalarticles;sa=deletecategory" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					<a href="', $scripturl, '?action=helpadmin;help=sp-categoriesDelete" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" class="icon" /></a>
					', $txt['sp-categoriesDelete'], '
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="sp_content_padding">
					<div class="sp_center">
					', sprintf($txt['sp-categoriesDeleteCount'], $context['category_info']['articles']), '<br />';

	if (!empty($context['list_categories'])) 
	{
		echo '
					', $txt['sp-categoriesDeleteOption1'], '
					</div>
					<dl class="sp_form">
						<dt>
							<label for="category_move">', $txt['sp-categoriesMove'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="category_move" value="1" id="category_move" checked="checked" class="input_check" />
						</dd>
						<dt>
							<label for="category_move_to">', $txt['sp-categoriesMoveTo'], ':</label>
						</dt>
						<dd>
							<select id="category_move_to" name="category_move_to">';

		foreach($context['list_categories'] as $category) 
		{
			if ($category['id'] != $context['category_info']['id'])
				echo '
								<option value="', $category['id'], '">', $category['name'], '</option>';
		}

							echo '
							</select>
						</dd>
					</dl>';
	}
	else
	{
		echo '
				', $txt['sp-categoriesDeleteOption2'], '
				</div>';
	}

	echo '
					<div class="sp_button_container">
						<input type="submit" name="delete_category" value="', $txt['sp-categoriesDelete'], '" onclick="return confirm(\'' . $txt['sp-categoriesDeleteConfirm'] . '\');" class="button_submit" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<input type="hidden" name="category_id" value="', $context['category_info']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

?>