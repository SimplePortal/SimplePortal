<?php
// Version: 2.3.5; SPortalHelp

global $helptxt;

// Configuration area
$helptxt['sp_ConfigurationArea'] = 'Here you can configure SimplePortal to meet your needs.';

// General settings
$helptxt['portalactive'] = 'This will enable the portal page.';
$helptxt['sp_portal_mode'] = 'SimplePortal can run in several modes. This option enables you to select the mode you wish to use. Supported modes include:<br /><br />
<strong>Disabled:</strong> This will completely disable the portal.<br /><br />
<strong>Frontpage:</strong> This is the default setting. The portal page will greet viewers instead of the board index. Members will be able to access the board index by using the "forum" action, which can be accessed through the "forum" button.<br /><br />
<strong>Integration:</strong> This will disable the portal page. Blocks are only usable in forum.<br /><br />
<strong>Standalone:</strong> This will enable the portal to be displayed at a different url, away from the forum. The portal page appears in the url defined for the "Standalone URL" option. For details, check the PortalStandalone.php file found inside the forum root.';
$helptxt['sp_maintenance'] = 'When maintenance is enabled, portal is only visible by members with Moderate SimplePortal permission.';
$helptxt['sp_standalone_url'] = 'Full URL to the standalone file.<br /><br />Example: http://myforum.com/portal.php';
$helptxt['portaltheme'] = 'Select the theme which will be used for the portal.';
$helptxt['sp_disableForumRedirect'] = 'If this box is unchecked, users will be redirected to the portal after they login or logout. If this box is checked, then users will be redirected to the BoardIndex.';
$helptxt['sp_disableColor'] = 'If the Member Color Link mod is installed, this will disable the mod on the portal (except in the who\'s online list).';
$helptxt['sp_disable_random_bullets'] = 'Disables random coloring for bullet images used in portal lists.';
$helptxt['sp_disable_php_validation'] = 'Disables validation of PHP block codes, which is to prevent syntax and database errors in code.';
$helptxt['sp_disable_side_collapse'] = 'Disables ability to collapse the left and right sides of the portal.';
$helptxt['sp_resize_images'] = 'Enables resizing images in articles and board news to 300x300px, to prevent possible overflows.';

// Block settings
$helptxt['showleft'] = 'This will enable left side blocks on the portal and inside the forum.';
$helptxt['showright'] = 'This will enable right side blocks on the portal and inside the forum.';
$helptxt['leftwidth'] = 'If left side blocks are enabled, their width can be specified here. The width can be specified in pixels (px) or in percentages (%).';
$helptxt['rightwidth'] = 'If right side blocks are enabled, their width can be specified here. The width can be specified in pixels (px) or in percentages (%).';
$helptxt['sp_enableIntegration'] = 'This setting enables blocks inside the forum. It allows the advanced <em>Display Options</em> for each block to be specified.';
$helptxt['sp_IntegrationHide'] = 'Hide blocks in certain forum sections. The <em>Display blocks in Forum</em> setting must be enabled for this to work.';

// Article settings
$helptxt['articleactive'] = 'This setting enables articles to be displayed on the portal.';
$helptxt['articleperpage'] = 'This sets the maximum amount of articles shown per page.';
$helptxt['articlelength'] = 'This setting allows a limit to be set on the amount of characters an article can display on the portal page. If the article exceeds this limit it will be shortened and have an ellipsis (...) link on the end, which allows the user to view the entire article.';
$helptxt['articleavatar'] = 'If this setting is enabled the authors avatar will be shown along with each article.';

// Blocks area
$helptxt['sp_BlocksArea'] = 'Blocks are boxes which can be displayed on the portal or inside the forum. This section enables existing blocks to be modified, and new ones to be created.';

// Block list
$helptxt['sp-blocksLeftList'] = 'These blocks are displayed on the left side of the portal and forum.';
$helptxt['sp-blocksTopList'] = 'These blocks are centered at the top of the portal and forum.';
$helptxt['sp-blocksBottomList'] = 'These blocks are centered at the bottom of the portal and forum.';
$helptxt['sp-blocksRightList'] = 'These blocks are displayed on the right side of the portal and forum.';
$helptxt['sp-blocksHeaderList'] = 'These blocks are displayed on the top of the portal and forum.';
$helptxt['sp-blocksFooterList'] = 'These blocks are displayed on the bottom of the portal and forum.';

// Add/Edit blocks
$helptxt['sp-blocksAdd'] = 'This area enables the selected block to be customised and configured.';
$helptxt['sp-blocksSelectType'] = 'This area enables blocks to be created for the portal page. Pre-built blocks or custom content blocks can be created easily by selecting the appropriate options.';
$helptxt['sp-blocksEdit'] = 'This area enables the selected block to be customised and configured.';
$helptxt['sp-blocksDisplayOptions'] = 'This area allows you to select on which pages block will appear.';
$helptxt['sp-blocksCustomDisplayOptions'] = 'Custom display options allows a more advanced control over where to display the block with its special syntax.<br /><br />
<strong>Special actions include:</strong><br /><br />
<strong>all:</strong> every page in forum.<br />
<strong>portal:</strong> portal page and it\'s sub-actions.<br />
<strong>forum:</strong> board index.<br />
<strong>sforum:</strong> all actions and boards, except portal.<br />
<strong>allaction:</strong> all actions.<br />
<strong>allboard:</strong> all boards.<br /><br />
<strong>Wavy (~)</strong><br />
This symbol acts as a wildcard, allowing you to include dynamic actions like ../index.php?issue=* or ../index.php?game=*. Used as ~action<br /><br />
<strong>Idkin (|)</strong><br />
Another wildcard sybmol which allows you to specify an exact value for a dynamic action like ../index.php?issue=1.0 or ../index.php?game=xyz. Should be used with wavy and after the action like; ~action|value<br /><br />
<strong>Negator (-)</strong><br />
This symbol is to exclude regular and dynamic actions. Should be used before the action name for regular actions and before the wavy for dynamic actions. Used as -action and -~action';
$helptxt['sp-blocksStyleOptions'] = 'These options allow you to specify CSS styling for each blocks.';

// Articles area
$helptxt['sp_ArticlesArea'] = 'Articles are topics (first post only) which are displayed on the portal. This section enables existing articles to be modified, and new ones to be created for the portal.';

// Add/Edit articles
$helptxt['sp-articlesAdd'] = 'This area allows you to add articles to categories from your boards.';
$helptxt['sp-articlesEdit'] = 'In this area you change the category or status of the articles.';
$helptxt['sp-articlesCategory'] = 'Select a category for this article.';
$helptxt['sp-articlesApproved'] = 'Approved articles will appear on the portal articles area.';
$helptxt['sp-articlesTopics'] = 'Select the topics to be displayed as articles on the portal.';
$helptxt['sp-articlesBoards'] = 'Select a board to search for topics.';

// Categories area
$helptxt['sp_CategoriesArea'] = 'Categories hold articles. This section enables existing categories to be modified, and new ones to be created for articles. To create an article there must be at least one category.';

// Add/Edit categories
$helptxt['sp-categoriesAdd'] = 'This section enables categories to be created for articles. To create articles there must be at least one category.';
$helptxt['sp-categoriesEdit'] = 'This section enables categories to be modified.';
$helptxt['sp-categoriesCategories'] = 'This page displays a list of the current article categories. To create articles there must be at least one category.';
$helptxt['sp-categoriesDelete'] = 'Deleting a category will either delete the articles in it, or move them to another category.';

// Pages area
$helptxt['sp_PagesArea'] = 'Pages are BBC, PHP or HTML code blocks that are shown on their own page within your forum. This section allows you to create, edit and configure your pages.';

// Shoutbox area
$helptxt['sp_ShoutboxArea'] = 'Shoutboxes need to be created in this section. This section allows shoutboxes to be created and configured. A shoutbox block will then need to be used to show the shoutbox that is created.';

// Add/Edit shoutboxes
$helptxt['sp-shoutboxesWarning'] = 'The warning message that you set here will be shown in the shoutbox, anyone using the shoutbox will see this message.';
$helptxt['sp-shoutboxesBBC'] = 'This setting allows you to choose the BBC that can be used in this shoutbox.<br /><br />Hold down the CTRL key to select or deselect a particular BBC. <br /><br />If you want to select a series of consecutive BBC, then click on the first BBC that you want to select, hold down the SHIFT key, then click on the last BBC that you want to select.';

$helptxt['sp_permissions'] = 'This option enables permissions to be used on blocks. The first three options are the simplest to use and understand.
<ul>
	<li><strong>Guests:</strong> Any user who is not registered or logged in <em>will</em> see this block. Logged-in users (including Administrators) <em>will not</em> see this block.</li>
	<li><strong>Members:</strong> Any user who is logged in (including Administrators) <em>will</em> see this block.</li>
	<li><strong>Everyone:</strong> All users, whether they are logged in or not, <em>will</em> see this block.</li>
	<li><strong>Custom:</strong> Select this to show the Custom Permissions area. In the Custom Permissions Settings, there are three options to choose for each group.
		<ul>
			<li><strong>A:</strong> Allowed, any user of this group <em>will</em> see this block.</li>
			<li><strong>X:</strong> Disallowed, any user of this group <em>will not</em> see this block by default. The user may see this block if they are part of a group with Allowed permissions.</li>
			<li><strong>D:</strong> Denied, any user of this group <em>will never</em> see this block. This overrides the Allowed setting for any group a user is part of, so <strong>be careful</strong> with this permission.</li>
		</ul>
	</li>
</ul>';

// Block parameters
$helptxt['sp_param_sp_latestMember_limit'] = 'How many members to display.';
$helptxt['sp_param_sp_boardStats_averages'] = 'Display average statistics.';
$helptxt['sp_param_sp_topPoster_limit'] = 'How many top posters to display.';
$helptxt['sp_param_sp_topPoster_type'] = 'Time period to show top posters from.';
$helptxt['sp_param_sp_recent_limit'] = 'How many recent posts or topics to display.';
$helptxt['sp_param_sp_recent_type'] = 'Display recent posts or topics.';
$helptxt['sp_param_sp_recentPosts_limit'] = 'How many recent posts to display.';
$helptxt['sp_param_sp_recentTopics_limit'] = 'How many recent topics to display.';
$helptxt['sp_param_sp_topTopics_type'] = 'Sort topics by replies or views.';
$helptxt['sp_param_sp_topTopics_limit'] = 'How many topics to display.';
$helptxt['sp_param_sp_topBoards_limit'] = 'How many boards to display.';
$helptxt['sp_param_sp_showPoll_topic'] = 'The ID of the topic containing the poll to be displayed.';
$helptxt['sp_param_sp_showPoll_type'] = 'Select the way polls should be displayed. Normal enables a specific poll to be called by the topic ID, Recent displays the most recently posted poll, and random displays a random poll.';
$helptxt['sp_param_sp_boardNews_board'] = 'The ID of the board where the topics come from. Leave empty to fetch topics from all visible boards.';
$helptxt['sp_param_sp_boardNews_limit'] = 'The maximum number of news items to be displayed.';
$helptxt['sp_param_sp_boardNews_start'] = 'The ID of a particular post to start with (otherwise the first result will be used).';
$helptxt['sp_param_sp_boardNews_length'] = 'If specified, posts exceeding this limit will be shortened and have an ellipsis (...), or a "Read More" link placed on the end.';
$helptxt['sp_param_sp_boardNews_avatar'] = 'Enables avatars to be displayed for the member who posted the board news.';
$helptxt['sp_param_sp_boardNews_per_page'] = 'How many posts to display per page. Leave empty to disable pagination.';
$helptxt['sp_param_sp_attachmentImage_limit'] = 'How many recently attached images to display.';
$helptxt['sp_param_sp_attachmentImage_direction'] = 'Attachment images can be aligned horizontally or vertically.';
$helptxt['sp_param_sp_attachmentRecent_limit'] = 'How many recent attachments to display.';
$helptxt['sp_param_sp_calendar_events'] = 'Enables events from the calendar to be displayed.';
$helptxt['sp_param_sp_calendar_birthdays'] = 'Displays birthdays from the calendar.';
$helptxt['sp_param_sp_calendar_holidays'] = 'Displays holidays from the calendar.';
$helptxt['sp_param_sp_calendarInformation_events'] = 'Enables events from the calendar to be displayed.';
$helptxt['sp_param_sp_calendarInformation_future'] = 'Allows you to choose the number of days into the future from which upcoming calendar events will be shown. This requires the ability to display events from the calendar. To display only events for today, use "0".';
$helptxt['sp_param_sp_calendarInformation_birthdays'] = 'Displays birthdays from the calendar.';
$helptxt['sp_param_sp_calendarInformation_holidays'] = 'Displays holidays from the calendar.';
$helptxt['sp_param_sp_rssFeed_url'] = 'Enter the full URL of the RSS feed.';
$helptxt['sp_param_sp_rssFeed_show_title'] = 'Show feed titles.';
$helptxt['sp_param_sp_rssFeed_show_content'] = 'Show feed contents.';
$helptxt['sp_param_sp_rssFeed_show_date'] = 'Show feed dates.';
$helptxt['sp_param_sp_rssFeed_strip_preserve'] = 'HTML tags to preserve in feed content separated by commas.';
$helptxt['sp_param_sp_rssFeed_count'] = 'How many items to display.';
$helptxt['sp_param_sp_rssFeed_limit'] = 'How many characters to display from the RSS feeds content.';
$helptxt['sp_param_sp_staff_lmod'] = 'Disables Local Moderators from being listed.';
$helptxt['sp_param_sp_articles_category'] = 'The category to display articles from.';
$helptxt['sp_param_sp_articles_limit'] = 'How many articles to display.';
$helptxt['sp_param_sp_articles_type'] = 'Displays random articles, or the latest articles.';
$helptxt['sp_param_sp_articles_image'] = 'Enables a category image, avatar, or nothing to be displayed on the article.';
$helptxt['sp_param_sp_gallery_limit'] = 'How many items to display.';
$helptxt['sp_param_sp_gallery_type'] = 'Displays random or the latest gallery items.';
$helptxt['sp_param_sp_gallery_direction'] = 'Gallery images can be aligned horizontally or vertically.';
$helptxt['sp_param_sp_arcade_limit'] = 'How many items to display.';
$helptxt['sp_param_sp_arcade_type'] = 'Displays the arcades most played games, best players, or longest champions.';
$helptxt['sp_param_sp_shop_style'] = 'Displays the richest members or shop items.';
$helptxt['sp_param_sp_shop_limit'] = 'How many items to display.';
$helptxt['sp_param_sp_shop_type'] = 'Displays the members total money, pocketed money, or money in the bank.';
$helptxt['sp_param_sp_shop_sort'] = 'Displays random or recently added items.';
$helptxt['sp_param_sp_blog_limit'] = 'How many items to display.';
$helptxt['sp_param_sp_blog_type'] = 'Displays articles or blogs.';
$helptxt['sp_param_sp_blog_sort'] = 'Displays blogs randomly or by the latest blogs updated.';
$helptxt['sp_param_sp_html_content'] = 'Enter the custom HTML content in this box.';
$helptxt['sp_param_sp_bbc_content'] = 'Enter the custom BBC content in this box.';
$helptxt['sp_param_sp_php_content'] = 'Enter the custom PHP content in this box.';

?>