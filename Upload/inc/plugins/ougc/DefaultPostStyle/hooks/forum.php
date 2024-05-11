<?php

/***************************************************************************
 *
 *    OUGC Default Post Style plugin (/inc/plugins/ougc/DefaultPostStyle/forum.php)
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

namespace ougc\DefaultPostStyle\Hooks\Forum;

use MyBB;

use UserDataHandler;

use function ougc\DefaultPostStyle\Core\deleteUserTemplate;
use function ougc\DefaultPostStyle\Core\getSetting;
use function ougc\DefaultPostStyle\Core\getUserTemplate;
use function ougc\DefaultPostStyle\Core\getUserTemplates;
use function ougc\DefaultPostStyle\Core\insertUserTemplate;
use function ougc\DefaultPostStyle\Core\loadLanguage;
use function ougc\DefaultPostStyle\Core\getTemplate;
use function ougc\DefaultPostStyle\Core\addPoints;
use function ougc\DefaultPostStyle\Core\parseTemplate;
use function ougc\DefaultPostStyle\Core\updateUserDefaultTemplate;
use function ougc\DefaultPostStyle\Core\updateUserTemplate;
use function ougc\DefaultPostStyle\Core\urlHandlerSet;
use function ougc\DefaultPostStyle\Core\urlHandlerBuild;

function global_start()
{
    global $templatelist, $mybb;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= ',';

    if (defined('THIS_SCRIPT')) {
        if (in_array(THIS_SCRIPT, ['usercp.php', 'usercp2.php', 'private.php'])) {
            $templatelist .= '';
        }
    }

    // we convert legacy template to new system
    if (!empty($mybb->user['uid']) && !empty($mybb->user['ougc_defaultpoststyle'])) {
        global $db;

        $userID = (int)$mybb->user['uid'];

        $templateDbData = [
            'userID' => $userID,
            'templateName' => 'Legacy Template',
            //'templateStyle' => $mybb->get_input('templateStyle'),
            'templateContents' => $mybb->user['ougc_defaultpoststyle'],
            'isEnabled' => 1,
            'isDefault' => 1,
            'updateStamp' => TIME_NOW
        ];

        insertUserTemplate($templateDbData);

        $templateID = (int)$db->insert_id();

        updateUserDefaultTemplate($userID, $templateID);
    }
}

function usercp_menu_built(): bool
{
    global $mybb;

    if (!is_member(getSetting('groups'))) {
        return false;
    }

    global $lang, $templates, $usercpnav;

    loadLanguage();

    $pageUrl = urlHandlerBuild(['action' => getSetting('pageAction')]);

    $navigationItem = eval(getTemplate('menu'));

    $usercpnav = str_replace('<!--OUGC_DEFAULTPOSTSTYLE-->', $navigationItem, $usercpnav);

    return true;
}

function usercp_start(bool $isModeratorPanel = false)
{
    global $mybb, $lang;

    $pageAction = getSetting('pageAction');

    if ($isModeratorPanel) {
        if (!is_member(getSetting('moderatorGroups'))) {
            return false;
        }

        urlHandlerSet('modcp.php');
    } elseif (!is_member(getSetting('groups'))) {
        error_no_permission();
    }

    urlHandlerSet(urlHandlerBuild(['action' => $pageAction]));

    $pageUrl = urlHandlerBuild();

    if ($isModeratorPanel) {
        global $modcp_nav;

        loadLanguage();

        $navigationItem = eval(getTemplate('moderatorNav'));

        $modcp_nav = str_replace('<!--OUGC_DEFAULTPOSTSTYLE-->', $navigationItem, $modcp_nav);
    }

    if ($mybb->get_input('action') !== $pageAction) {
        return false;
    }

    global $plugins, $lang, $headerinclude, $header, $footer, $usercpnav, $theme;

    loadLanguage();

    $moderatorUserPanel = $userTable = '';

    if ($isModeratorPanel) {
        add_breadcrumb($lang->nav_modcp, "{$mybb->settings['bburl']}/{$pageUrl}");

        add_breadcrumb($lang->ougcDefaultPostStyleModeratorControlTitle);

        $userID = $mybb->get_input('userID', \MyBB::INPUT_INT);

        if (isset($mybb->input['userName'])) {
            $userData = get_user_by_username($mybb->get_input('userName'));

            if (!empty($userData['uid'])) {
                $userID = (int)$userData['uid'];
            }
        }

        $panelNavigation = $modcp_nav;

        if ($userID) {
            urlHandlerSet(urlHandlerBuild(['userID' => $userID]));

            $pageUrl = urlHandlerBuild();
        }
    } else {
        add_breadcrumb($lang->nav_usercp, "{$mybb->settings['bburl']}/{$pageUrl}");

        add_breadcrumb($lang->ougcDefaultPostStyleUserControlTitle);

        $userID = (int)$mybb->user['uid'];

        $panelNavigation = $usercpnav;
    }

    if ($mybb->get_input('deleteTemplate', MyBB::INPUT_INT)) {
        $templateID = $mybb->get_input('deleteTemplate', MyBB::INPUT_INT);

        $templateData = getUserTemplate($templateID, $userID);

        if (empty($templateData['templateID'])) {
            error_no_permission();
        }

        deleteUserTemplate($templateID);

        redirect("{$mybb->settings['bburl']}/{$pageUrl}", $lang->ougcDefaultPostStyleUserControlPanelRedirectDelete);
    }

    if ($mybb->get_input('createTemplate', MyBB::INPUT_INT) === 1 || $mybb->get_input(
            'editTemplate',
            MyBB::INPUT_INT
        )) {
        $mybb->input['createTemplate'] = $mybb->get_input('createTemplate', MyBB::INPUT_INT);

        $createTemplatePage = $mybb->input['createTemplate'] === 1;

        $previewTemplate = $templateName = $templateStyle = $templateContents = $templateStyleRow = $smilieInserter = $codeButtons = $newpointsRow = '';

        $deductiblePoints = (float)getSetting('newpoints');

        $errorMessages = [];

        if ($createTemplatePage) {
            $templateID = 0;
        } else {
            $templateID = $mybb->get_input('editTemplate', MyBB::INPUT_INT);

            $templateData = getUserTemplate($templateID, $userID);

            if (empty($templateData['templateID'])) {
                error_no_permission();
            }
        }

        if ($mybb->request_method === 'post') {
            verify_post_check($mybb->input['my_post_key']);

            if (my_strlen($mybb->get_input('templateName')) < 1 || my_strlen($mybb->get_input('templateName')) > 200) {
                $errorMessages[] = $lang->ougcDefaultPostStyleUserControlPanelErrorsTemplateName;
            }

            if (my_strpos($mybb->get_input('templateContents'), getSetting('string')) === false) {
                $errorMessages[] = $lang->sprintf(
                    $lang->ougcDefaultPostStyleUserControlPanelErrorsTemplateContents,
                    getSetting('string')
                );
            }

            $templateContentsLength = my_strlen($mybb->get_input('templateContents'));

            if ($templateContentsLength > getSetting('limit')) {
                $errorMessages[] = $lang->sprintf(
                    $lang->ougcDefaultPostStyleUserControlPanelErrorsTemplateContentsLength,
                    getSetting('limit'),
                    $templateContentsLength - getSetting('limit')
                );
            }

            if ($deductiblePoints && $deductiblePoints > (float)$mybb->user['newpoints']) {
                $errorMessages[] = $lang->sprintf(
                    $lang->ougcDefaultPostStyleUserControlPanelErrorsNewpoints,
                    newpoints_format_points($deductiblePoints)
                );
            }

            if (isset($mybb->input['preview'])) {
                $parsedTemplate = parseTemplate(
                    $mybb->get_input('templateContents')
                );

                $previewDescription = $lang->sprintf(
                    $lang->ougcDefaultPostStyleUserControlPanelFormPreviewDescription,
                    getSetting('string')
                );

                $previewTemplate = eval(getTemplate('formPreview'));
            } elseif (empty($errorMessages)) {
                $templateDbData = [
                    'userID' => (int)$userID,
                    'templateName' => $mybb->get_input('templateName'),
                    //'templateStyle' => $mybb->get_input('templateStyle'),
                    'templateContents' => $mybb->get_input('templateContents'),
                    'isEnabled' => $mybb->get_input('isEnabled', MyBB::INPUT_INT),
                    'isDefault' => $mybb->get_input('isDefault', MyBB::INPUT_INT),
                    'updateStamp' => TIME_NOW
                ];

                if ($createTemplatePage) {
                    if (!$deductiblePoints || addPoints($userID, -$deductiblePoints, $templateID) === true) {
                        insertUserTemplate($templateDbData);

                        global $db;

                        $templateID = (int)$db->insert_id();
                    }

                    $redirectMessage = $lang->ougcDefaultPostStyleUserControlPanelRedirectCreate;
                } else {
                    unset($templateDbData['userID']);

                    if (!$deductiblePoints || addPoints(
                            $userID,
                            -$deductiblePoints,
                            $templateID,
                            'updateTemplate'
                        ) === true) {
                        updateUserTemplate($templateDbData, ["templateID='{$templateID}'"]);
                    }

                    if (!empty($templateData['isDefault']) && empty($templateDbData['isDefault'])) {
                        updateUserDefaultTemplate($userID, 0);
                    }

                    $redirectMessage = $lang->ougcDefaultPostStyleUserControlPanelRedirectUpdate;
                }

                if (!empty($templateDbData['isDefault'])) {
                    updateUserDefaultTemplate($userID, $templateID);
                }

                redirect("{$mybb->settings['bburl']}/{$pageUrl}", $redirectMessage);
            }
        } elseif (!$createTemplatePage) {
            foreach (
                [
                    'templateID',
                    'templateName',
                    'templateStyle',
                    'templateContents',
                    'isEnabled',
                    'isDefault',
                ] as $inputKey
            ) {
                if (!isset($mybb->input[$inputKey])) {
                    $mybb->input[$inputKey] = $templateData[$inputKey];
                }
            }
        }

        if ($errorMessages) {
            $errorMessages = inline_error($errorMessages);
        } else {
            $errorMessages = '';
        }

        if ($createTemplatePage) {
            $formTitle = $lang->ougcDefaultPostStyleUserControlPanelFormTitleCreate;

            $formDescription = $lang->ougcDefaultPostStyleUserControlPanelFormDescriptionCreate;
        } else {
            $formTitle = $lang->ougcDefaultPostStyleUserControlPanelFormTitleEdit;

            $formDescription = $lang->ougcDefaultPostStyleUserControlPanelFormDescriptionEdit;
        }

        foreach (
            [
                'templateName',
                'templateStyle',
                'templateContents',
            ] as $inputKey
        ) {
            if (isset($mybb->input[$inputKey])) {
                ${$inputKey} = htmlspecialchars_uni($mybb->get_input($inputKey));
            }
        }

        if (!$templateContents) {
            $templateContents = getSetting('string');
        }

        if (is_member(getSetting('allowedGroupsStyleCode'))) {
            $templateStyleRow = eval(getTemplate('formRowStyleCode'));
        }

        if (!empty($mybb->settings['smilieinserter'])) {
            $smilieInserter = build_clickable_smilies();
        }

        if (!empty($mybb->settings['bbcodeinserter'])) {
            $codeButtons = build_mycode_inserter('templateContents');
        }

        $checkElementIsEnabled = $checkElementIsDefault = $checkElementType = '';

        if ($mybb->get_input('isEnabled', MyBB::INPUT_INT) === 1) {
            $checkElementIsEnabled = 'checked="checked"';
        }

        if ($mybb->get_input('isDefault', MyBB::INPUT_INT) === 1) {
            $checkElementIsDefault = 'checked="checked"';
        }

        if (getSetting('newpoints')) {
            $newpointsDescription = $lang->sprintf(
                $lang->ougcDefaultPostStyleUserControlPanelFormNewpoints,
                newpoints_format_points(getSetting('newpoints'))
            );

            $newpointsRow = eval(getTemplate('formRowNewpoints'));
        }

        $pageContents = eval(getTemplate('form'));

        output_page($pageContents);

        exit;
    }

    $templateList = '';

    $alternativeBackground = alt_trow(true);

    foreach (getUserTemplates($userID) as $templateID => $templateData) {
        $templateName = htmlspecialchars_uni($templateData['templateName']);

        $templateIsEnabled = $lang->yes;

        if (empty($templateData['isEnabled'])) {
            $templateIsEnabled = $lang->no;
        }

        $templateIsDefault = $lang->yes;

        if (empty($templateData['isDefault'])) {
            $templateIsDefault = $lang->no;
        }

        $templateUpdateDate = my_date('normal', $templateData['UpdateDate']);

        $editLink = urlHandlerBuild(['editTemplate' => $templateID]);

        $deleteLink = urlHandlerBuild(['deleteTemplate' => $templateID]);

        $templateList .= eval(getTemplate('rowsItem'));

        $alternativeBackground = alt_trow();
    }

    if (!$templateList) {
        $templateList = eval(getTemplate('rowsEmpty'));
    }

    $createLink = urlHandlerBuild(['createTemplate' => 1]);

    if ($isModeratorPanel) {
        if (!$userID) {
            $moderatorUserPanel = eval(getTemplate('moderatorUserForm'));
        } else {
            $userData = get_user($userID);

            $userName = htmlspecialchars_uni($userData['username']);

            $userNameFormatted = format_name($userData['username'], $userData['usergroup'], $userData['displaygroup']);

            $profileLinkFormatted = build_profile_link($userNameFormatted, $userID);

            $moderatorUserPanel = eval(getTemplate('moderatorUserMessage'));
        }
    }

    if (!$isModeratorPanel || $userID) {
        $userTable = eval(getTemplate('userTable'));
    }

    $pageContents = eval(getTemplate());

    output_page($pageContents);

    exit;
}

function modcp_start()
{
    usercp_start(true);
}

function datahandler_post_validate_thread(\PostDataHandler $postDataHandler): \PostDataHandler
{
    return datahandler_post_validate_post($postDataHandler);
}

function datahandler_post_validate_post(\PostDataHandler $postDataHandler): \PostDataHandler
{
    global $mybb;
    global $ougcDefaultPostStyleSelectedTemplateID;

    $forumID = (int)$postDataHandler->data['fid'];

    if (\ougc\DefaultPostStyle\Core\isIgnoredForum($forumID) || !is_member(
            getSetting('groups'),
            get_user($postDataHandler->data['uid'])
        )) {
        return $postDataHandler;
    }

    if (!isset($mybb->input['ougcDefaultPostStyleTemplateID'])) {
        return $postDataHandler;
    }

    $selectedTemplateID = $ougcDefaultPostStyleSelectedTemplateID = $mybb->get_input(
        'ougcDefaultPostStyleTemplateID',
        \MyBB::INPUT_INT
    );

    $userID = (int)$postDataHandler->data['uid'];

    $templateData = getUserTemplate($selectedTemplateID, $userID);

    if (!$userID || empty($templateData['isEnabled'])) {
        return $postDataHandler;
    }

    $ougcDefaultPostStyleSelectedTemplateID = (int)$templateData['templateID'];

    return $postDataHandler;
}

function datahandler_post_insert_thread_post(\PostDataHandler $postDataHandler): \PostDataHandler
{
    return datahandler_post_insert_post($postDataHandler);
}

function datahandler_post_insert_post(\PostDataHandler $postDataHandler): \PostDataHandler
{
    global $ougcDefaultPostStyleSelectedTemplateID;

    if (isset($ougcDefaultPostStyleSelectedTemplateID)) {
        if (isset($postDataHandler->post_update_data)) {
            $postDataHandler->post_update_data['ougcDefaultPostStyleTemplateID'] = (int)$ougcDefaultPostStyleSelectedTemplateID;
        }

        if (isset($postDataHandler->post_insert_data)) {
            $postDataHandler->post_insert_data['ougcDefaultPostStyleTemplateID'] = (int)$ougcDefaultPostStyleSelectedTemplateID;
        }
    }

    return $postDataHandler;
}

function datahandler_post_update(\PostDataHandler $postDataHandler): \PostDataHandler
{
    return datahandler_post_insert_post($postDataHandler);
}

function postbit_prev(array $postData): array
{
    global $mybb;

    if (isset($mybb->input['ougcDefaultPostStyleTemplateID'])) {
        $postData['ougcDefaultPostStyleTemplateID'] = $mybb->get_input(
            'ougcDefaultPostStyleTemplateID',
            \MyBB::INPUT_INT
        );
    }

    return postbit($postData);
}

function postbit_pm(array $postData): array
{
    return postbit($postData);
}

function postbit_announcement(array $postData): array
{
    return postbit($postData);
}

function postbit(array $postData): array
{
    $forumID = (int)$postData['fid'];

    if (\ougc\DefaultPostStyle\Core\isIgnoredForum($forumID) || !is_member(
            getSetting('groups'),
            get_user($postData['uid'])
        )) {
        return $postData;
    }

    if ($postData['pid'] == 304) {
        _dump($postData);
    }

    if (empty($postData['ougcDefaultPostStyleTemplateID']) && getSetting('fallbackToDefault')) {
        $postData['ougcDefaultPostStyleTemplateID'] = (int)$postData['ougcDefaultPostStyleDefaultTemplateID'];
    }

    if (!empty($postData['ougcDefaultPostStyleTemplateID'])) {
        $userID = (int)$postData['uid'];

        $templateID = (int)$postData['ougcDefaultPostStyleTemplateID'];

        $templateData = getUserTemplate($templateID, $userID);

        if ($userID && !empty($templateData['templateID'])) {
            $postData['message'] = parseTemplate($templateData['templateContents'], $postData['message']);
        }
    }

    return $postData;
}

function newthread_end()
{
    newreply_end();
}

function newreply_end(bool $editPost = false)
{
    global $mybb, $lang, $fid;
    global $ougcDefaultPostStyleSelectRow;

    $ougcDefaultPostStyleSelectRow = '';

    $forumID = (int)$fid;

    if (\ougc\DefaultPostStyle\Core\isIgnoredForum($forumID) || !is_member(getSetting('groups'))) {
        return false;
    }

    $userID = (int)$mybb->user['uid'];

    $selectOptions = '';

    $defaultTemplateID = (int)$mybb->user['ougcDefaultPostStyleDefaultTemplateID'];

    if ($mybb->get_input('ougcDefaultPostStyleTemplateID', \MyBB::INPUT_INT)) {
        $defaultTemplateID = $mybb->get_input('ougcDefaultPostStyleTemplateID', \MyBB::INPUT_INT);
    } elseif ($editPost) {
        global $post;

        $defaultTemplateID = (int)$post['ougcDefaultPostStyleTemplateID'];
    }

    foreach (getUserTemplates($userID, ["isEnabled='1'"]) as $templateID => $templateData) {
        $selectElement = '';

        if ($defaultTemplateID === $templateID) {
            $selectElement = ' selected="selected"';
        }

        $templateName = htmlspecialchars_uni($templateData['templateName']);

        $selectOptions .= eval(getTemplate('selectOption'));
    }

    if ($selectOptions) {
        loadLanguage();

        $ougcDefaultPostStyleSelectButton = '';

        if (getSetting('enableInsertButton')) {
            $ougcDefaultPostStyleSelectButton = eval(getTemplate('selectButton'));
        }

        $ougcDefaultPostStyleSelect = eval(getTemplate('select'));

        $ougcDefaultPostStyleSelectRow = eval(getTemplate('newReply'));
    }
}

function editpost_end()
{
    newreply_end(true);
}

function xmlhttp()
{
    global $mybb;

    if ($mybb->get_input('action') !== 'ougcDefaultPostStyle') {
        return;
    }

    $userID = (int)$mybb->user['uid'];

    $templateID = $mybb->get_input('templateID', MyBB::INPUT_INT);

    $templateData = getUserTemplate($templateID, $userID);

    if (!$userID || empty($templateData['templateID'])) {
        return;
    }

    global $lang;

    if (!empty($lang->settings['charset'])) {
        $charset = $lang->settings['charset'];
    } else {
        $charset = 'UTF-8';
    }

    header("Content-type: application/json; charset={$charset}");

    echo json_encode([
        'success' => 1,
        'templateContents' => $templateData['templateContents'],
    ]);

    exit;
}