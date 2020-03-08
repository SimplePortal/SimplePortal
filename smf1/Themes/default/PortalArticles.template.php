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

function template_articles()
{
	global $context, $txt, $modSettings, $scripturl;

	if (empty($modSettings['articleactive']))
		return;

	while ($article = $context['get_articles']())
	{
		echo '
					<div class="tborder sp_article_content">
						<table class="sp_block">
							<tr class="catbg">
								<td class="sp_middle">', $article['message']['icon'], '</td>
								<td class="sp_middle sp_regular_padding sp_fullwidth">', $article['topic']['link'], '</td>
							</tr>
							<tr class="windowbg">
								<td class="sp_regular_padding" colspan="2">';

		if (!empty($modSettings['articleavatar']) && $article['poster']['avatar']['name'] !== null && !empty($article['poster']['avatar']['href']))
			echo '
									<a href="', $scripturl, '?action=profile;u=', $article['poster']['id'], '"><img src="', $article['poster']['avatar']['href'], '" alt="', $article['poster']['name'], '" width="30" style="float: right;" /></a>
									<div class="middletext">', $article['message']['time'], ' ', $txt[525], ' ', $article['poster']['link'], '<br />', $txt['sp-articlesViews'], ': ', $article['topic']['views'], ' | ', $txt['sp-articlesComments'], ': ', $article['topic']['replies'], '</div>';
		else
			echo '
									<div class="middletext">', $article['message']['time'], ' ', $txt[525], ' ', $article['poster']['link'], ' | ', $txt['sp-articlesViews'], ': ', $article['topic']['views'], ' | ', $txt['sp-articlesComments'], ': ', $article['topic']['replies'], '</div>';

		echo '
									<div class="post"><hr />', !empty($article['category']['picture']['href']) ? '<div><img src="' . $article['category']['picture']['href'] . '" alt="' . $article['category']['name'] . '" class="sp_article_image" align="right" /></div>' : '', $article['message']['body'], '<br/><br/>
									</div>
								</td>
							</tr>
							<tr>
								<td class="windowbg2" colspan="2">
									<div class="sp_right sp_regular_padding">', $article['article']['link'], ' ',  $article['article']['new_comment'], '</div>
								</td>
							</tr>
						</table>
					</div>';
	}

	if (!empty($modSettings['articleperpage']) && !empty($context['page_index']))
		echo '
					<div class="sp_page_index">', $txt['sp-articlesPages'], ': ', $context['page_index'], '</div>';
}

function template_add_article()
{
	global $context, $scripturl, $txt;

	echo '<br />
	<table border="0" align="center" cellspacing="1" cellpadding="4" class="tborder" width="40%">
		<tr class="catbg">
			<td>', $txt['sp-articlesAdd'], '</td>
		</tr>
		<tr class="windowbg2">
			<td align="center">
				<form action="', $scripturl, '?action=portal;sa=addarticle" method="post" accept-charset="', $context['character_set'], '">
					<table cellpadding="4">
						<tr>
							<th align="right">', $txt['sp-articlesCategory'], ':</th>
							<td align="left">
								<select id="category" name="category">';

								foreach($context['list_categories'] as $category)
									echo '
									<option value="', $category['id'], '">', $category['name'], '</option>';

								echo '
								</select>
							</td>
						</tr><tr>
							<td colspan="2" align="center"><input type="submit" name="add_article" value="', $txt['sp-articlesAdd'], '" /></td>
						</tr>
					</table>
				<input type="hidden" name="return" value="', $context['return'], '" />
				<input type="hidden" name="message" value="', $context['message'], '" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				</form>
			</td>
		</tr>
	</table>';
}

?>