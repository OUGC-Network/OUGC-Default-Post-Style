<?php

/***************************************************************************
 *
 *    OUGC Default Post Style plugin (/inc/plugins/ougc/DefaultPostStyle/core.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012-2014 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow users to set a default style for their posts.
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

declare(strict_types=1);

namespace ougc\DefaultPostStyle\Core;

use PostDataHandler;

use postParser;

use function ougc\DefaultPostStyle\Admin\_info;

use const ougc\DefaultPostStyle\Core\SETTINGS;
use const ougc\DefaultPostStyle\Core\DEBUG;
use const ougc\DefaultPostStyle\ROOT;

const URL = 'usercp.php';

function loadLanguage(): bool
{
    global $lang;

    isset($lang->ougcDefaultPostStyle) || $lang->load('ougc_defaultpoststyle');

    return true;
}

function loadPluginLibrary(): bool
{
    global $PL, $lang;

    loadLanguage();

    if ($fileExists = file_exists(PLUGINLIBRARY)) {
        global $PL;

        $PL or require_once PLUGINLIBRARY;
    }

    $_info = _info();

    if (!$fileExists || $PL->version < $_info['pl']['version']) {
        flash_message(
            $lang->sprintf($lang->ougc_defaultpoststyle_pl_required, $_info['pl']['url'], $_info['pl']['version']),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function addHooks(string $namespace): bool
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, '', 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

            if (is_numeric(substr($hookName, -2))) {
                $hookName = substr($hookName, 0, -2);
            } else {
                $priority = 10;
            }

            $plugins->add_hook($hookName, $callable, $priority);
        }
    }

    return true;
}

function urlHandler(string $newUrl = ''): string
{
    static $setUrl = URL;

    if (($newUrl = trim($newUrl))) {
        $setUrl = $newUrl;
    }

    return $setUrl;
}

function urlHandlerSet(string $newUrl): string
{
    return urlHandler($newUrl);
}

function urlHandlerGet(): string
{
    return urlHandler();
}

function urlHandlerBuild(array $urlAppend = [], bool $fetchImportUrl = false, bool $encode = true): string
{
    global $PL;

    if (!is_object($PL)) {
        $PL or require_once PLUGINLIBRARY;
    }

    if ($fetchImportUrl === false) {
        if ($urlAppend && !is_array($urlAppend)) {
            $urlAppend = explode('=', $urlAppend);
            $urlAppend = [$urlAppend[0] => $urlAppend[1]];
        }
    }

    return $PL->url_append(urlHandlerGet(), $urlAppend, '&amp;', $encode);
}

function getSetting(string $settingKey = '')
{
    global $mybb;

    return isset(SETTINGS[$settingKey]) ? SETTINGS[$settingKey] : (
    isset($mybb->settings['ougc_defaultpoststyle_' . $settingKey]) ? $mybb->settings['ougc_defaultpoststyle_' . $settingKey] : false
    );
}

function getTemplateName(string $templateName = ''): string
{
    $templatePrefix = '';

    if ($templateName) {
        $templatePrefix = '_';
    }

    return "ougcdefaultpoststyle{$templatePrefix}{$templateName}";
}

function getTemplate(string $templateName = '', bool $enableHTMLComments = true): string
{
    global $templates;

    if (DEBUG) {
        $filePath = ROOT . "/templates/{$templateName}.html";

        $templateContents = file_get_contents($filePath);

        $templates->cache[getTemplateName($templateName)] = $templateContents;
    } elseif (my_strpos($templateName, '/') !== false) {
        $templateName = substr($templateName, strpos($templateName, '/') + 1);
    }

    return $templates->render(getTemplateName($templateName), true, $enableHTMLComments);
}

function insertUserTemplate(array $insertData, bool $updateTemplate = false, array $updateClauses = []): bool
{
    global $db;

    $cleanData = [];

    foreach (['userID', 'isEnabled', 'isDefault', 'updateStamp'] as $fieldKey) {
        if (isset($insertData[$fieldKey])) {
            $cleanData[$fieldKey] = (int)$insertData[$fieldKey];
        }
    }

    foreach (['templateName', 'templateStyle', 'templateContents'] as $fieldKey) {
        if (isset($insertData[$fieldKey])) {
            $cleanData[$fieldKey] = $db->escape_string($insertData[$fieldKey]);
        }
    }

    if ($updateTemplate) {
        $updateClauses = implode(' AND ', $updateClauses);

        $db->update_query('ougcDefaultPostStyleTemplates', $cleanData, $updateClauses);
    } else {
        $db->insert_query('ougcDefaultPostStyleTemplates', $cleanData);
    }

    return true;
}

function updateUserTemplate(array $updateData, array $updateClauses): bool
{
    return insertUserTemplate($updateData, true, $updateClauses);
}

function deleteUserTemplate(int $templateID): bool
{
    global $db;

    $db->delete_query('ougcDefaultPostStyleTemplates', "templateID='{$templateID}'");

    return true;
}

function getUserTemplate(int $templateID, int $userID = 0): array
{
    global $db;

    $whereClauses = ["templateID='{$templateID}'"];

    if ($userID) {
        $whereClauses[] = "userID='{$userID}'";
    }

    $dbQuery = $db->simple_select('ougcDefaultPostStyleTemplates', '*', implode(' AND ', $whereClauses));

    if ($db->num_rows($dbQuery)) {
        return (array)$db->fetch_array($dbQuery);
    }

    return [];
}

function getUserTemplates(int $userID, array $whereClauses = []): array
{
    global $db;

    $whereClauses[] = "userID='{$userID}'";

    $dbQuery = $db->simple_select('ougcDefaultPostStyleTemplates', '*', implode(' AND ', $whereClauses));

    $templateObjects = [];

    if ($db->num_rows($dbQuery)) {
        while ($templateData = $db->fetch_array($dbQuery)) {
            $templateObjects[(int)$templateData['templateID']] = $templateData;
        }
    }

    return $templateObjects;
}

function updateUserDefaultTemplate(int $userID, int $templateID): bool
{
    global $db;

    updateUserTemplate(['isDefault' => 0], ["templateID!='{$templateID}'", "userID='{$userID}'"]);

    $db->update_query('users', ['ougcDefaultPostStyleDefaultTemplateID' => $templateID], "uid='{$userID}'");

    return true;
}

function addPoints(int $userID, float $totalPoints, int $templateID, string $processAction = 'createTemplate'): bool
{
    $userData = get_user($userID);

    if (empty($userData['uid'])) {
        return false;
    }

    newpoints_addpoints($userID, $totalPoints, 1, 1, false, true);

    newpoints_log(
        'ougc_defaultpoststyle',
        "{$processAction}={$templateID}",
        $userData['username'],
        $userID
    );

    return true;
}

function parseTemplate(string $templateContents, string $messageContent = ''): string
{
    global $parser;

    if (!($parser instanceof postParser)) {
        require_once MYBB_ROOT . '/inc/class_parser.php';

        $parser = new postParser();
    }

    global $mybb;

    $parserOptionsSettings = array_flip(explode(',', getSetting('parserOptions')));

    $parserOptions = [
        'allow_html' => isset($parserOptionsSettings['allowHTML']),
        'allow_mycode' => isset($parserOptionsSettings['allowMyCode']),
        'allow_smilies' => isset($parserOptionsSettings['allowSmilies']),
        'allow_imgcode' => isset($parserOptionsSettings['allowImageCode']),
        'allow_videocode' => isset($parserOptionsSettings['allowVideoCode']),
        'me_username' => isset($parserOptionsSettings['filterBadWords']) ? $mybb->user['username'] : '',
        'filter_badwords' => isset($parserOptionsSettings['filterBadWords']),
    ];

    if ($messageContent === '') {
        $messageContent = getSetting('string');
    }

    return str_replace(
        getSetting('string'),
        $messageContent,
        $parser->parse_message($templateContents, $parserOptions)
    );
}

function isIgnoredForum(int $forumID): bool
{
    if ((int)getSetting('forums') === -1 || is_member(
            getSetting('forums'),
            ['usergroup' => $forumID, 'additionalgroups' => '']
        )) {
        return true;
    }

    return false;
}