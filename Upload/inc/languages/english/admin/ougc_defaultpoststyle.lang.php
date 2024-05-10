<?php

/***************************************************************************
 *
 *   OUGC Default Post Style plugin (/inc/languages/english/admin/ougc_defaultpoststyle.php)
 *   Author: Omar Gonzalez
 *   Copyright: © 2012-2014 Omar Gonzalez
 *
 *   Website: http://omarg.me
 *
 *   Allow users to set a default style for their posts.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/
$l = [
    'ougcDefaultPostStyle' => 'OUGC Default Post Style',
    'ougcDefaultPostStyleDescription' => 'Allow users to set a default style for their posts.',
];

$l['setting_group_ougc_defaultpoststyle'] = 'Default Post Style (Templates)';
$l['setting_group_ougc_defaultpoststyle_desc'] = 'Allow users to create custom post templates for their posts.';
$l['setting_ougc_defaultpoststyle_groups'] = 'Allowed Groups';
$l['setting_ougc_defaultpoststyle_groups_desc'] = 'Select which groups are allowed to create custom post templates.';
$l['setting_ougc_defaultpoststyle_moderatorGroups'] = 'Moderator Groups';
$l['setting_ougc_defaultpoststyle_moderatorGroups_desc'] = 'Select which groups are allowed to moderate user custom post templates.';
$l['setting_ougc_defaultpoststyle_forums'] = 'Ignored Forums';
$l['setting_ougc_defaultpoststyle_forums_desc'] = 'Select which forums are ignored for using custom post templates.';
$l['setting_ougc_defaultpoststyle_allowedGroupsStyleCode'] = 'Allowed CSS Style';
$l['setting_ougc_defaultpoststyle_allowedGroupsStyleCode_desc'] = 'Select which groups are allowed to use custom CSS in their post templates.';
$l['setting_ougc_defaultpoststyle_parserOptions'] = 'Parser Options';
$l['setting_ougc_defaultpoststyle_parserOptions_desc'] = 'Select how content is going to be parsed in templates.';
$l['setting_ougc_defaultpoststyle_parserOptions_allowHTML'] = 'Allow HTML';
$l['setting_ougc_defaultpoststyle_parserOptions_allowMyCode'] = 'Allow MyCode';
$l['setting_ougc_defaultpoststyle_parserOptions_allowSmilies'] = 'Allow Smilies';
$l['setting_ougc_defaultpoststyle_parserOptions_allowImageCode'] = 'Allow Image MyCode';
$l['setting_ougc_defaultpoststyle_parserOptions_allowVideoCode'] = 'Allow Video MyCode';
$l['setting_ougc_defaultpoststyle_parserOptions_allowMeCode'] = 'Allow Me MyCode';
$l['setting_ougc_defaultpoststyle_parserOptions_filterBadWords'] = 'Filter Bad Words';
$l['setting_ougc_defaultpoststyle_limit'] = 'Character Limit';
$l['setting_ougc_defaultpoststyle_limit_desc'] = 'Maximum number of characters users may use for their custom post templates.';
$l['setting_ougc_defaultpoststyle_string'] = 'Replacement String';
$l['setting_ougc_defaultpoststyle_string_desc'] = "String to be replaced within the post style to define the post's content.";
$l['setting_ougc_defaultpoststyle_newpoints'] = 'Newpoints Payment';
$l['setting_ougc_defaultpoststyle_newpoints_desc'] = 'Select the amount of points users HAVE to pay each time they create or update their custom post templates.';
$l['setting_ougc_defaultpoststyle_fallbackToDefault'] = 'Fallback to Default Template';
$l['setting_ougc_defaultpoststyle_fallbackToDefault_desc'] = 'If you enable this, the user default template will be used for posts when no post template is assigned to them.';
$l['setting_ougc_defaultpoststyle_enableInsertButton'] = 'Enable Insert Button';
$l['setting_ougc_defaultpoststyle_enableInsertButton_desc'] = 'Show an insert button along the select form to allow insertion of templates directly into messages.';
$l['setting_ougc_defaultpoststyle_pageAction'] = 'Page Action';
$l['setting_ougc_defaultpoststyle_pageAction_desc'] = 'Select the User Control Panel page action to use for this plugin. Default: <code>defaultpoststyle</code>';

$l['ougc_defaultpoststyle_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';