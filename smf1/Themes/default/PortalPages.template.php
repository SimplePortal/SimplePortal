<?php
// Version: 2.3.6; PortalPages

function template_view_page()
{
	global $context;

	echo '
				<div style="padding: 3px;">', theme_linktree(), '</div>
				<div', !empty($context['SPortal']['page']['style']['no_body']) ? '' : ' class="tborder"', '>
					<table class="sp_block">';

	if (empty($context['SPortal']['page']['style']['no_title']))
		echo '
						<tr>
							<td class="sp_block_padding ', $context['SPortal']['page']['style']['title']['class'], '"', !empty($context['SPortal']['page']['style']['title']['style']) ? ' style="' . $context['SPortal']['page']['style']['title']['style'] . '"' : '', '>
								', $context['SPortal']['page']['title'], '
							</td>
						</tr>';

	echo '
						<tr>
							<td class="sp_block_padding', empty($context['SPortal']['page']['style']['body']['class']) ? '' : ' ' . $context['SPortal']['page']['style']['body']['class'], '"', !empty($context['SPortal']['page']['style']['body']['style']) ? ' style="' . $context['SPortal']['page']['style']['body']['style'] . '"' : '', '>';

	sportal_parse_page($context['SPortal']['page']['body'], $context['SPortal']['page']['type']);

	echo '
							</td>
						</tr>
					</table>
				</div>';
}

?>