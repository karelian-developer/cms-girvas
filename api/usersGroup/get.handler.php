<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;

if ($CMSCore->urlp->getPath(3) == 'permissions') {
  $usersGroup = is_numeric($CMSCore->urlp->getPath(2) ? new UserGroup($CMSCore, $CMSCore->urlp->getPath(2)) : UserGroup::getByName($CMSCore, $CMSCore->urlp->getPath(2)));

  if (!is_null($usersGroup)) {
    $usersGroup->initData(['metadata', 'permissions']);

    $handlerOutputData['usersGroup'] = [];
    $handlerOutputData['usersGroup']['permissions'] = [];
    $handlerOutputData['usersGroup']['permissions']['admin_panel_auth'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_PANEL_AUTH);
    $handlerOutputData['usersGroup']['permissions']['admin_users_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_users_groups_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_modules_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_templates_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_feeds_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_FEEDS_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_forms_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_FORMS_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_content_blocks_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_CONTENT_BLOCKS_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_settings_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['admin_viewing_logs'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_VIEWING_LOGS);
    $handlerOutputData['usersGroup']['permissions']['moder_users_ban'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_MODER_USERS_BAN);
    $handlerOutputData['usersGroup']['permissions']['moder_entries_comments_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['moder_users_warns'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_MODER_USERS_WARNS);
    $handlerOutputData['usersGroup']['permissions']['editor_media_files_management'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT);
    $handlerOutputData['usersGroup']['permissions']['editor_entries_edit'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT);
    $handlerOutputData['usersGroup']['permissions']['editor_entries_categories_edit'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT);
    $handlerOutputData['usersGroup']['permissions']['editor_pages_static_edit'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT);
    $handlerOutputData['usersGroup']['permissions']['editor_content_blocks_edit'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_CONTENT_BLOCKS_EDIT);
    $handlerOutputData['usersGroup']['permissions']['base_entry_comment_create'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE);
    $handlerOutputData['usersGroup']['permissions']['base_entry_comment_change'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE);
    $handlerOutputData['usersGroup']['permissions']['base_entry_comment_rate'] = $usersGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE);

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USERS_GROUP_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else if ($CMSCore->urlp->getPath(3) === null) {
  $usersGroup = is_numeric($CMSCore->urlp->getPath(2)) ? new UserGroup($CMSCore, $CMSCore->urlp->getPath(2)) : UserGroup::getByName($CMSCore, $CMSCore->urlp->getPath(2));

  if ($usersGroup !== null) {
    $usersGroup->initData(['name', 'texts', 'metadata']);

    $localeName = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');
    
    $handlerOutputData['usersGroup'] = [];
    $handlerOutputData['usersGroup']['id'] = $usersGroup->getID();
    $handlerOutputData['usersGroup']['name'] = $usersGroup->getName();
    $handlerOutputData['usersGroup']['title'] = $usersGroup->getTitle($localeName);

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USERS_GROUP_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}