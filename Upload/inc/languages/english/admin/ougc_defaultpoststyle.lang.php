<?php

/***************************************************************************
 *
 *   OUGC Default Post Style plugin (/inc/languages/english/admin/ougc_defaultpoststyle.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012-2014 Omar Gonzalez
 *   
 *   Website: http://omarg.me
 *
 *   Allow users to set a default style for their posts/messages.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Plugin API
$l['setting_group_ougc_defaultpoststyle'] = 'OUGC Default Post Style';
$l['setting_group_ougc_defaultpoststyle_desc'] = 'Allow users to set a default style for their posts/messages.';

// Settings
$l['setting_ougc_defaultpoststyle_limit'] = 'Character Limit';
$l['setting_ougc_defaultpoststyle_limit_desc'] = 'Maximum number of characters users may use for their default post style.';
$l['setting_ougc_defaultpoststyle_string'] = 'Replacement String';
$l['setting_ougc_defaultpoststyle_string_desc'] = 'String to be replaced within the post style to define the post\'s content.';
$l['setting_ougc_defaultpoststyle_groups'] = 'Allowed Groups';
$l['setting_ougc_defaultpoststyle_groups_desc'] = 'Allowed groups to use this feature.';
$l['setting_ougc_defaultpoststyle_forums'] = 'Ignored Forums';
$l['setting_ougc_defaultpoststyle_forums_desc'] = 'Forums to exclude from this feature.';
$l['setting_ougc_defaultpoststyle_newpoints'] = 'Require Newpoints Payment';
$l['setting_ougc_defaultpoststyle_newpoints_desc'] = 'Insert the amount of points users need to pay each time they update their default post style.';

// PluginLibrary
$l['ougc_defaultpoststyle_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
$l['ougc_defaultpoststyle_pl_old'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later, whereas your current version is {3}.';